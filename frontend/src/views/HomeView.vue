<template>
  <div class="home">
    <div class="header">
      <h1>Films en ligne</h1>
      <div class="filters">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Rechercher un film..."
          @input="handleSearch"
          class="search-input"
        />
        <select v-model="onlineFilter" @change="handleFilterChange" class="filter-select">
          <option value="">Tous les films</option>
          <option value="true">En ligne uniquement</option>
          <option value="false">Hors ligne uniquement</option>
        </select>
      </div>
    </div>

    <div v-if="moviesStore.loading" class="loading">Chargement des films...</div>
    <div v-else-if="moviesStore.error" class="error">
      <p>❌ Erreur: {{ moviesStore.error }}</p>
      <p class="error-detail">Vérifiez que l'API est accessible sur http://localhost:8319</p>
    </div>
    <div v-else-if="moviesStore.movies.length === 0" class="no-movies">
      <p>Aucun film trouvé.</p>
    </div>

    <div v-else class="movies-grid">
      <div
        v-for="movie in moviesStore.movies"
        :key="movie.id"
        class="movie-card"
        @click="goToMovie(movie.id)"
      >
        <div class="movie-image">
          <img
            v-if="movie.image"
            :src="getImageUrl(movie.image)"
            :alt="movie.name"
          />
          <div v-else class="no-image">Pas d'image</div>
        </div>
        <div class="movie-info">
          <h3>{{ movie.name }}</h3>
          <p v-if="movie.description" class="description">
            {{ movie.description.substring(0, 100) }}...
          </p>
          <div class="movie-meta">
            <span v-if="movie.releaseDate" class="release-date">
              {{ new Date(movie.releaseDate).getFullYear() }}
            </span>
            <span v-if="movie.duration" class="duration">{{ movie.duration }} min</span>
            <span v-if="movie.online" class="badge online">En ligne</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="moviesStore.pagination.total_pages > 1" class="pagination">
      <button
        @click="changePage(moviesStore.pagination.current_page - 1)"
        :disabled="!moviesStore.pagination.has_previous"
        class="btn"
      >
        Précédent
      </button>
      <span class="page-info">
        Page {{ moviesStore.pagination.current_page }} sur
        {{ moviesStore.pagination.total_pages }}
      </span>
      <button
        @click="changePage(moviesStore.pagination.current_page + 1)"
        :disabled="!moviesStore.pagination.has_next"
        class="btn"
      >
        Suivant
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMoviesStore } from '@/stores/movies'

const router = useRouter()
const moviesStore = useMoviesStore()

const searchQuery = ref('')
const onlineFilter = ref('')
const currentPage = ref(1)

let searchTimeout: ReturnType<typeof setTimeout> | null = null

onMounted(() => {
  loadMovies()
})

function loadMovies() {
  moviesStore.fetchMovies({
    page: currentPage.value,
    limit: 12,
    online: onlineFilter.value ? onlineFilter.value === 'true' : undefined,
    search: searchQuery.value || undefined
  })
}

function handleSearch() {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    loadMovies()
  }, 500)
}

function handleFilterChange() {
  currentPage.value = 1
  loadMovies()
}

function changePage(page: number) {
  currentPage.value = page
  loadMovies()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function goToMovie(id: number) {
  router.push({ name: 'movie-detail', params: { id } })
}

function getImageUrl(image: string | null): string {
  if (!image) return ''
  // Si l'image commence par http:// ou https://, c'est déjà une URL complète
  if (image.startsWith('http://') || image.startsWith('https://')) {
    return image
  }
  // Sinon, c'est un chemin local
  return `http://localhost:8319${image}`
}
</script>

<style scoped>
.home {
  width: 100%;
  min-width: 100%;
  padding: 2rem;
  box-sizing: border-box;
}

.header {
  margin-bottom: 2rem;
}

h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.filters {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.search-input,
.filter-select {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.loading,
.error,
.no-movies {
  text-align: center;
  padding: 3rem 2rem;
  font-size: 1.2rem;
}

.error {
  color: #dc3545;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 8px;
}

.error-detail {
  font-size: 0.9rem;
  margin-top: 0.5rem;
  color: #721c24;
}

.no-movies {
  color: #666;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
}

.movies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.movie-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.movie-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.movie-image {
  height: 350px;
  background-color: #f5f5f5;
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

.movie-info {
  padding: 1rem;
}

.movie-info h3 {
  font-size: 1.2rem;
  margin-bottom: 0.5rem;
}

.description {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.movie-meta {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  font-size: 0.85rem;
  color: #666;
}

.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-weight: bold;
}

.badge.online {
  background-color: #28a745;
  color: white;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.btn {
  padding: 0.5rem 1rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.btn:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.9rem;
  color: #666;
}
</style>
