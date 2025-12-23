<template>
  <div class="movie-form">
    <h1>{{ isEdit ? 'Modifier le film' : 'Ajouter un film' }}</h1>
    <form @submit.prevent="handleSubmit">
      <div class="form-row">
        <div class="form-group">
          <label for="name">Titre *</label>
          <input
            id="name"
            v-model="formData.name"
            type="text"
            required
            class="form-control"
          />
        </div>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea
          id="description"
          v-model="formData.description"
          rows="4"
          class="form-control"
        ></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="duration">Durée (minutes)</label>
          <input
            id="duration"
            v-model.number="formData.duration"
            type="number"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label for="releaseDate">Date de sortie</label>
          <input id="releaseDate" v-model="formData.releaseDate" type="date" class="form-control" />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="image">URL de l'image</label>
          <input id="image" v-model="formData.image" type="text" class="form-control" />
        </div>
        <div class="form-group">
          <label for="online">Statut</label>
          <select id="online" v-model="formData.online" class="form-control">
            <option :value="true">En ligne</option>
            <option :value="false">Hors ligne</option>
          </select>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" :disabled="loading" class="btn btn-primary">
          {{ loading ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
        <router-link :to="{ name: 'admin-movies' }" class="btn btn-secondary">
          Annuler
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMoviesStore } from '@/stores/movies'

const route = useRoute()
const router = useRouter()
const moviesStore = useMoviesStore()

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)

const formData = ref({
  name: '',
  description: '',
  duration: null as number | null,
  releaseDate: '',
  image: '',
  online: false
})

onMounted(async () => {
  if (isEdit.value) {
    const movie = await moviesStore.fetchMovie(Number(route.params.id))
    if (movie) {
      formData.value = {
        name: movie.name,
        description: movie.description || '',
        duration: movie.duration,
        releaseDate: movie.releaseDate || '',
        image: movie.image || '',
        online: movie.online
      }
    }
  }
})

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await moviesStore.updateMovie(Number(route.params.id), formData.value)
    } else {
      await moviesStore.createMovie(formData.value)
    }
    router.push({ name: 'admin-movies' })
  } catch (error) {
    alert('Erreur lors de l\'enregistrement')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.movie-form {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  max-width: 800px;
}

h1 {
  font-size: 2rem;
  margin-bottom: 2rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
}

textarea.form-control {
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  text-decoration: none;
  display: inline-block;
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

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
