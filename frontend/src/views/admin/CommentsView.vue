<template>
  <div class="admin-comments">
    <h1>Modération des commentaires</h1>

    <div v-if="commentsStore.loading" class="loading">Chargement...</div>

    <table v-else class="comments-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Contenu</th>
          <th>Auteur</th>
          <th>Film</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="comment in commentsStore.comments" :key="comment.id">
          <td>{{ comment.id }}</td>
          <td class="content">{{ comment.content }}</td>
          <td>{{ comment.author.firstname || comment.author.email }}</td>
          <td>{{ comment.movie?.name || comment.movie_id }}</td>
          <td>{{ new Date(comment.createdAt).toLocaleDateString('fr-FR') }}</td>
          <td class="actions">
            <button @click="deleteComment(comment.id)" class="btn-link danger">
              🗑️ Supprimer
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="commentsStore.pagination.total_pages > 1" class="pagination">
      <button
        @click="changePage(commentsStore.pagination.current_page - 1)"
        :disabled="!commentsStore.pagination.has_previous"
        class="btn"
      >
        Précédent
      </button>
      <span>
        Page {{ commentsStore.pagination.current_page }} /
        {{ commentsStore.pagination.total_pages }}
      </span>
      <button
        @click="changePage(commentsStore.pagination.current_page + 1)"
        :disabled="!commentsStore.pagination.has_next"
        class="btn"
      >
        Suivant
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useCommentsStore } from '@/stores/comments'

const commentsStore = useCommentsStore()
const currentPage = ref(1)

onMounted(() => {
  loadComments()
})

function loadComments() {
  commentsStore.fetchComments({ page: currentPage.value, limit: 20 })
}

function changePage(page: number) {
  currentPage.value = page
  loadComments()
}

async function deleteComment(id: number) {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
    await commentsStore.deleteComment(id)
    loadComments()
  }
}
</script>

<style scoped>
.admin-comments {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  width: 100%;
  min-width: 100%;
  box-sizing: border-box;
}

h1 {
  font-size: 2rem;
  margin-bottom: 2rem;
}

.comments-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
}

.comments-table th,
.comments-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.comments-table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.comments-table tr:hover {
  background-color: #f8f9fa;
}

.content {
  max-width: 400px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
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
}

.btn:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.btn-link {
  background: none;
  border: none;
  color: #007bff;
  cursor: pointer;
  padding: 0;
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
