import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI, type User } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref<string | null>(null)
  const twoFactorRequired = ref(false)
  const pendingCredentials = ref<{ email: string; password: string } | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.roles.includes('ROLE_ADMIN') ?? false)

  async function login(email: string, password: string, totpCode?: string) {
    loading.value = true
    error.value = null
    twoFactorRequired.value = false
    try {
      const response = await authAPI.login(email, password, totpCode)
      console.log('Login response:', response.data)

      // Check if 2FA is required
      if (response.data.status === 'totp_required') {
        twoFactorRequired.value = true
        pendingCredentials.value = { email, password }
        error.value = response.data.message || '2FA code required'
        return false
      }

      token.value = response.data.token
      localStorage.setItem('token', response.data.token)
      await fetchUser()
      pendingCredentials.value = null
      return true
    } catch (err: any) {
      console.log('Login error:', err)
      console.log('Error response:', err.response)
      console.log('Error response data:', err.response?.data)
      console.log('Error response status:', err.response?.data?.status)

      // Check if 2FA is required (when backend returns 401 with totp_required status)
      if (err.response?.data?.status === 'totp_required') {
        console.log('2FA required detected!')
        twoFactorRequired.value = true
        pendingCredentials.value = { email, password }
        error.value = err.response.data.message || '2FA code required'
        return false
      }

      error.value = err.response?.data?.error || err.response?.data?.message || 'Login failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function loginWith2FA(totpCode: string) {
    if (!pendingCredentials.value) {
      error.value = 'No pending credentials'
      return false
    }
    return await login(
      pendingCredentials.value.email,
      pendingCredentials.value.password,
      totpCode
    )
  }

  async function register(data: {
    email: string
    password: string
    firstname?: string
    lastname?: string
  }) {
    loading.value = true
    error.value = null
    try {
      const response = await authAPI.register(data)
      if (response.data.success && response.data.data) {
        token.value = response.data.data.token
        user.value = response.data.data.user
        localStorage.setItem('token', response.data.data.token)
        return true
      }
      return false
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Registration failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      const response = await authAPI.me()
      if (response.data.success && response.data.data) {
        user.value = response.data.data
      }
    } catch (err) {
      logout()
    }
  }

  function logout() {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
  }

  // Initialize user if token exists
  if (token.value) {
    fetchUser()
  }

  return {
    user,
    token,
    loading,
    error,
    twoFactorRequired,
    isAuthenticated,
    isAdmin,
    login,
    loginWith2FA,
    register,
    logout,
    fetchUser
  }
})
