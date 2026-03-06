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
      <header class="content-header">
        <div>
          <h1>Project</h1>
          <p class="subtitle">Kelola semua project</p>
        </div>
        <button @click="openAddModal" class="btn-primary">+ Tambah Project</button>
      </header>

      <div class="filter-section">
        <div class="search-wrapper">
          <span class="search-icon">🔍</span>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Cari project..." 
            class="filter-input search-input"
          />
        </div>
        
        <select v-model="selectedStatus" class="filter-input status-select">
          <option value="">Semua Status</option>
          <option value="active">Active</option>
          <option value="archived">Archived</option>
        </select>
      </div>

      <div class="table-container">
        <div v-if="isLoading" class="loading-state">Memuat data project...</div>
        
        <table v-else class="project-table">
          <thead>
            <tr>
              <th>NAMA</th>
              <th class="desc-col">DESKRIPSI</th>
              <th>STATUS</th>
              <th>TASK</th> 
              <th>DIBUAT</th>
              <th>AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="filteredProjects.length === 0">
              <td colspan="6" class="empty-state">Tidak ada project yang ditemukan.</td>
            </tr>
            <tr v-for="project in filteredProjects" :key="project.id">
              <td class="font-bold project-name">{{ project.name }}</td>
              <td class="text-gray desc-cell" :title="project.description">{{ project.description }}</td>
              <td>
                <span :class="['status-badge', project.status]">
                  {{ project.status === 'active' ? 'Active' : 'Archived' }}
                </span>
              </td>
              <td class="text-center font-bold">-</td> 
              <td class="text-gray">{{ formatDate(project.created_at || new Date()) }}</td>
              <td>
                <div class="action-buttons">
                  <button @click="goToDetail(project.id)" class="btn-action detail">Detail</button>
                  <button @click="openEditModal(project)" class="btn-action edit">Edit</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
           <h2>{{ isEdit ? 'Edit Project' : 'Tambah Project Baru' }}</h2>
           <button @click="closeModal" class="btn-close">✕</button>
        </div>
       
        <form @submit.prevent="saveProject">
          <div class="form-group">
            <label>Nama Project</label>
            <input type="text" v-model="form.name" required placeholder="Masukkan nama project" />
          </div>
          
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea v-model="form.description" rows="3" required placeholder="Deskripsi project..."></textarea>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select v-model="form.status">
              <option value="active">Active</option>
              <option value="archived">Archived</option>
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
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import api from '../services/api'

const authStore = useAuthStore()
const router = useRouter()

const projects = ref<any[]>([])
const isLoading = ref(true)
const isSaving = ref(false)

//filter state
const searchQuery = ref('')
const selectedStatus = ref('')

//modal state
const isModalOpen = ref(false)
const isEdit = ref(false)
const form = ref({
  id: null as number | null,
  name: '',
  description: '',
  status: 'active'
})

const userInitial = computed(() => {
  const name = authStore.user?.name || 'U'
  return name.charAt(0).toUpperCase()
})

//fetch data
const fetchProjects = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/projects')
    projects.value = response.data.data
  } catch (error) {
    console.error('Gagal mengambil project', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchProjects()
})

//computed filter
const filteredProjects = computed(() => {
  return projects.value.filter(project => {
    const matchSearch = project.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchStatus = selectedStatus.value === '' || project.status === selectedStatus.value
    return matchSearch && matchStatus
  })
})

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

//logika modal dan form
const openAddModal = () => {
  isEdit.value = false
  form.value = { id: null, name: '', description: '', status: 'active' }
  isModalOpen.value = true
}

