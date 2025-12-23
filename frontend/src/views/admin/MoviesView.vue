<template>
  <div class="admin-movies">
    <div class="header">
      <h1>Gestion des films</h1>
      <router-link :to="{ name: 'admin-movies-create' }" class="btn btn-primary">
        + Ajouter un film
      </router-link>
    </div>

    <div class="filters">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Rechercher..."
        @input="handleSearch"
        class="search-input"
      />
    </div>

    <div v-if="moviesStore.loading" class="loading">Chargement...</div>

    <table v-else class="movies-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Titre</th>
          <th>Date de sortie</th>
          <th>Durée</th>
          <th>Statut</th>
          <th>Auteur</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="movie in moviesStore.movies" :key="movie.id">
          <td>{{ movie.id }}</td>
          <td>{{ movie.name }}</td>
          <td>{{ movie.releaseDate ? new Date(movie.releaseDate).toLocaleDateString('fr-FR') : '-' }}</td>
          <td>{{ movie.duration }} min</td>
          <td>
            <span :class="['badge', movie.online ? 'online' : 'offline']">
              {{ movie.online ? 'En ligne' : 'Hors ligne' }}
            </span>
          </td>
          <td>{{ movie.author?.firstname || movie.author?.email || '-' }}</td>
          <td class="actions">
            <router-link
              :to="{ name: 'admin-movies-edit', params: { id: movie.id } }"
              class="btn-link"
            >
              ✏️ Modifier
            </router-link>
            <button @click="deleteMovie(movie.id)" class="btn-link danger">🗑️ Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="moviesStore.pagination.total_pages > 1" class="pagination">
      <button
        @click="changePage(moviesStore.pagination.current_page - 1)"
        :disabled="!moviesStore.pagination.has_previous"
        class="btn"
      >
        Précédent
      </button>
      <span>
        Page {{ moviesStore.pagination.current_page }} / {{ moviesStore.pagination.total_pages }}
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
import { useMoviesStore } from '@/stores/movies'

const moviesStore = useMoviesStore()
const searchQuery = ref('')
const currentPage = ref(1)

let searchTimeout: ReturnType<typeof setTimeout> | null = null

onMounted(() => {
  loadMovies()
})

function loadMovies() {
  moviesStore.fetchMovies({
    page: currentPage.value,
    limit: 20,
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

function changePage(page: number) {
  currentPage.value = page
  loadMovies()
}

async function deleteMovie(id: number) {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce film ?')) {
    const success = await moviesStore.deleteMovie(id)
    if (success) {
      loadMovies()
    }
  }
}
</script>

<style scoped>
.admin-movies {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  width: 100%;
  min-width: 100%;
  box-sizing: border-box;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

h1 {
  font-size: 2rem;
  margin: 0;
}

.filters {
  margin-bottom: 2rem;
}

.search-input {
  width: 100%;
  max-width: 400px;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.movies-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
}

.movies-table th,
.movies-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.movies-table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.movies-table tr:hover {
  background-color: #f8f9fa;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: bold;
}

.badge.online {
  background-color: #28a745;
  color: white;
}

.badge.offline {
  background-color: #6c757d;
  color: white;
}

.actions {
  display: flex;
  gap: 1rem;
}

.btn {
  padding: 0.5rem 1rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
}

.btn:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #28a745;
}

.btn-primary:hover {
  background-color: #218838;
}

.btn-link {
  background: none;
  border: none;
  color: #007bff;
  cursor: pointer;
  text-decoration: none;
}

.btn-link:hover {
  text-decoration: underline;
}

.btn-link.danger {
  color: #dc3545;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.loading {
  text-align: center;
  padding: 2rem;
}
</style>
