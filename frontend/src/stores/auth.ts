import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI, type User } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.roles.includes('ROLE_ADMIN') ?? false)

  async function login(email: string, password: string) {
    loading.value = true
    error.value = null
    try {
      const response = await authAPI.login(email, password)
      token.value = response.data.token
      localStorage.setItem('token', response.data.token)
      await fetchUser()
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Login failed'
      return false
    } finally {
      loading.value = false
    }
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
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
    fetchUser
  }
})
