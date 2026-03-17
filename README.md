# Task Tracker Application
- Aplikasi manajemen tugas (Task Management) berbasis web. Proyek ini mencakup pengembangan RESTful API dengan Laravel, antarmuka pengguna dengan Vue.js 3 + TypeScript.

<img width="861" height="645" alt="image" src="https://github.com/user-attachments/assets/013882d9-3c3f-48a6-8fa1-e4ec9ea7c21f" />
<img width="1912" height="936" alt="image" src="https://github.com/user-attachments/assets/723d3303-bdf9-4536-af78-a75a2022a869" />
<img width="1917" height="933" alt="image" src="https://github.com/user-attachments/assets/c4f5c1a4-12d3-41c4-89ec-1ad9f265f686" />
<img width="1919" height="936" alt="image" src="https://github.com/user-attachments/assets/a9ec473c-971e-44eb-a02a-c5395d7fabe6" />

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
