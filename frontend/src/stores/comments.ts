import { defineStore } from 'pinia'
import { ref } from 'vue'
import { commentsAPI, type Comment } from '@/services/api'

export const useCommentsStore = defineStore('comments', () => {
  const comments = ref<Comment[]>([])
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

  async function fetchComments(params?: { page?: number; limit?: number; movie?: number }) {
    loading.value = true
    error.value = null
    try {
      const response = await commentsAPI.list(params)
      if (response.data.success && response.data.data) {
        comments.value = response.data.data
        if (response.data.pagination) {
          pagination.value = response.data.pagination
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Failed to fetch comments'
    } finally {
      loading.value = false
    }
  }

  async function createComment(data: { content: string; movie_id: number }) {
    loading.value = true
    error.value = null
    try {
      const response = await commentsAPI.create(data)
      if (response.data.success && response.data.data) {
        comments.value.unshift(response.data.data)
        return response.data.data
      }
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Failed to create comment'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateComment(id: number, content: string) {
    loading.value = true
    error.value = null
    try {
      const response = await commentsAPI.update(id, { content })
      if (response.data.success && response.data.data) {
        const index = comments.value.findIndex((c) => c.id === id)
        if (index !== -1) {
          comments.value[index] = response.data.data
        }
        return response.data.data
      }
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Failed to update comment'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteComment(id: number) {
    loading.value = true
    error.value = null
    try {
      await commentsAPI.delete(id)
      comments.value = comments.value.filter((c) => c.id !== id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Failed to delete comment'
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    comments,
    loading,
    error,
    pagination,
    fetchComments,
    createComment,
    updateComment,
    deleteComment
  }
})
