<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <span class="text-white logo-text">Task</span><span class="text-blue logo-text">Tracker</span>
      </div>

      <!-- //icon pakai bawaan windows (tombol windows + .) -->
      <nav class="sidebar-nav">
        <a href="#" class="nav-item active">
          <span class="icon">📊</span> Dashboard
        </a>
        <router-link :to="{ name: 'projects' }" class="nav-item">
          <span class="icon">📁</span> Project
        </router-link>
        <router-link :to="{ name: 'tasks' }" class="nav-item">
          <span class="icon">✅</span> Task
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn-logout">
          <span class="icon">🚪</span> Keluar
        </button>
        <div class="user-profile">
          <div class="avatar">{{ userInitial }}</div>
          <div class="user-info">
            <p class="user-name">{{ authStore.user?.name || 'User' }}</p>
            <p class="user-role">{{ authStore.user?.is_admin ? 'Administrator' : 'Staff' }}</p>
          </div>
        </div>
      </div>
    </aside>

    <main class="main-content">
      <header class="content-header">
        <h1>Dashboard</h1>
        <p class="subtitle">Selamat datang 👋</p>
      </header>

      <div v-if="isLoading" class="loading-state">
        Memuat data dashboard...
      </div>

      <div v-else class="dashboard-content">
        <div class="summary-cards">
          <div class="card">
            <div class="card-icon bg-gray">📁</div>
            <div class="card-info">
              <p class="card-title">Project Aktif</p>
              <h2 class="card-value">{{ dashboardData.total_active_projects }}</h2>
            </div>
          </div>
          
          <div class="card">
            <div class="card-icon bg-yellow">⏳</div>
            <div class="card-info">
              <p class="card-title">Task Belum Selesai</p>
              <h2 class="card-value">{{ dashboardData.total_unfinished_tasks }}</h2>
            </div>
          </div>
        </div>

        <div class="upcoming-tasks-section">
          <h3 class="section-title">Task Mendekati Due Date</h3>
          <div class="task-list">
            <div v-if="dashboardData.upcoming_tasks.length === 0" class="empty-state">
              Tidak ada task yang mendekati due date.
            </div>
            
            <div 
              v-for="task in dashboardData.upcoming_tasks" 
              :key="task.id" 
              class="task-item"
            >
              <div class="task-item-left">
                <div class="status-dot"></div>
                <div class="task-details">
                  <h4 class="task-name">{{ task.title }}</h4>
                  <p class="task-project">{{ task.project?.name || 'Tanpa Project' }}</p>
                </div>
              </div>
              <div class="task-item-right">
                <span class="due-date">{{ formatDate(task.due_date) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import api from '../services/api'

const authStore = useAuthStore()
const router = useRouter()

const isLoading = ref(true)
const dashboardData = ref({
  total_active_projects: 0,
  total_unfinished_tasks: 0,
  upcoming_tasks: [] as any[]
})

const userInitial = computed(() => {
  const name = authStore.user?.name || 'U'
  return name.charAt(0).toUpperCase()
})

onMounted(async () => {
  try {
    const response = await api.get('/dashboard')
    dashboardData.value = response.data.data
  } catch (error) {
    console.error('Gagal mengambil data dashboard:', error)
  } finally {
    isLoading.value = false
  }
})

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

//fungsi untuk format tanggal
const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f3f4f6; 
}

/* --- sidebar styles --- */
.sidebar {
  width: 260px;
  background-color: #1a1e29;
  color: white;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  padding: 1.5rem;
  font-size: 1.5rem;
  font-weight: bold;
  border-bottom: 1px solid #2d3748;
}

.logo-text.text-white { color: #ffffff; }
.logo-text.text-blue { color: #3b82f6; }

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  display: flex;
  flex-direction: column;
}

.nav-item {
  padding: 0.75rem 1.5rem;
  color: #9ca3af;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: all 0.2s;
  font-weight: 500;
}

.nav-item:hover, .nav-item.active {
  background-color: #2d3748;
  color: #ffffff;
}

.nav-item.active {
  border-left: 4px solid #3b82f6;
}

.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid #2d3748;
}

.btn-logout {
  width: 100%;
  padding: 0.75rem;
  background: transparent;
  border: none;
  color: #9ca3af;
  text-align: left;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  margin-bottom: 1rem;
  transition: color 0.2s;
}

.btn-logout:hover {
  color: #ef4444; 
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem;
}

.avatar {
  width: 40px;
  height: 40px;
  background-color: #8b5cf6; 
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
}

.user-info p { margin: 0; line-height: 1.2; }
.user-name { font-size: 0.875rem; font-weight: bold; color: white; }
.user-role { font-size: 0.75rem; color: #9ca3af; }

/* --- main contetns styles --- */
.main-content {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}

.content-header h1 {
  margin: 0 0 0.25rem 0;
  color: #111827;
}

.subtitle {
  color: #6b7280;
  margin-top: 0;
  margin-bottom: 2rem;
}

.summary-cards {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1.5rem;
}

.bg-gray { background-color: #f3f4f6; }
.bg-yellow { background-color: #fef3c7; }

.card-info p { margin: 0; color: #6b7280; font-size: 0.875rem; }
.card-info h2 { margin: 0; font-size: 1.5rem; color: #111827; }

.upcoming-tasks-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.section-title {
  margin-top: 0;
  margin-bottom: 1.5rem;
  font-size: 1rem;
  color: #111827;
}

.task-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.task-item:last-child { border-bottom: none; }

.task-item-left {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #ef4444;    
  margin-top: 6px;
}

.task-details h4 { margin: 0 0 0.25rem 0; color: #111827; font-size: 0.95rem; }
.task-project { margin: 0; color: #9ca3af; font-size: 0.8rem; }

.due-date {
  font-size: 0.85rem;
  font-weight: 500;
  color: #ef4444;
}

.loading-state, .empty-state {
  color: #6b7280;
  font-style: italic;
}
</style>