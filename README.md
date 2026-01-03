# Sistem Pengelolaan Tugas Mahasiswa (SPTM)
## UIN Alauddin Makassar

Platform manajemen tugas dan konsultasi akademik berbasis web untuk memudahkan interaksi antara dosen dan mahasiswa dalam pengelolaan tugas kuliah.

![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple)

## 🎯 Fitur Utama

### Fitur Wajib
- ✅ **Upload Tugas** - Mahasiswa dapat mengumpulkan tugas dalam berbagai format
- ✅ **Komentar Dosen** - Feedback langsung dari dosen pada setiap submission
- ✅ **Status Revisi** - Tracking status (Pending, Diterima, Revisi, Ditolak)
- ✅ **Deadline Reminder** - Notifikasi otomatis menjelang deadline

### Fitur Tambahan
- 📊 **Dashboard Statistik** - Real-time analytics untuk mahasiswa dan dosen
- 👥 **Multi-Role System** - Support untuk Mahasiswa, Dosen, dan Admin
- 🔐 **Email Validation** - Integrasi dengan database kampus
- 📁 **File Management** - Upload, download, dan manajemen file tugas
- 🔔 **Real-time Notifications** - System notifikasi yang comprehensive
- 🔄 **Multi-attempt Submissions** - Support revisi berulang
- ⏰ **Late Submission Handling** - Pengaturan pengumpulan terlambat dengan penalty
- 📝 **Grading System** - Penilaian 0-100 dengan komentar
- 📦 **Batch Grading** - Nilai banyak mahasiswa sekaligus
- 👨‍🎓 **Class Enrollment** - Manajemen pendaftaran mahasiswa per kelas
- 📈 **Performance Tracking** - Monitoring progress mahasiswa
- 📅 **Calendar View** - Visualisasi deadline dalam kalender
- 📄 **Export Reports** - Export laporan dalam format PDF
- 🎨 **Responsive Design** - Mobile-friendly interface

## 🛠️ Teknologi

- **Backend**: Laravel 10.x
- **Frontend**: Bootstrap 5.3, Bootstrap Icons
- **Database**: MySQL
- **Authentication**: Laravel built-in Auth
- **File Storage**: Laravel Storage

## 📋 Persyaratan Sistem

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (untuk asset compilation)
- Web Server (Apache/Nginx)

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone [repository-url]
cd tugas-akademik
```

### 2. Install Dependencies
```bash
composer install
npm install && npm run build
```

### 3. Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tugas_akademik
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Setup Database
```bash
# Buat database
mysql -u root -p
CREATE DATABASE tugas_akademik;
exit;

# Jalankan migration
php artisan migrate

# Jalankan seeder untuk data dummy
php artisan db:seed
```

### 5. Setup Storage
```bash
php artisan storage:link
```

### 6. Set Permissions (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
```

### 7. Jalankan Aplikasi
```bash
php artisan serve
```

Buka browser dan akses: `http://localhost:8000`

## 👤 Akun Default

### Admin
- Email: `admin@uin-alauddin.ac.id`
- Password: `password`

### Dosen
- Email: `ahmad.yani@uin-alauddin.ac.id`
- Password: `password`

- Email: `siti.nurhaliza@uin-alauddin.ac.id`
- Password: `password`

### Mahasiswa
- Email: `muhammad.rizki@uin-alauddin.ac.id`
- Password: `password`

- Email: `fatimah.azzahra@uin-alauddin.ac.id`
- Password: `password`

## 📱 Fitur per Role

### Mahasiswa
- Lihat mata kuliah yang diambil
- Lihat dan download tugas
- Submit tugas dengan file attachment
- Lihat status dan nilai tugas
- Revisi tugas yang ditolak
- Komunikasi dengan dosen via komentar
- Lihat statistik performa
- Calendar view untuk deadline

### Dosen
- Kelola kelas yang diampu
- Buat dan kelola tugas
- Lihat semua submission mahasiswa
- Beri nilai dan feedback
- Batch grading untuk efisiensi
- Kelola enrollment mahasiswa
- Lihat statistik kelas
- Export laporan nilai

### Admin
- Kelola semua user (Mahasiswa, Dosen)
- Kelola mata kuliah
- Lihat semua kelas
- Generate reports sistem
- Kelola pengaturan sistem

## 🔧 Command Artisan

```bash
# Send deadline reminders
php artisan reminders:deadline

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Reset database (WARNING: Will delete all data!)
php artisan migrate:fresh --seed
```

## 📝 Schedule Tasks

Untuk menjalankan scheduled tasks (deadline reminders), tambahkan ini ke crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Atau jalankan manual:
```bash
php artisan schedule:work
```

## 🎨 Tema & Desain

Aplikasi menggunakan tema warna hijau UIN Alauddin Makassar:
- Primary Color: `#006838` (Hijau UIN)
- Secondary Color: `#004d28` (Hijau Gelap)
- Accent Color: `#e8f5e9` (Hijau Terang)

## 📂 Struktur Folder

```
tugas-akademik/
├── app/
│   ├── Console/Commands/
│   ├── Http/Controllers/
│   ├── Http/Middleware/
│   ├── Models/
│   ├── Services/
│   └── Helpers/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── auth/
│       ├── dashboard/
│       ├── tugas/
│       ├── kelas/
│       ├── notifications/
│       └── profile/
├── routes/
│   └── web.php
└── storage/
    ├── app/
    │   ├── public/
    │   │   ├── avatars/
    │   │   ├── tugas-files/
    │   │   ├── submissions/
    │   │   └── komentar-files/
    └── logs/
```

## 🔒 Security

- CSRF Protection pada semua form
- Input validation & sanitization
- File upload validation
- Role-based access control
- Password hashing menggunakan bcrypt
- XSS protection

## 🐛 Troubleshooting

### Error: Class not found
```bash
composer dump-autoload
```

### Error: Storage link not working
```bash
php artisan storage:link
```

### Error: Permission denied
```bash
sudo chmod -R 777 storage bootstrap/cache
```

### Error: Migration failed
```bash
php artisan migrate:fresh
```

## 📧 Support

Untuk pertanyaan atau issue, silakan hubungi:
- Email: support@uin-alauddin.ac.id

## 📄 License

Copyright © 2025 UIN Alauddin Makassar. All rights reserved.

## 🙏 Credits

Developed with ❤️ for UIN Alauddin Makassar

---

**Version**: 1.0.0  
**Last Updated**: January 2025