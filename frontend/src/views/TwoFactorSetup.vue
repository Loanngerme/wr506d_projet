<template>
  <div class="setup-container">
    <div class="setup-card">
      <h1>Activer l'authentification à deux facteurs</h1>

      <!-- Step 1: Generate QR Code -->
      <div v-if="step === 1" class="step">
        <p class="info-text">
        </p>
        <button @click="generateQRCode" :disabled="loading" class="btn btn-primary btn-block">
          {{ loading ? 'Génération...' : 'Commencer' }}
        </button>
      </div>

      <div v-if="step === 2" class="step">
        <p class="info-text">
          Scannez ce QR code avec votre application d'authentification (Google Authenticator, Authy,
          etc.)
        </p>
        <div v-if="qrCode" class="qr-code-container">
          <img :src="qrCode" alt="QR Code" class="qr-code" />
        </div>
        <div class="secret-container">
          <p class="secret-label">Ou entrez ce code manuellement :</p>
          <code class="secret-code">{{ secret }}</code>
        </div>
        <button @click="step = 3" class="btn btn-primary btn-block">Continuer</button>
      </div>

      <!-- Step 3: Verify Code -->
      <div v-if="step === 3" class="step">
        <p class="info-text">
          Entrez le code à 6 chiffres généré par votre application pour vérifier la configuration.
        </p>
        <form @submit.prevent="enableTwoFactor">
          <div class="form-group">
            <label for="code">Code 2FA</label>
            <input
              id="code"
              v-model="verificationCode"
              type="text"
              required
              maxlength="6"
              pattern="[0-9]{6}"
              placeholder="123456"
              class="form-control code-input"
              autofocus
            />
          </div>
          <div v-if="error" class="error-message">{{ error }}</div>
          <button
            type="submit"
            :disabled="loading || verificationCode.length !== 6"
            class="btn btn-primary btn-block"
          >
            {{ loading ? 'Vérification...' : 'Activer le 2FA' }}
          </button>
        </form>
      </div>

      <!-- Step 4: Success with Backup Codes -->
      <div v-if="step === 4" class="step">
        <div class="success-message">
          <h2>✅ 2FA activé avec succès !</h2>
        </div>
        <div class="backup-codes-container">
          <p class="warning-text">
            ⚠️ <strong>Important :</strong> Sauvegardez ces codes de secours dans un endroit sûr.
            Ils peuvent être utilisés une seule fois si vous perdez l'accès à votre application
            d'authentification.
          </p>
          <div class="backup-codes">
            <code v-for="(code, index) in backupCodes" :key="index" class="backup-code">
              {{ code }}
            </code>
          </div>
          <button @click="router.push('/')" class="btn btn-primary btn-block">
            Terminer
          </button>
        </div>
      </div>

      <p v-if="step < 4" class="back-link">
        <router-link to="/">Annuler</router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { twoFactorAPI } from '@/services/api'

const router = useRouter()

const step = ref(1)
const loading = ref(false)
const error = ref<string | null>(null)
const qrCode = ref<string | null>(null)
const secret = ref<string>('')
const verificationCode = ref('')
const backupCodes = ref<string[]>([])

async function generateQRCode() {
  loading.value = true
  error.value = null
  try {
    const response = await twoFactorAPI.setup()
    qrCode.value = response.data.qr_code
    secret.value = response.data.secret
    step.value = 2
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to generate QR code'
  } finally {
    loading.value = false
  }
}

async function enableTwoFactor() {
  loading.value = true
  error.value = null
  try {
    const response = await twoFactorAPI.enable(verificationCode.value)
    backupCodes.value = response.data.backup_codes
    step.value = 4
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Invalid code'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.setup-container {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background-color: #f8f9fa;
}

.setup-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 500px;
}

h1 {
  font-size: 1.75rem;
  margin-bottom: 1.5rem;
  text-align: center;
  color: #333;
}

h2 {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  text-align: center;
  color: #28a745;
}

.step {
  margin-bottom: 1rem;
}

.info-text {
  text-align: center;
  color: #666;
  margin-bottom: 1.5rem;
  line-height: 1.5;
}

.qr-code-container {
  display: flex;
  justify-content: center;
  margin: 1.5rem 0;
}

.qr-code {
  max-width: 250px;
  height: auto;
  border: 2px solid #ddd;
  border-radius: 8px;
  padding: 10px;
  background: white;
}

.secret-container {
  text-align: center;
  margin: 1.5rem 0;
}

.secret-label {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.secret-code {
  display: block;
  background-color: #f8f9fa;
  padding: 0.75rem;
  border-radius: 4px;
  font-family: monospace;
  font-size: 1.1rem;
  letter-spacing: 0.1rem;
  word-break: break-all;
  color: #333;
  border: 1px solid #ddd;
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
  text-align: center;
}

.success-message {
  text-align: center;
  margin-bottom: 1.5rem;
}

.backup-codes-container {
  margin-top: 1.5rem;
}

.warning-text {
  color: #856404;
  background-color: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 4px;
  padding: 1rem;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.backup-codes {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.backup-code {
  background-color: #f8f9fa;
  padding: 0.5rem;
  border-radius: 4px;
  font-family: monospace;
  font-size: 0.9rem;
  text-align: center;
  border: 1px solid #ddd;
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
