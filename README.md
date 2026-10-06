# CobaAI - Laravel 13 AI Chat App

Aplikasi Chat AI modern dan responsif yang dibangun dengan **Laravel 13 (PHP 8.4)** dengan dukungan multi-provider untuk **NVIDIA NIM (Meta LLaMA 3.2, DeepSeek, GLM)** dan **Google Gemini API**.

---

## ✨ Fitur Utama

- **Multi-Model & Provider**:
  - **Meta LLaMA 3.2 11B Vision** (NVIDIA NIM - Cepat & Responsif)
  - **GLM 5.3** (NVIDIA NIM)
  - **DeepSeek v4.1 Flash** (NVIDIA NIM)
  - **Google Gemini Flash** (Google AI Studio)
- **Antarmuka Minimalis & Elegan**:
  - Tampilan modern dengan palet warna gelap yang nyaman di mata.
  - Dukungan rendering **Markdown** lengkap (tabel, daftar, blok kutipan).
  - **Syntax Highlighting** kode program lengkap dengan tombol *One-Click Copy Code*.
  - Auto-expanding input textarea (Enter kirim, Shift+Enter baris baru).
- **Manajemen Obrolan**:
  - Sesi obrolan tersimpan otomatis di database (SQLite).
  - Sidebar riwayat obrolan dengan fitur ganti obrolan, hapus obrolan, dan bersihkan pesan.
- **Tahan Banting & Cepat**:
  - Penanganan error dan timeout otomatis tanpa membuat UI macet.
  - Dropdown pemilih model langsung di header.

---

## 🚀 Panduan Instalasi & Menjalankan

### 1. Clone Repositori
```bash
git clone https://github.com/Novan-lin/cobaai.git
cd cobaai
```

### 2. Install Dependensi PHP
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` ke `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

Isi konfigurasi API key pada `.env`:
```env
AI_PROVIDER=nvidia

# NVIDIA NIM API (LLaMA, DeepSeek, GLM)
NVIDIA_API_KEY=your_nvidia_api_key_here
NVIDIA_BASE_URL=https://integrate.api.nvidia.com/v1
NVIDIA_MODEL=meta/llama-3.2-11b-vision-instruct

# Google Gemini API
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_MODEL=gemini-flash-latest
```

### 4. Database Migration
```bash
php artisan migrate
```

### 5. Jalankan Server
Jika menggunakan **Laravel Herd**, aplikasi langsung aktif di:
👉 `http://cobaai.test` (atau `http://laravel13.test`)

Atau jalankan via Artisan:
```bash
php artisan serve
```
Buka browser di `http://127.0.0.1:8000`.

---

## 🧪 Menjalankan Pengujian

```bash
php artisan test
```

---

## 📄 Lisensi
Open-source di bawah lisensi [MIT License](LICENSE).
