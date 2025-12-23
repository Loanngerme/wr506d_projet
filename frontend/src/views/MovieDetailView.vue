<template>
  <div class="movie-detail" v-if="movie">
    <div class="movie-header">
      <div class="movie-image">
        <img v-if="movie.image" :src="getImageUrl(movie.image)" :alt="movie.name" />
        <div v-else class="no-image">Pas d'image</div>
      </div>
      <div class="movie-info">
        <h1>{{ movie.name }}</h1>
        <div class="movie-meta">
          <span v-if="movie.releaseDate" class="meta-item">
            📅 {{ new Date(movie.releaseDate).toLocaleDateString('fr-FR') }}
          </span>
          <span v-if="movie.duration" class="meta-item">⏱️ {{ movie.duration }} min</span>
          <span v-if="movie.online" class="badge online">En ligne</span>
        </div>
        <p v-if="movie.description" class="description">{{ movie.description }}</p>
        <div v-if="movie.director" class="director">
          <strong>Réalisateur:</strong> {{ movie.director.firstname }}
          {{ movie.director.lastname }}
        </div>
        <div v-if="movie.author" class="author">
          <strong>Ajouté par:</strong> {{ movie.author.firstname || movie.author.email }}
        </div>
        <div v-if="movie.actors && movie.actors.length" class="actors">
          <strong>Acteurs:</strong>
          <span v-for="actor in movie.actors" :key="actor.id" class="actor-tag">
            {{ actor.firstname }} {{ actor.lastname }}
          </span>
        </div>
        <div v-if="movie.categories && movie.categories.length" class="categories">
          <strong>Catégories:</strong>
          <span v-for="category in movie.categories" :key="category.id" class="category-tag">
            {{ category.name }}
          </span>
        </div>
      </div>
    </div>

    <div class="comments-section">
      <h2>Commentaires ({{ comments.length }})</h2>

      <div v-if="authStore.isAuthenticated" class="comment-form">
        <textarea
          v-model="newComment"
          placeholder="Ajouter un commentaire..."
          rows="3"
          class="comment-input"
        ></textarea>
        <button @click="submitComment" :disabled="!newComment.trim()" class="btn btn-primary">
          Publier
        </button>
      </div>
      <div v-else class="login-prompt">
        <p>
          <router-link :to="{ name: 'login', query: { redirect: $route.fullPath } }">
            Connectez-vous
          </router-link>
          pour laisser un commentaire
        </p>
      </div>

      <div class="comments-list">
        <div v-for="comment in comments" :key="comment.id" class="comment">
          <div class="comment-header">
            <strong>{{ comment.author.firstname || comment.author.email }}</strong>
            <span class="comment-date">{{ formatDate(comment.createdAt) }}</span>
          </div>
          <p class="comment-content">{{ comment.content }}</p>
          <div
            v-if="canEditComment(comment)"
            class="comment-actions"
          >
            <button @click="deleteComment(comment.id)" class="btn-link danger">Supprimer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else-if="loading" class="loading">Chargement...</div>
  <div v-else class="error">Film non trouvé</div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useMoviesStore } from '@/stores/movies'
import { useCommentsStore } from '@/stores/comments'
import { useAuthStore } from '@/stores/auth'
import type { Movie, Comment } from '@/services/api'

const route = useRoute()
const moviesStore = useMoviesStore()
const commentsStore = useCommentsStore()
const authStore = useAuthStore()

const movie = ref<Movie | null>(null)
const comments = ref<Comment[]>([])
const newComment = ref('')
const loading = ref(true)

onMounted(async () => {
  const movieId = Number(route.params.id)
  movie.value = await moviesStore.fetchMovie(movieId)
  await loadComments()
  loading.value = false
})

async function loadComments() {
  await commentsStore.fetchComments({ movie: Number(route.params.id), limit: 100 })
  comments.value = commentsStore.comments
}

async function submitComment() {
  if (!newComment.value.trim()) return
  try {
    await commentsStore.createComment({
      content: newComment.value,
      movie_id: Number(route.params.id)
    })
    newComment.value = ''
    await loadComments()
  } catch (error) {
    alert('Erreur lors de la publication du commentaire')
  }
}

async function deleteComment(id: number) {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
    await commentsStore.deleteComment(id)
    await loadComments()
  }
}

function canEditComment(comment: Comment) {
  return (
    authStore.isAuthenticated &&
    (authStore.user?.id === comment.author.id || authStore.isAdmin)
  )
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getImageUrl(image: string | null): string {
  if (!image) return ''
  if (image.startsWith('http://') || image.startsWith('https://')) {
    return image
  }
  return `http://localhost:8319${image}`
}
</script>

<style scoped>
.movie-detail {
  width: 100%;
  min-width: 100%;
  padding: 2rem 4rem;
  box-sizing: border-box;
}

.movie-header {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 2rem;
  margin-bottom: 3rem;
}

.movie-image {
  width: 100%;
  height: 450px;
  background-color: #f5f5f5;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.movie-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  color: #999;
}

.movie-info h1 {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.movie-meta {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
  color: #666;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.9rem;
}

.badge.online {
  background-color: #28a745;
  color: white;
}

.description {
  font-size: 1.1rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
  color: #333;
}

.director,
.author,
.actors,
.categories {
  margin-bottom: 1rem;
}

.actor-tag,
.category-tag {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background-color: #e9ecef;
  border-radius: 4px;
  margin-right: 0.5rem;
  margin-top: 0.5rem;
  font-size: 0.9rem;
}

.comments-section {
  margin-top: 3rem;
}

.comments-section h2 {
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
}

.comment-form {
  margin-bottom: 2rem;
}

.comment-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  font-family: inherit;
  margin-bottom: 0.5rem;
  resize: vertical;
}

.login-prompt {
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
  margin-bottom: 2rem;
  text-align: center;
}

.login-prompt a {
  color: #007bff;
  text-decoration: none;
  font-weight: bold;
}

.comments-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.comment {
  padding: 1rem;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  background-color: #fff;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.comment-date {
  color: #666;
  font-size: 0.85rem;
}

.comment-content {
  margin: 0.5rem 0;
  line-height: 1.5;
}

.comment-actions {
  margin-top: 0.5rem;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn-primary:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.btn-link {
  background: none;
  border: none;
  color: #007bff;
  cursor: pointer;
  padding: 0;
  font-size: 0.9rem;
}

.btn-link.danger {
  color: #dc3545;
}

.loading,
.error {
  text-align: center;
  padding: 3rem;
  font-size: 1.2rem;
}

@media (max-width: 768px) {
  .movie-header {
    grid-template-columns: 1fr;
  }
}
</style>
