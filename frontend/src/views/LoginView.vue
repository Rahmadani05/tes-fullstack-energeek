<template>
  <div class="login-container">
    <div class="login-card">
      <div class="logo">
        <span class="text-black">Task</span><span class="text-blue">Tracker</span>
      </div>
      <p class="subtitle">Masuk ke akun kamu</p>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            v-model="email"
            placeholder="admin@energeek.id"
            :class="{ 'is-invalid': errors.email }"
          />
          <span class="error-text" v-if="errors.email">{{ errors.email[0] }}</span>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            v-model="password"
            placeholder="••••••••"
            :class="{ 'is-invalid': errors.password }"
          />
          <span class="error-text" v-if="errors.password">{{ errors.password[0] }}</span>
        </div>

        <div class="error-alert" v-if="errorMessage">
          {{ errorMessage }}
        </div>

        <button type="submit" class="btn-submit" :disabled="isLoading">
          {{ isLoading ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { LayoutDashboard, Folder, CheckSquare, LogOut } from 'lucide-vue-next'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const email = ref('')
const password = ref('')
const isLoading = ref(false)
const errors = ref<Record<string, string[]>>({})
const errorMessage = ref('')

const authStore = useAuthStore()
const router = useRouter()

const handleLogin = async () => {
  isLoading.value = true
  errors.value = {}
  errorMessage.value = ''

  try {
    await authStore.login({
      email: email.value,
      password: password.value
    })
    //jika sukses, arahkan ke dashboard
    router.push({ name: 'dashboard' })
  } catch (error: any) {
    if (error.response?.status === 422) {
      //cek error validasi dari laravel per field
      errors.value = error.response.data.errors
    } else if (error.response?.status === 401) {
      //cek error unauthorized (email/password salah)
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Terjadi kesalahan pada server.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #1a1e29; 
}

.login-card {
  background: white;
  padding: 2.5rem 2.5rem;
  border-radius: 12px;
  width: 100%;
  max-width: 360px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.logo {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.text-black { color: #111827; }
.text-blue { color: #3b82f6; }

.subtitle {
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 2rem;
  margin-top: 0;
}

.form-group {
  margin-bottom: 1.25rem;
}

label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s;
  box-sizing: border-box;
}

input:focus {
  border-color: #3b82f6;
}

input.is-invalid {
  border-color: #ef4444;
}

.error-text {
  color: #ef4444;
  font-size: 0.75rem;
  margin-top: 0.35rem;
  display: block;
}

.error-alert {
  background-color: #fee2e2;
  color: #b91c1c;
  padding: 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  margin-bottom: 1.25rem;
  text-align: center;
}

.btn-submit {
  width: 100%;
  background-color: #4f46e5;
  color: white;
  font-weight: 600;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background-color 0.2s;
  margin-top: 0.5rem;
}

.btn-submit:hover:not(:disabled) {
  background-color: #4338ca;
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>