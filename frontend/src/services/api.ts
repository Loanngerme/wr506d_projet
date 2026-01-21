import axios, { type AxiosInstance } from 'axios'

const apiClient: AxiosInstance = axios.create({
  baseURL: 'http://localhost:8319/api',
  headers: {
    'Content-Type': 'application/json'
  }
})

// Add token to requests if available
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Handle 401 errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    // Don't redirect if it's a 2FA required response
    if (error.response?.status === 401 && error.response?.data?.status !== 'totp_required') {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export interface ApiResponse<T> {
  success: boolean
  data?: T
  message?: string
  error?: string
  pagination?: {
    current_page: number
    per_page: number
    total_items: number
    total_pages: number
    has_next: boolean
    has_previous: boolean
  }
}

export interface User {
  id: number
  email: string
  firstname: string | null
  lastname: string | null
  roles: string[]
  createdAt: string
  movies_count?: number
  comments_count?: number
}

export interface Movie {
  id: number
  name: string
  description: string | null
  duration: number | null
  releaseDate: string | null
  image: string | null
  online: boolean
  nbEntries: number | null
  url: string | null
  budget: number | null
  createdAt: string
  director: {
    id: number
    firstname: string
    lastname: string
  } | null
  author: {
    id: number
    email: string
    firstname: string | null
    lastname: string | null
  } | null
  actors?: Array<{
    id: number
    firstname: string
    lastname: string
  }>
  categories?: Array<{
    id: number
    name: string
  }>
}

export interface Comment {
  id: number
  content: string
  createdAt: string
  updatedAt: string | null
  author: {
    id: number
    email: string
    firstname: string | null
    lastname: string | null
  }
  movie_id?: number
  movie?: {
    id: number
    name: string
  }
}

// Auth API
export const authAPI = {
  register(data: { email: string; password: string; firstname?: string; lastname?: string }) {
    return apiClient.post<ApiResponse<{ user: User; token: string }>>('/register', data)
  },
  login(email: string, password: string, totpCode?: string) {
    const payload: any = { email, password }
    if (totpCode) {
      payload.totp_code = totpCode
    }
    return apiClient.post<{ token: string; status?: string; message?: string }>('/auth', payload, {
      baseURL: 'http://localhost:8319'
    })
  },
  me() {
    return apiClient.get<ApiResponse<User>>('/me')
  }
}

// 2FA API
export const twoFactorAPI = {
  setup() {
    return apiClient.post<{
      secret: string
      qr_code: string
      provisioning_uri: string
      message: string
    }>('/2fa/setup')
  },
  enable(code: string) {
    return apiClient.post<{
      message: string
      backup_codes: string[]
      warning: string
    }>('/2fa/enable', { code })
  },
  disable(code: string) {
    return apiClient.post<{ message: string }>('/2fa/disable', { code })
  }
}

// Movies API
export const moviesAPI = {
  list(params?: {
    page?: number
    limit?: number
    online?: boolean
    title?: string
    search?: string
    author?: number
    date_from?: string
    date_to?: string
  }) {
    return apiClient.get<ApiResponse<Movie[]>>('/v1/movies', { params })
  },
  get(id: number) {
    return apiClient.get<ApiResponse<Movie>>(`/v1/movies/${id}`)
  },
  create(data: Partial<Movie>) {
    return apiClient.post<ApiResponse<Movie>>('/v1/movies', data)
  },
  update(id: number, data: Partial<Movie>) {
    return apiClient.patch<ApiResponse<Movie>>(`/v1/movies/${id}`, data)
  },
  delete(id: number) {
    return apiClient.delete<ApiResponse<void>>(`/v1/movies/${id}`)
  }
}

// Comments API
export const commentsAPI = {
  list(params?: { page?: number; limit?: number; movie?: number }) {
    return apiClient.get<ApiResponse<Comment[]>>('/v1/comments', { params })
  },
  get(id: number) {
    return apiClient.get<ApiResponse<Comment>>(`/v1/comments/${id}`)
  },
  create(data: { content: string; movie_id: number }) {
    return apiClient.post<ApiResponse<Comment>>('/v1/comments', data)
  },
  update(id: number, data: { content: string }) {
    return apiClient.patch<ApiResponse<Comment>>(`/v1/comments/${id}`, data)
  },
  delete(id: number) {
    return apiClient.delete<ApiResponse<void>>(`/v1/comments/${id}`)
  }
}

// Users API
export const usersAPI = {
  list(params?: { page?: number; limit?: number }) {
    return apiClient.get<ApiResponse<User[]>>('/v1/users', { params })
  },
  get(id: number) {
    return apiClient.get<ApiResponse<User>>(`/v1/users/${id}`)
  },
  update(id: number, data: Partial<User>) {
    return apiClient.patch<ApiResponse<User>>(`/v1/users/${id}`, data)
  },
  delete(id: number) {
    return apiClient.delete<ApiResponse<void>>(`/v1/users/${id}`)
  },
  updateRoles(id: number, roles: string[]) {
    return apiClient.patch<ApiResponse<User>>(`/v1/users/${id}/roles`, { roles })
  }
}

// Upload API
export const uploadAPI = {
  uploadFile(file: File) {
    const formData = new FormData()
    formData.append('file', file)
    return apiClient.post<ApiResponse<{ filename: string; url: string }>>(
      '/v1/upload',
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )
  },
  uploadImage(file: File) {
    const formData = new FormData()
    formData.append('image', file)
    return apiClient.post<ApiResponse<{ filename: string; url: string }>>(
      '/v1/upload/image',
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )
  }
}

export default apiClient
