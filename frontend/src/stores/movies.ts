import { defineStore } from 'pinia'
import { ref } from 'vue'
import { moviesAPI, type Movie, type ApiResponse } from '@/services/api'

export const useMoviesStore = defineStore('movies', () => {
  const movies = ref<Movie[]>([])
  const currentMovie = ref<Movie | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    current_page: 1,
    per_page: 20,
    total_items: 0,
    total_pages: 0,
    has_next: false,
    has_previous: false
  })

  async function fetchMovies(params?: {
    page?: number
    limit?: number
    online?: boolean
    title?: string
    search?: string
    author?: number
    date_from?: string
    date_to?: string
  }) {
    loading.value = true
    error.value = null
    try {
      const response = await moviesAPI.list(params)
      if (response.data.success && response.data.data) {
        movies.value = response.data.data
        if (response.data.pagination) {
          pagination.value = response.data.pagination
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Failed to fetch movies'
    } finally {
      loading.value = false
    }
  }

  async function fetchMovie(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await moviesAPI.get(id)
      if (response.data.success && response.data.data) {
        currentMovie.value = response.data.data
        return response.data.data
      }
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Failed to fetch movie'
    } finally {
      loading.value = false
    }
  }

  async function createMovie(data: Partial<Movie>) {
    loading.value = true
    error.value = null
    try {
      const response = await moviesAPI.create(data)
      if (response.data.success && response.data.data) {
        return response.data.data
      }
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Failed to create movie'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateMovie(id: number, data: Partial<Movie>) {
    loading.value = true
    error.value = null
    try {
      const response = await moviesAPI.update(id, data)
      if (response.data.success && response.data.data) {
        return response.data.data
      }
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Failed to update movie'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteMovie(id: number) {
    loading.value = true
    error.value = null
    try {
      await moviesAPI.delete(id)
      movies.value = movies.value.filter((m) => m.id !== id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Failed to delete movie'
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    movies,
    currentMovie,
    loading,
    error,
    pagination,
    fetchMovies,
    fetchMovie,
    createMovie,
    updateMovie,
    deleteMovie
  }
})
