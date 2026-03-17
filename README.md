# Task Tracker Application
- Aplikasi manajemen tugas (Task Management) berbasis web. Proyek ini mencakup pengembangan RESTful API dengan Laravel, antarmuka pengguna dengan Vue.js 3 + TypeScript.

# Pada Backend rename file .env.example sehingga menjadi .env dan ubah mengikuti berikut :
- DB_CONNECTION=pgsql
- DB_HOST=127.0.0.1
- DB_PORT=5432
- DB_DATABASE= (isi dengan nama database kalian)
- DB_USERNAME=postgres
- DB_PASSWORD= (isi dengan password postgreeSQL kalian)

## Install Depedensi Backend Laravel
- **composer update**

## Install Depedensi Frontend Vue.js
- **npm install**

## Cara run yaitu dengan cara jalankan semua server baik Backend maupun Frontend
- **Backend** : php artisan serve
- **Frontend** : npm run dev
- Jalankan localhost milih Frontend

## Fitur Utama
- **Dashboard** : Statistik ringkasan proyek dan tugas.
- **Manajemen Proyek** : Operasi CRUD (Create, Read, Update, Delete) lengkap dengan filter.
- **Manajemen Tugas** : Manajemen tugas dengan fitur Soft Delete.
- **Kanban Board** : Visualisasi progres tugas per proyek.
- **Keamanan** : Autentikasi menggunakan Laravel Sanctum.

## Tech Stack
- **Backend** : Laravel 12
- **Frontend** : Vue.js 3 (Vite), Pinia, Tailwind CSS, TypeScript
- **Database** : PostgreSQL
- **Testing** : PHPUnit (Backend) & Vitest (Frontend)

## Clone Repositori
- https://github.com/Rahmadani05/Aplikasi-Task-Project-Tracker.git

## Akun Login
- **Email** : admin@coba.id
- **Password** : tes123
