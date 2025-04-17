# Pengujian Aplikasi To-Do List

## 1. Pengujian Fungsional
### 1.1 Registrasi Pengguna
**Skenario:**
- Masukkan username dan password baru
- Klik tombol "Register"
- Pastikan pengguna terdaftar dalam database
- Pastikan pengguna dapat login setelah registrasi

**Hasil yang Diharapkan:**
- Pengguna berhasil terdaftar dan diarahkan ke halaman login
- Tidak ada duplikasi akun dengan username yang sama

### 1.2 Login
**Skenario:**
- Masukkan username dan password yang benar
- Klik tombol "Login"
- Masukkan username atau password yang salah

**Hasil yang Diharapkan:**
- Jika benar, pengguna diarahkan ke halaman utama
- Jika salah, muncul pesan error "Username atau password salah"

### 1.3 Menambah Tugas
**Skenario:**
- Masukkan teks tugas pada input
- Klik tombol "Tambah"
- Pastikan tugas ditambahkan ke daftar tugas

**Hasil yang Diharapkan:**
- Tugas baru muncul dalam daftar
- Data tugas tersimpan dalam database

### 1.4 Mengedit Tugas
**Skenario:**
- Klik tombol "Edit" pada salah satu tugas
- Ubah nama tugas atau statusnya
- Klik tombol "Simpan"

**Hasil yang Diharapkan:**
- Perubahan disimpan dalam database
- Daftar tugas diperbarui

### 1.5 Menghapus Tugas
**Skenario:**
- Klik tombol "Hapus" pada salah satu tugas
- Konfirmasi penghapusan
- Pastikan tugas dihapus dari database

**Hasil yang Diharapkan:**
- Tugas dihapus dari daftar dan database
- Tidak bisa menghapus tugas yang tidak ada

## 2. Pengujian Keamanan
### 2.1 SQL Injection
**Skenario:**
- Coba masukkan karakter berbahaya pada input seperti `' OR '1'='1' --`

**Hasil yang Diharapkan:**
- Input difilter dengan benar
- Sistem tidak rentan terhadap SQL Injection

### 2.2 Cross-Site Scripting (XSS)
**Skenario:**
- Masukkan skrip `<script>alert('Hacked')</script>` dalam input

**Hasil yang Diharapkan:**
- Skrip tidak dieksekusi
- Data yang ditampilkan di-escape dengan benar

## 3. Pengujian Antarmuka Pengguna
### 3.1 Responsivitas
**Skenario:**
- Buka aplikasi di berbagai ukuran layar (mobile, tablet, desktop)

**Hasil yang Diharapkan:**
- Tampilan tetap rapi dan mudah digunakan

### 3.2 Penggunaan UX
**Skenario:**
- Periksa apakah tombol, input, dan navigasi mudah digunakan

**Hasil yang Diharapkan:**
- Pengguna dapat dengan mudah menavigasi aplikasi tanpa kesulitan

---

### Catatan Tambahan
- Pastikan semua fitur berjalan sesuai dengan spesifikasi
- Laporkan bug jika ditemukan

