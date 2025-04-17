# To-Do List Application

## Deskripsi
Aplikasi To-Do List ini memungkinkan pengguna untuk menambahkan, menampilkan, mengedit, dan menghapus tugas. Pengguna juga dapat mengelompokkan tugas berdasarkan proyek.

## Fitur Utama
- **Menambah Tugas**: Pengguna dapat menambahkan tugas baru.
- **Menampilkan Daftar Tugas**: Semua tugas yang telah ditambahkan akan ditampilkan.
- **Mengedit Tugas**: Pengguna dapat mengubah status tugas menjadi selesai.
- **Menghapus Tugas**: Pengguna dapat menghapus tugas dari daftar.
- **Manajemen Proyek**: Pengguna dapat mengelompokkan tugas berdasarkan proyek.
- **Keamanan**: Menggunakan prepared statements untuk mencegah SQL Injection.

## Teknologi yang Digunakan
- PHP (Backend)
- MySQL (Database)
- HTML, CSS, JavaScript (Frontend)
- SweetAlert2 (Konfirmasi aksi penghapusan)

## Cara Instalasi
1. Clone repositori ini atau unduh file ZIP.
2. Pastikan server lokal Anda berjalan (XAMPP, MAMP, atau WAMP).
3. Buat database baru di MySQL dengan nama `todo_list`.
4. Import file `database.sql` ke dalam database.
5. Konfigurasi database di file `config.php`.
6. Jalankan aplikasi melalui browser dengan mengakses `http://localhost/todo-list/`.

## Struktur Folder
```
/todo-list
├── assets/          # File CSS, JS, dan gambar
├── config.php       # Konfigurasi koneksi database
├── functions.php    # Fungsi bantuan untuk aplikasi
├── index.php        # Halaman utama aplikasi
├── login.php        # Halaman login
├── register.php     # Halaman registrasi pengguna
├── logout.php       # Proses logout
├── README.md        # Dokumentasi proyek
└── testing.md       # Dokumentasi pengujian
```

## Cara Menggunakan
1. **Registrasi Akun**: Pengguna harus mendaftar sebelum menggunakan aplikasi.
2. **Login**: Masukkan kredensial yang benar untuk mengakses aplikasi.
3. **Menambahkan Tugas**: Ketik tugas dan tekan tombol tambah.
4. **Mengedit atau Menandai Selesai**: Klik tugas yang ingin diubah.
5. **Menghapus Tugas**: Klik tombol hapus pada tugas yang diinginkan.
6. **Logout**: Klik tombol logout untuk keluar dari aplikasi.

## Kontributor
- Developer: Mzx1x

## Lisensi
Proyek ini berlisensi MIT. Anda bebas menggunakannya dengan tetap memberikan kredit kepada pembuat asli.

