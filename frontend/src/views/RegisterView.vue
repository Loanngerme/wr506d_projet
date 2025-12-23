<template>
  <div class="register-container">
    <div class="register-card">
      <h1>Inscription</h1>
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label for="email">Email *</label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            required
            placeholder="votre@email.com"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label for="password">Mot de passe *</label>
          <input
            id="password"
            v-model="formData.password"
            type="password"
            required
            placeholder="••••••••"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label for="firstname">Prénom</label>
          <input
            id="firstname"
            v-model="formData.firstname"
            type="text"
            placeholder="Jean"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label for="lastname">Nom</label>
          <input
            id="lastname"
            v-model="formData.lastname"
            type="text"
            placeholder="Dupont"
            class="form-control"
          />
        </div>
        <div v-if="authStore.error" class="error-message">{{ authStore.error }}</div>
        <button type="submit" :disabled="authStore.loading" class="btn btn-primary btn-block">
          {{ authStore.loading ? 'Inscription...' : "S'inscrire" }}
        </button>
      </form>
      <p class="login-link">
        Déjà un compte ?
        <router-link :to="{ name: 'login' }">Se connecter</router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const formData = ref({
  email: '',
  password: '',
  firstname: '',
  lastname: ''
})

async function handleRegister() {
  const success = await authStore.register(formData.value)
  if (success) {
    router.push('/')
  }
}
</script>

<style scoped>
.register-container {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background-color: #f8f9fa;
}

.register-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

h1 {
  font-size: 2rem;
  margin-bottom: 1.5rem;
  text-align: center;
  color: #333;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
}

.error-message {
  color: #dc3545;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 4px;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  transition: background-color 0.3s;
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

.btn-block {
  width: 100%;
}

.login-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #666;
}

.login-link a {
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>
