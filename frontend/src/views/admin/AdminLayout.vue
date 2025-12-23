<template>
  <div class="admin-layout">
    <nav class="admin-nav">
      <h2>Administration</h2>
      <ul>
        <li>
          <router-link :to="{ name: 'admin-movies' }" active-class="active">
            🎬 Films
          </router-link>
        </li>
        <li>
          <router-link :to="{ name: 'admin-comments' }" active-class="active">
            💬 Commentaires
          </router-link>
        </li>
        <li v-if="authStore.isAdmin">
          <router-link :to="{ name: 'admin-users' }" active-class="active">
            👥 Utilisateurs
          </router-link>
        </li>
        <li>
          <router-link :to="{ name: 'home' }">← Retour au site</router-link>
        </li>
      </ul>
    </nav>
    <main class="admin-content">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>

<style scoped>
.admin-layout {
  display: grid;
  grid-template-columns: 250px 1fr;
  min-height: 100vh;
}

.admin-nav {
  background-color: #2c3e50;
  color: white;
  padding: 2rem 1rem;
}

.admin-nav h2 {
  margin-bottom: 2rem;
  font-size: 1.5rem;
}

.admin-nav ul {
  list-style: none;
  padding: 0;
}

.admin-nav li {
  margin-bottom: 0.5rem;
}

.admin-nav a {
  display: block;
  padding: 0.75rem 1rem;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.admin-nav a:hover,
.admin-nav a.active {
  background-color: #34495e;
}

.admin-content {
  padding: 2rem;
  background-color: #f8f9fa;
}

@media (max-width: 768px) {
  .admin-layout {
    grid-template-columns: 1fr;
  }

  .admin-nav {
    padding: 1rem;
  }

  .admin-nav ul {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .admin-nav li {
    margin-bottom: 0;
  }
}
</style>
