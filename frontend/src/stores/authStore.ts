import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../services/api';

//menefinisikan tipe data User menggunakan type script
export interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
}

export const useAuthStore = defineStore('auth', () => {
    //mengambil data dari local storage jika user me-refresh halaman
    const user = ref<User | null>(JSON.parse(localStorage.getItem('user_data') || 'null'));
    const token = ref<string | null>(localStorage.getItem('access_token') || null);

    //cek apakah user sedang login???
    const isAuthenticated = computed(() => !!token.value);

    //fungsi Login dengan Laravel Sanctum PAT
    const login = async (credentials: Record<string, string>) => {
        const response = await api.post('/login', credentials);
        
        //store data ke pinia
        token.value = response.data.access_token;
        user.value = response.data.user;

        //store data ke local storage
        localStorage.setItem('access_token', token.value as string);
        localStorage.setItem('user_data', JSON.stringify(user.value));
    };

    //fungsi Logout untuk hapus/revoke token
    const logout = async () => {
        try {
            await api.post('/logout');
        } catch (error) {
            console.error('Gagal memanggil API logout', error);
        } finally {
            //hapus data dari state
            token.value = null;
            user.value = null;
            
            //hapus data dari local storage
            localStorage.removeItem('access_token');
            localStorage.removeItem('user_data');
        }
    };

    return { user, token, isAuthenticated, login, logout };
});