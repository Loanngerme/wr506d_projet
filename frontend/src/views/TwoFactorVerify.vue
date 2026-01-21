<template>
  <div class="login-container">
    <div class="login-card">
      <h1>Authentification à deux facteurs</h1>
      <p class="info-text">
        Entrez le code à 6 chiffres généré par votre application d'authentification.
      </p>
      <form @submit.prevent="handleVerify">
        <div class="form-group">
          <label for="code">Code 2FA</label>
          <input
            id="code"
            v-model="code"
            type="text"
            required
            maxlength="6"
            pattern="[0-9]{6}"
            placeholder="123456"
            class="form-control code-input"
            autofocus
          />
        </div>
        <div v-if="authStore.error" class="error-message">{{ authStore.error }}</div>
        <button type="submit" :disabled="authStore.loading || code.length !== 6" class="btn btn-primary btn-block">
          {{ authStore.loading ? 'Vérification...' : 'Vérifier' }}
        </button>
      </form>
      <p class="back-link">
        <router-link :to="{ name: 'login' }">Retour à la connexion</router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const code = ref('')

// Redirect if no pending credentials
if (!authStore.twoFactorRequired) {
  router.push({ name: 'login' })
}

async function handleVerify() {
  const success = await authStore.loginWith2FA(code.value)
  if (success) {
    const redirect = route.query.redirect as string
    router.push(redirect || '/')
  }
}
</script>

<style scoped>
.login-container {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background-color: #f8f9fa;
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

h1 {
  font-size: 1.75rem;
  margin-bottom: 1rem;
  text-align: center;
  color: #333;
}

.info-text {
  text-align: center;
  color: #666;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
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

.code-input {
  text-align: center;
  font-size: 1.5rem;
  letter-spacing: 0.5rem;
  font-family: monospace;
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

.back-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #666;
}

.back-link a {
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
}

.back-link a:hover {
  text-decoration: underline;
}
</style>
