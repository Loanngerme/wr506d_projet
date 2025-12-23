<template>
  <div class="admin-users">
    <h1>Gestion des utilisateurs</h1>

    <div v-if="loading" class="loading">Chargement...</div>

    <table v-else class="users-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Email</th>
          <th>Nom</th>
          <th>Rôles</th>
          <th>Date d'inscription</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.id }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.firstname }} {{ user.lastname }}</td>
          <td>
            <span
              v-for="role in user.roles"
              :key="role"
              class="badge"
              :class="{ admin: role === 'ROLE_ADMIN' }"
            >
              {{ formatRole(role) }}
            </span>
          </td>
          <td>{{ new Date(user.createdAt).toLocaleDateString('fr-FR') }}</td>
          <td class="actions">
            <button @click="toggleAdmin(user)" class="btn-link">
              {{ user.roles.includes('ROLE_ADMIN') ? '↓ Retirer admin' : '↑ Promouvoir admin' }}
            </button>
            <button
              v-if="user.id !== authStore.user?.id"
              @click="deleteUser(user.id)"
              class="btn-link danger"
            >
              🗑️ Supprimer
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="pagination.total_pages > 1" class="pagination">
      <button @click="changePage(pagination.current_page - 1)" :disabled="!pagination.has_previous" class="btn">
        Précédent
      </button>
      <span>Page {{ pagination.current_page }} / {{ pagination.total_pages }}</span>
      <button @click="changePage(pagination.current_page + 1)" :disabled="!pagination.has_next" class="btn">
        Suivant
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { usersAPI, type User, type ApiResponse } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const users = ref<User[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total_items: 0,
  total_pages: 0,
  has_next: false,
  has_previous: false
})

onMounted(() => {
  loadUsers()
})

async function loadUsers() {
  loading.value = true
  try {
    const response = await usersAPI.list({ page: currentPage.value, limit: 20 })
    if (response.data.success && response.data.data) {
      users.value = response.data.data
      if (response.data.pagination) {
        pagination.value = response.data.pagination
      }
    }
  } catch (error) {
    console.error('Failed to load users:', error)
  } finally {
    loading.value = false
  }
}

function changePage(page: number) {
  currentPage.value = page
  loadUsers()
}

async function toggleAdmin(user: User) {
  const hasAdmin = user.roles.includes('ROLE_ADMIN')
  const newRoles = hasAdmin
    ? user.roles.filter((r) => r !== 'ROLE_ADMIN')
    : [...user.roles, 'ROLE_ADMIN']

  try {
    await usersAPI.updateRoles(user.id, newRoles)
    loadUsers()
  } catch (error) {
    alert('Erreur lors de la modification des rôles')
  }
}

async function deleteUser(id: number) {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
    try {
      await usersAPI.delete(id)
      loadUsers()
    } catch (error) {
      alert('Erreur lors de la suppression')
    }
  }
}

function formatRole(role: string): string {
  return role.replace('ROLE_', '')
}
</script>

<style scoped>
.admin-users {
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

.users-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
}

.users-table th,
.users-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.users-table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.users-table tr:hover {
  background-color: #f8f9fa;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background-color: #6c757d;
  color: white;
  border-radius: 4px;
  font-size: 0.85rem;
  margin-right: 0.5rem;
}

.badge.admin {
  background-color: #dc3545;
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
