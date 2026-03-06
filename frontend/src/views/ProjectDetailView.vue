<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <span class="text-white logo-text">Task</span><span class="text-blue logo-text">Tracker</span>
      </div>

      <nav class="sidebar-nav">
        <router-link :to="{ name: 'dashboard' }" class="nav-item">
          <span class="icon">📊</span> Dashboard
        </router-link>
        <router-link :to="{ name: 'projects' }" class="nav-item active">
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
      <div v-if="isLoading" class="loading-state">Memuat detail project...</div>
      
      <div v-else-if="project" class="project-detail-container">
        <button @click="goBack" class="btn-back">← Kembali</button>
        
        <div class="project-info-card">
          <div class="project-info-header">
            <h2>{{ project.name }}</h2>
            <div class="header-actions">
              <span :class="['status-badge', project.status]">
                {{ project.status === 'active' ? 'Active' : 'Archived' }}
              </span>
              <button class="btn-outline">Edit</button>
            </div>
          </div>
          <p class="project-desc">{{ project.description }}</p>
          
          <div class="project-stats">
            <div class="stat-item">
              <span class="stat-label">DIBUAT</span>
              <span class="stat-value">{{ formatDate(project.created_at) }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">TOTAL TASK</span>
              <span class="stat-value">{{ tasks.length }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">SELESAI</span>
              <span class="stat-value text-green">{{ completedTasksCount }} / {{ tasks.length }}</span>
            </div>
          </div>
        </div>

        <div class="kanban-header">
          <h3>Task</h3>
          <button class="btn-primary">+ Tambah Task</button>
        </div>

        <div class="kanban-board">
          <div 
            v-for="column in kanbanColumns" 
            :key="column.name" 
            class="kanban-column"
          >
            <div class="column-header" :class="column.colorClass">
              <span class="column-title">{{ column.name }}</span>
              <span class="column-count">{{ getTasksByCategory(column.name).length }}</span>
            </div>
            
            <div class="column-content">
              <div v-if="getTasksByCategory(column.name).length === 0" class="empty-card">
                Belum ada task
              </div>
              
              <div 
                v-for="task in getTasksByCategory(column.name)" 
                :key="task.id" 
                class="task-card"
              >
                <h4 class="task-title">{{ task.title }}</h4>
                <p class="task-date" :class="{'text-danger': isOverdue(task.due_date)}">
                  {{ formatShortDate(task.due_date) }}
                  <span v-if="isOverdue(task.due_date)">⚠️</span>
                </p>
                <div class="task-actions">
                  <button class="btn-card-action edit">Edit</button>
                  <button class="btn-card-action delete">Hapus</button>
                </div>
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
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import api from '../services/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isLoading = ref(true)
const project = ref<any>(null)
const tasks = ref<any[]>([])

// Definisi Kolom Kanban
const kanbanColumns = [
  { name: 'TODO', colorClass: 'border-blue' },
  { name: 'IN PROGRESS', colorClass: 'border-yellow' },
  { name: 'TESTING', colorClass: 'border-purple' },
  { name: 'DONE', colorClass: 'border-green' },
  { name: 'PENDING', colorClass: 'border-orange' }
]

const userInitial = computed(() => {
  const name = authStore.user?.name || 'U'
  return name.charAt(0).toUpperCase()
})

const completedTasksCount = computed(() => {
  return tasks.value.filter(t => t.category?.name.toUpperCase() === 'DONE').length
})

const fetchData = async () => {
  const projectId = route.params.id
  try {
    // Ambil detail project dan task yang memiliki project_id ini
    const [projectRes, tasksRes] = await Promise.all([
      api.get(`/projects/${projectId}`),
      api.get(`/tasks?project_id=${projectId}`)
    ])
    
    project.value = projectRes.data.data
    tasks.value = tasksRes.data.data
  } catch (error) {
    console.error('Gagal mengambil data project detail', error)
    alert('Project tidak ditemukan.')
    router.push({ name: 'projects' })
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Mengelompokkan Task berdasarkan nama kategori
const getTasksByCategory = (categoryName: string) => {
  return tasks.value.filter(task => {
    return task.category?.name.toUpperCase() === categoryName.toUpperCase()
  })
}

const goBack = () => {
  router.push({ name: 'projects' })
}

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

// Helpers Formatting
const formatDate = (dateString: string) => {
  if(!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const formatShortDate = (dateString: string) => {
  if(!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
}

const isOverdue = (dateString: string) => {
  return new Date(dateString) < new Date()
}
</script>

<style scoped>
/* --- REUSE LAYOUT & SIDEBAR STYLES --- */
.app-layout { display: flex; min-height: 100vh; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.sidebar { width: 260px; background-color: #1a1e29; color: white; display: flex; flex-direction: column; flex-shrink: 0;}
.sidebar-header { padding: 1.5rem; font-size: 1.5rem; font-weight: bold; border-bottom: 1px solid #2d3748; }
.logo-text.text-white { color: #ffffff; }
.logo-text.text-blue { color: #3b82f6; }
.sidebar-nav { flex: 1; padding: 1rem 0; display: flex; flex-direction: column; }
.nav-item { padding: 0.75rem 1.5rem; color: #9ca3af; text-decoration: none; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; font-weight: 500;}
.nav-item:hover, .nav-item.active { background-color: #2d3748; color: #ffffff; }
.nav-item.active { border-left: 4px solid #3b82f6; }
.icon { font-size: 1.1rem; display: inline-block; width: 24px; text-align: center; }
.sidebar-footer { padding: 1rem; border-top: 1px solid #2d3748; }
.btn-logout { width: 100%; padding: 0.75rem; background: transparent; border: none; color: #9ca3af; text-align: left; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; margin-bottom: 1rem;}
.user-profile { display: flex; align-items: center; gap: 1rem; padding: 0.5rem; }
.avatar { width: 40px; height: 40px; background-color: #8b5cf6; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; color: white;}
.user-info p { margin: 0; line-height: 1.2; }
.user-name { font-size: 0.875rem; font-weight: bold; }
.user-role { font-size: 0.75rem; color: #9ca3af; }

/* --- MAIN CONTENT STYLES --- */
.main-content { flex: 1; padding: 2rem; overflow-x: hidden; display: flex; flex-direction: column; height: 100vh; box-sizing: border-box;}
.project-detail-container { display: flex; flex-direction: column; height: 100%; }

.btn-back { background: white; border: 1px solid #d1d5db; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600; color: #374151; margin-bottom: 1.5rem; align-self: flex-start; transition: all 0.2s;}
.btn-back:hover { background: #f3f4f6; }

/* PROJECT HEADER CARD */
.project-info-card { background: white; border-radius: 12px; padding: 1.5rem 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; border: 1px solid #e5e7eb;}
.project-info-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
.project-info-header h2 { margin: 0; font-size: 1.5rem; color: #111827; }
.header-actions { display: flex; align-items: center; gap: 1rem; }
.status-badge { padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.status-badge.active { background-color: #dcfce7; color: #166534; }
.status-badge.archived { background-color: #f3f4f6; color: #374151; }
.btn-outline { background: transparent; border: 1px solid #d1d5db; padding: 0.4rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500; color: #374151; }
.project-desc { color: #6b7280; margin-bottom: 2rem; font-size: 0.95rem;}

.project-stats { display: flex; gap: 3rem; }
.stat-item { display: flex; flex-direction: column; gap: 0.25rem; }
.stat-label { font-size: 0.75rem; color: #6b7280; font-weight: 600; }
.stat-value { font-size: 1rem; font-weight: bold; color: #111827; }
.text-green { color: #16a34a; }

/* KANBAN BOARD SECTION */
.kanban-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.kanban-header h3 { margin: 0; font-size: 1.25rem; color: #111827; }
.btn-primary { background-color: #3b82f6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 600; cursor: pointer; }

.kanban-board { display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 1rem; flex: 1; align-items: flex-start;}
.kanban-column { background: white; border-radius: 12px; min-width: 280px; max-width: 280px; padding: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display: flex; flex-direction: column; max-height: 100%;}

/* KANBAN COLUMN HEADERS */
.column-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; margin-bottom: 1rem; border-bottom: 2px solid #e5e7eb; }
.column-title { font-weight: bold; font-size: 0.85rem; color: #374151; letter-spacing: 0.05em; }
.column-count { background: #f3f4f6; color: #6b7280; font-size: 0.75rem; padding: 0.1rem 0.5rem; border-radius: 9999px; font-weight: bold; }

/* Border Colors matching mockup */
.border-blue { border-bottom-color: #3b82f6; }
.border-yellow { border-bottom-color: #f59e0b; }
.border-purple { border-bottom-color: #8b5cf6; }
.border-green { border-bottom-color: #10b981; }
.border-orange { border-bottom-color: #f97316; }

/* KANBAN CARDS */
.column-content { display: flex; flex-direction: column; gap: 0.75rem; overflow-y: auto; padding-right: 0.25rem;}
.empty-card { text-align: center; color: #9ca3af; font-size: 0.85rem; padding: 1rem; border: 1px dashed #d1d5db; border-radius: 8px;}
.task-card { background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: box-shadow 0.2s;}
.task-card:hover { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
.task-title { margin: 0 0 0.5rem 0; font-size: 0.95rem; color: #111827; }
.task-date { margin: 0 0 1rem 0; font-size: 0.8rem; color: #6b7280; }
.text-danger { color: #ef4444; font-weight: 600; }

.task-actions { display: flex; gap: 0.5rem; }
.btn-card-action { padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; }
.btn-card-action.edit { background-color: #eff6ff; color: #3b82f6; border-color: #dbeafe;}
.btn-card-action.delete { background-color: #fef2f2; color: #ef4444; border-color: #fee2e2;}

.loading-state { padding: 3rem; text-align: center; color: #6b7280; font-style: italic; }
</style>