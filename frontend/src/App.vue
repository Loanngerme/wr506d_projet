<script setup lang="ts">
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

function handleLogout() {
  authStore.logout()
}
</script>

<template>
  <div id="app">
    <header class="main-header">
      <router-link to="/" class="logo">🎬 CinéHub</router-link>
      <nav class="main-nav">
        <router-link to="/" class="nav-link">Films</router-link>
        <router-link
          v-if="authStore.isAuthenticated"
          to="/admin"
          class="nav-link"
        >
          Administration
        </router-link>
        <div v-if="authStore.isAuthenticated" class="user-menu">
          <span class="user-name">{{ authStore.user?.firstname || authStore.user?.email }}</span>
          <router-link to="/2fa/setup" class="btn-2fa">🔐 Activer 2FA</router-link>
          <button @click="handleLogout" class="btn-logout">Déconnexion</button>
        </div>
        <div v-else class="auth-links">
          <router-link to="/login" class="nav-link">Connexion</router-link>
          <router-link to="/register" class="btn-register">Inscription</router-link>
        </div>
      </nav>
    </header>

    <main>
      <RouterView />
    </main>

    <footer class="main-footer">
      <p>&copy; 2025 CinéHub - Plateforme de gestion de films</p>
    </footer>
  </div>
</template>

<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html {
  width: 100%;
  height: 100%;
  overflow-x: hidden;
}

body {
  width: 100%;
  min-height: 100vh;
  margin: 0;
  padding: 0;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial,
    sans-serif;
  line-height: 1.6;
  color: #333;
  background-color: #f8f9fa;
  overflow-x: hidden;
}

#app {
  min-height: 100vh;
  width: 100%;
  min-width: 100%;
  display: flex;
  flex-direction: column;
  margin: 0;
  padding: 0;
}

main {
  flex: 1;
  width: 100%;
  min-width: 100%;
  margin: 0;
  padding: 0;
}
</style>

<style scoped>
.main-header {
  background-color: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 1rem 2rem;
  position: sticky;
  top: 0;
  z-index: 1000;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 1.5rem;
  font-weight: bold;
  color: #007bff;
  text-decoration: none;
}

.main-nav {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-link {
  color: #333;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
}

.nav-link:hover,
.nav-link.router-link-active {
  color: #007bff;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  font-weight: 500;
  color: #666;
}

.btn-2fa {
  padding: 0.5rem 1rem;
  background-color: #17a2b8;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: 500;
  transition: background-color 0.3s;
  white-space: nowrap;
}

.btn-2fa:hover {
  background-color: #138496;
}

.btn-logout {
  padding: 0.5rem 1rem;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.3s;
}

.btn-logout:hover {
  background-color: #c82333;
}

.auth-links {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.btn-register {
  padding: 0.5rem 1rem;
  background-color: #28a745;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  font-weight: 500;
  transition: background-color 0.3s;
}

.btn-register:hover {
  background-color: #218838;
}

.main-footer {
  background-color: #2c3e50;
  color: white;
  padding: 2rem;
  margin-top: 4rem;
  text-align: center;
  width: 100%;
}

@media (max-width: 768px) {
  .main-header {
    flex-direction: column;
    gap: 1rem;
  }

  .main-nav {
    flex-wrap: wrap;
    justify-content: center;
  }
}
</style>
