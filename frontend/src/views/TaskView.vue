<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <span class="text-white logo-text">Task</span><span class="text-blue logo-text">Tracker</span>
      </div>

      <!-- //icon pakai bawaan windows (tombol windows + .) -->
      <nav class="sidebar-nav">
        <router-link :to="{ name: 'dashboard' }" class="nav-item">
          <span class="icon">📊</span> Dashboard
        </router-link>
        <router-link :to="{ name: 'projects' }" class="nav-item">
          <span class="icon">📁</span> Project
        </router-link>
        <router-link :to="{ name: 'tasks' }" class="nav-item active">
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
        <div>
          <h1>Task</h1>
          <p class="subtitle">Semua task lintas project</p>
        </div>
        <button @click="openAddModal" class="btn-primary">+ Tambah Task</button>
      </header>

      <div class="filter-section">
        <div class="search-wrapper">
          <span class="search-icon">🔍</span>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Cari task..." 
            class="filter-input search-input"
          />
        </div>
        
        <select v-model="selectedCategory" class="filter-input">
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>

        <select v-model="selectedProject" class="filter-input">
          <option value="">Semua Project</option>
          <option v-for="proj in projects" :key="proj.id" :value="proj.id">
            {{ proj.name }}
          </option>
        </select>
      </div>

      <div class="table-container">
        <div v-if="isLoading" class="loading-state">Memuat data task...</div>
        
        <table v-else class="task-table">
          <thead>
            <tr>
              <th>JUDUL TASK</th>
              <th>PROJECT</th>
              <th>KATEGORI</th>
              <th>DUE DATE</th>
              <th>AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="filteredTasks.length === 0">
              <td colspan="5" class="empty-state">Tidak ada task yang ditemukan.</td>
            </tr>
            <tr v-for="task in filteredTasks" :key="task.id">
              <td class="font-bold">{{ task.title }}</td>
              <td class="text-gray">{{ task.project?.name || '-' }}</td>
              <td>
                <span :class="['cat-badge', getCategoryClass(task.category?.name)]">
                  {{ task.category?.name }}
                </span>
              </td>
              <td>
                <span :class="{'text-danger': isOverdue(task.due_date)}">
                  {{ formatDate(task.due_date) }} 
                  <span v-if="isOverdue(task.due_date)">⚠️</span>
                </span>
              </td>
              <td class="action-buttons">
                <button @click="openEditModal(task)" class="btn-action edit">Edit</button>
                <button @click="openDeleteModal(task)" class="btn-action delete">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
           <h2>{{ isEdit ? 'Edit Task' : 'Tambah Task Baru' }}</h2>
           <button @click="closeModal" class="btn-close">✕</button>
        </div>
        
        <form @submit.prevent="saveTask">
          <div class="form-group">
            <label>Judul Task</label>
            <input type="text" v-model="form.title" required placeholder="Masukkan judul task" />
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea v-model="form.description" rows="3" required placeholder="Deskripsi singkat..."></textarea>
          </div>
          
          <div class="form-row">
            <div class="form-group half-width">
              <label>Kategori</label>
              <select v-model="form.category_id" required>
                <option value="" disabled>Pilih Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <div class="form-group half-width">
              <label>Due Date</label>
              <input type="date" v-model="form.due_date" required />
            </div>
          </div>

          <div class="form-group">
            <label>Project</label>
            <select v-model="form.project_id" required :disabled="isEdit && !form.project_id">
              <option value="" disabled>Pilih Project</option>
              <option v-for="proj in projects" :key="proj.id" :value="proj.id">
                {{ proj.name }}
              </option>
            </select>
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn-cancel">Batal</button>
            <button type="submit" class="btn-primary" :disabled="isSaving">
              {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div v-if="isDeleteModalOpen" class="modal-overlay">
      <div class="modal-content delete-modal">
        <button @click="closeDeleteModal" class="btn-close-absolute">✕</button>
        <div class="delete-icon">🗑️</div>
        <h2>Hapus Task?</h2>
        <p class="delete-desc">
          Task <strong>"{{ taskToDelete?.title }}"</strong> akan dihapus. Data task masih tersimpan di sistem (soft delete) dan bisa dipulihkan jika diperlukan.
        </p>
        <div class="modal-actions-center">
          <button @click="closeDeleteModal" class="btn-cancel">Batal</button>
          <button @click="executeDelete" class="btn-danger">Ya, Hapus</button>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import api from '../services/api'

const authStore = useAuthStore()
const router = useRouter()

//state data
const tasks = ref<any[]>([])
const projects = ref<any[]>([])
const isLoading = ref(true)
const isSaving = ref(false)

//seeder backend
const categories = ref([
  { id: 1, name: 'TODO' },
  { id: 2, name: 'IN PROGRESS' },
  { id: 3, name: 'TESTING' },
  { id: 4, name: 'DONE' },
  { id: 5, name: 'PENDING' }
])

//filter state
const searchQuery = ref('')
const selectedCategory = ref<number | ''>('')
const selectedProject = ref<number | ''>('')

//modal state
const isModalOpen = ref(false)
const isEdit = ref(false)
const form = ref({
  id: null as number | null,
  title: '',
  project_id: '' as number | '',
  category_id: '' as number | '',
  due_date: '',
  description: ''
})

const userInitial = computed(() => {
  const name = authStore.user?.name || 'U'
  return name.charAt(0).toUpperCase()
})

//fetch data
const fetchData = async () => {
  isLoading.value = true
  try {
    const [taskRes, projRes] = await Promise.all([
      api.get('/tasks'),
      api.get('/projects')
    ])
    tasks.value = taskRes.data.data
    projects.value = projRes.data.data
  } catch (error) {
    console.error('Gagal mengambil data', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchData()
})

//computed filter
const filteredTasks = computed(() => {
  return tasks.value.filter(task => {
    const matchSearch = task.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchCat = selectedCategory.value === '' || task.category_id === selectedCategory.value
    const matchProj = selectedProject.value === '' || task.project_id === selectedProject.value
    return matchSearch && matchCat && matchProj
  })
})

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

// === logika modal,CRUD ===
const openAddModal = () => {
  isEdit.value = false
  form.value = { id: null, title: '', project_id: '', category_id: '', due_date: '', description: '' }
  isModalOpen.value = true
}

const openEditModal = (task: any) => {
  isEdit.value = true
  form.value = { 
    id: task.id, 
    title: task.title, 
    project_id: task.project_id, 
    category_id: task.category_id, 
    due_date: task.due_date, 
    description: task.description 
  }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

const saveTask = async () => {
  isSaving.value = true
  try {
    if (isEdit.value) {
      await api.put(`/tasks/${form.value.id}`, form.value)
    } else {
      await api.post('/tasks', form.value)
    }
    closeModal()
    fetchData() //refresh tabel
  } catch (error: any) {
    alert(error.response?.data?.message || 'Terjadi kesalahan saat menyimpan task.')
  } finally {
    isSaving.value = false
  }
}

const isDeleteModalOpen = ref(false)
const taskToDelete = ref<any>(null)

const openDeleteModal = (task: any) => {
  taskToDelete.value = task
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false
  taskToDelete.value = null
}

const executeDelete = async () => {
  if (!taskToDelete.value) return
  
  try {
    await api.delete(`/tasks/${taskToDelete.value.id}`)
    fetchData()
    closeDeleteModal()
  } catch (error) {
    alert('Gagal menghapus task.')
  }
}

// === helper UI formatting ===
const formatDate = (dateString: string) => {
  const options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short', year: 'numeric' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

const isOverdue = (dateString: string) => {
  return new Date(dateString) < new Date()
}

const getCategoryClass = (catName: string = '') => {
  const name = catName.toUpperCase()
  if (name === 'DONE') return 'cat-done'
  if (name === 'IN PROGRESS') return 'cat-progress'
  if (name === 'TESTING') return 'cat-testing'
  if (name === 'PENDING') return 'cat-pending'
  return 'cat-todo'
}
</script>

<style scoped>
/* reuse sidebar dan layout */
.app-layout { display: flex; min-height: 100vh; background-color: #f3f4f6; }
.sidebar { width: 260px; background-color: #1a1e29; color: white; display: flex; flex-direction: column; }
.sidebar-header { padding: 1.5rem; font-size: 1.5rem; font-weight: bold; border-bottom: 1px solid #2d3748; }
.logo-text.text-white { color: #ffffff; }
.logo-text.text-blue { color: #3b82f6; }
.sidebar-nav { flex: 1; padding: 1rem 0; display: flex; flex-direction: column; }
.nav-item { padding: 0.75rem 1.5rem; color: #9ca3af; text-decoration: none; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; font-weight: 500; }
.nav-item:hover, .nav-item.active { background-color: #2d3748; color: #ffffff; border-left: 4px solid #3b82f6;}
.sidebar-footer { padding: 1rem; border-top: 1px solid #2d3748; }
.btn-logout { width: 100%; padding: 0.75rem; background: transparent; border: none; color: #9ca3af; text-align: left; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; margin-bottom: 1rem; transition: color 0.2s; }
.btn-logout:hover { color: #ef4444; }
.user-profile { display: flex; align-items: center; gap: 1rem; padding: 0.5rem; }
.avatar { width: 40px; height: 40px; background-color: #8b5cf6; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; }
.user-info p { margin: 0; line-height: 1.2; }
.user-name { font-size: 0.875rem; font-weight: bold; color: white; }
.user-role { font-size: 0.75rem; color: #9ca3af; }

/* main ccontent */
.main-content { flex: 1; padding: 2rem; overflow-y: auto; }
.content-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.content-header h1 { margin: 0 0 0.25rem 0; color: #111827; }
.subtitle { color: #6b7280; margin-top: 0; }
.btn-primary { background-color: #3b82f6; color: white; border: none; padding: 0.75rem 1.25rem; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-primary:hover { background-color: #4338ca; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }

/* fitlers */
.filter-section { display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: center; }
.search-wrapper { position: relative; flex: 1; max-width: 400px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.filter-input { padding: 0.6rem 1rem; border: 1px solid #e5e7eb; border-radius: 6px; outline: none; background: white; font-size: 0.875rem; color: #374151;}
.search-input { width: 100%; padding-left: 2.2rem; box-sizing: border-box; }
.filter-input:focus { border-color: #3b82f6; }

/* table */
.table-container { background: white; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.task-table { width: 100%; border-collapse: collapse; text-align: left; }
.task-table th { background-color: #f9fafb; color: #6b7280; font-weight: 600; font-size: 0.75rem; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
.task-table td { padding: 1rem; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem; color: #111827; }
.task-table tr:last-child td { border-bottom: none; }
.font-bold { font-weight: 600; }
.text-gray { color: #6b7280; }
.text-danger { color: #ef4444; font-weight: 600; }

/* badges */
.cat-badge { padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.cat-todo { background-color: #e0f2fe; color: #0284c7; }
.cat-progress { background-color: #fef3c7; color: #d97706; }
.cat-testing { background-color: #f3e8ff; color: #7e22ce; }
.cat-done { background-color: #dcfce7; color: #166534; }
.cat-pending { background-color: #ffedd5; color: #c2410c; }

/* actions */
.action-buttons { display: flex; gap: 0.5rem; }
.btn-action { padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; border: none; }
.btn-action.edit { background-color: #eff6ff; color: #3b82f6; }
.btn-action.edit:hover { background-color: #dbeafe; }
.btn-action.delete { background-color: #fef2f2; color: #ef4444; }
.btn-action.delete:hover { background-color: #fee2e2; }

/* modal */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 50; }
.modal-content { background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
.modal-content h2 { margin-top: 0; margin-bottom: 1.5rem; color: #111827; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; outline: none; box-sizing: border-box; }
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: #3b82f6; }
.modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }
.btn-cancel { background-color: transparent; color: #6b7280; border: none; padding: 0.75rem 1.25rem; font-weight: 600; cursor: pointer; }

/* modal header dan kolom sejajar */
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.modal-header h2 { margin: 0; color: #111827; font-size: 1.5rem;}
.btn-close { background: transparent; border: none; font-size: 1.2rem; color: #9ca3af; cursor: pointer; padding: 0.5rem; border-radius: 50%; background-color: #f3f4f6; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;}
.btn-close:hover { background-color: #e5e7eb; }

/* grid untuk 2 kolom */
.form-row { display: flex; gap: 1rem; }
.half-width { flex: 1; }

/* cancel button */
.btn-cancel { background-color: white; color: #374151; border: 1px solid #d1d5db; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem; transition: all 0.2s;}
.btn-cancel:hover { background-color: #f9fafb; border-color: #9ca3af;}

/* delete modal sttles */
.delete-modal { text-align: center; max-width: 400px; padding: 2.5rem 2rem; position: relative; }
.btn-close-absolute { position: absolute; top: 1rem; right: 1rem; background: #f3f4f6; border: none; font-size: 1.2rem; color: #9ca3af; cursor: pointer; padding: 0.5rem; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; }
.btn-close-absolute:hover { background: #e5e7eb; }
.delete-icon { font-size: 3.5rem; margin-bottom: 1rem; display: inline-block; }
.delete-modal h2 { margin-bottom: 0.75rem; color: #111827; }
.delete-desc { color: #6b7280; font-size: 0.95rem; line-height: 1.5; margin-bottom: 2rem; }
.modal-actions-center { display: flex; justify-content: center; gap: 1rem; }
.btn-danger { background-color: #ef4444; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem; transition: background-color 0.2s; }
.btn-danger:hover { background-color: #dc2626; }
</style>