const openEditModal = (project: any) => {
  isEdit.value = true
  form.value = { ...project }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

const saveProject = async () => {
  isSaving.value = true
  try {
    if (isEdit.value) {
      await api.put(`/projects/${form.value.id}`, form.value)
    } else {
      await api.post('/projects', form.value)
    }
    closeModal()
    fetchProjects() // Refresh data
  } catch (error: any) {
    alert(error.response?.data?.message || 'Terjadi kesalahan saat menyimpan project.')
  } finally {
    isSaving.value = false
  }
}

//navigasi ke halaman detail
const goToDetail = (id: number) => {
  router.push({ name: 'project-detail', params: { id } })
}

//helper format tanggal
const formatDate = (dateString: string | Date) => {
  if(!dateString) return '-';
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<style scoped>
/* --- reuse  layout dan sidebar styles --- */
.app-layout { display: flex; min-height: 100vh; background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.sidebar { width: 260px; background-color: #1a1e29; color: white; display: flex; flex-direction: column; }
.sidebar-header { padding: 1.5rem; font-size: 1.5rem; font-weight: bold; border-bottom: 1px solid #2d3748; display: flex; align-items: center; gap: 0.5rem;}
.logo-text.text-white { color: #ffffff; }
.logo-text.text-blue { color: #3b82f6; }
.sidebar-nav { flex: 1; padding: 1rem 0; display: flex; flex-direction: column; }
.nav-item { padding: 0.75rem 1.5rem; color: #9ca3af; text-decoration: none; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; font-weight: 500; font-size: 0.95rem;}
.nav-item:hover, .nav-item.active { background-color: #2d3748; color: #ffffff; }
.nav-item.active { border-left: 4px solid #3b82f6; }
.icon { font-size: 1.1rem; display: inline-block; width: 24px; text-align: center; }
.sidebar-footer { padding: 1rem; border-top: 1px solid #2d3748; }
.btn-logout { width: 100%; padding: 0.75rem; background: transparent; border: none; color: #9ca3af; text-align: left; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; margin-bottom: 1rem; transition: color 0.2s; font-size: 0.95rem; font-weight: 500;}
.btn-logout:hover { color: #ef4444; }
.user-profile { display: flex; align-items: center; gap: 1rem; padding: 0.5rem; }
.avatar { width: 40px; height: 40px; background-color: #8b5cf6; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; color: white;}
.user-info p { margin: 0; line-height: 1.2; }
.user-name { font-size: 0.875rem; font-weight: bold; color: white; }
.user-role { font-size: 0.75rem; color: #9ca3af; }

/* --- main content styles --- */
.main-content { flex: 1; padding: 2rem; overflow-y: auto; }
.content-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; }
.content-header h1 { margin: 0 0 0.25rem 0; color: #111827; font-size: 1.8rem;}
.subtitle { color: #6b7280; margin-top: 0; font-size: 1rem;}

.btn-primary { background-color: #3b82f6; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background-color 0.2s; font-size: 0.9rem;}
.btn-primary:hover { background-color: #2563eb; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }

/* --- filters --- */
.filter-section { display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: center; }
.search-wrapper { position: relative; flex: 1; max-width: 400px; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.9rem;}
.filter-input { padding: 0.7rem 1rem; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; background: white; font-size: 0.9rem; color: #374151; transition: border-color 0.2s;}
.search-input { width: 100%; padding-left: 2.5rem; box-sizing: border-box; }
.status-select { min-width: 150px; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; }
.filter-input:focus { border-color: #3b82f6; }

/* --- project table sytles --- */
.table-container { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; border: 1px solid #e5e7eb;}
.project-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
.project-table th { background-color: #f9fafb; color: #6b7280; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; letter-spacing: 0.05em;}
.project-table td { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; font-size: 0.9rem; color: #111827; vertical-align: middle;}
.project-table tr:last-child td { border-bottom: none; }
.project-table tbody tr:hover { background-color: #f9fafb; }

.project-name { font-size: 1rem; }
.desc-col { width: 30%; }
.desc-cell { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px; }
.font-bold { font-weight: 600; }
.text-gray { color: #6b7280; }
.text-center { text-align: center; }

/* --- badges --- */
.status-badge { padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; display: inline-block;}
.status-badge.active { background-color: #dcfce7; color: #166534; }
.status-badge.archived { background-color: #f3f4f6; color: #374151; }

/* --- action buttons --- */
.action-buttons { display: flex; gap: 0.5rem; }
.btn-action { padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; transition: all 0.2s;}
.btn-action.detail { background-color: #dfdfdf; color: #000000; border-color: #ffffff;}
.btn-action.detail:hover { background-color: #dbeafe; border-color: #bfdbfe;}
.btn-action.edit { background-color: #cfdcf8; color: #004cc5; border-color: #d1d5db;}
.btn-action.edit:hover { background-color: #f3f4f6; border-color: #9ca3af;}

/* --- modal sytles --- */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 50; backdrop-filter: blur(2px);}
.modal-content { background: white; padding: 2rem; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.modal-header h2 { margin: 0; color: #111827; font-size: 1.5rem;}
.btn-close { background: transparent; border: none; font-size: 1.5rem; color: #9ca3af; cursor: pointer; padding: 0;}
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; outline: none; box-sizing: border-box; transition: border-color 0.2s; font-family: inherit;}
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}
.modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2.5rem; }
.btn-cancel { background-color: white; color: #374151; border: 1px solid #d1d5db; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem; transition: all 0.2s;}
.btn-cancel:hover { background-color: #f9fafb; border-color: #9ca3af;}

.loading-state, .empty-state { padding: 3rem; text-align: center; color: #6b7280; font-style: italic; }
</style>