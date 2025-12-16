# 🚀 Setup Guide - Palettopia Backend

Panduan lengkap untuk install & run Palettopia Backend di laptop/komputer lain.

---

## 📋 Requirements

Pastikan sudah terinstall:
- ✅ PHP >= 8.1
- ✅ Composer
- ✅ MySQL/MariaDB
- ✅ Git

**Cek versi:**
```bash
php -v
composer -v
mysql --version
git --version
```

---

## 🔧 Step 1: Clone Repository

```bash
# Clone dari GitHub
git clone https://github.com/savanapaaa/palettopia-be.git

# Masuk ke folder project
cd palettopia-be
```

---

## 📦 Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install
```

Tunggu sampai selesai (bisa 2-5 menit tergantung internet).

---

## ⚙️ Step 3: Setup Environment File

```bash
# Copy .env.example jadi .env
copy .env.example .env
```

**Atau di Linux/Mac:**
```bash
cp .env.example .env
```

---

## 🔑 Step 4: Generate Application Key

```bash
php artisan key:generate
```

Output: `Application key set successfully.`

---

## 🎨 Step 5: Configure Gemini API

Buka file `.env` dan update:

```env
GEMINI_API_KEY=your_gemini_api_key_here
```

**Cara dapetin API key:**
1. Buka: https://makersuite.google.com/app/apikey
2. Login dengan Google
3. Klik "Create API Key"
4. Copy & paste ke `.env`

---

## 💾 Step 6: Setup Database

### 6.1. Buat Database di MySQL

Buka MySQL:
```bash
mysql -u root -p
```

Buat database:
```sql
CREATE DATABASE palettopia;
EXIT;
```

### 6.2. Configure Database di .env

Buka file `.env` dan pastikan:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=palettopia
DB_USERNAME=root
DB_PASSWORD=         # Isi dengan password MySQL Anda
```

### 6.3. Test Koneksi Database

```bash
php artisan migrate:status
```

Kalau sukses, lanjut!

---

## 🗄️ Step 7: Run Migrations

```bash
php artisan migrate
```

Output: Banyak migration sukses ✅

---

## 🌱 Step 8: Seed Database

```bash
# Seed sample products
php artisan db:seed --class=PaletteProductSeeder

# Seed admin user
php artisan db:seed --class=AdminUserSeeder
```

**Admin credentials:**
- Email: `admin@palettopia.com`
- Password: `admin123`

---

## 🔗 Step 9: Create Storage Link

```bash
php artisan storage:link
```

Ini untuk link folder storage supaya image bisa diakses public.

---

## 🚀 Step 10: Start Server

```bash
php artisan serve
```

Output:
```
INFO  Server running on [http://127.0.0.1:8000].
Press Ctrl+C to stop the server
```

**Server sudah jalan!** ✅

---

## 🧪 Step 11: Test API

### Test 1: Health Check
```bash
curl http://127.0.0.1:8000/api/palettes
```

### Test 2: Login Admin
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@palettopia.com\",\"password\":\"admin123\"}"
```

Kalau dapat response JSON dengan token, **SUKSES!** 🎉

---

## 📝 Optional: Clear Cache

Kalau ada masalah, coba clear semua cache:

```bash
php artisan optimize:clear
```

Atau satu per satu:
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🌐 Step 12: Connect Frontend

Update base URL di frontend ke:
```
http://127.0.0.1:8000/api
```

---

## 🔍 Troubleshooting

### Error: "SQLSTATE[HY000] [1045] Access denied"
❌ **Problem:** Password MySQL salah

✅ **Fix:** 
- Cek password MySQL Anda
- Update di `.env` bagian `DB_PASSWORD=`

---

### Error: "Class 'ZipArchive' not found"
❌ **Problem:** PHP extension zip belum aktif

✅ **Fix (Windows XAMPP):**
1. Buka `php.ini`
2. Cari `;extension=zip`
3. Hapus `;` jadi `extension=zip`
4. Restart Apache

---

### Error: "No application encryption key"
❌ **Problem:** APP_KEY belum di-generate

✅ **Fix:**
```bash
php artisan key:generate
```

---

### Server tidak bisa diakses dari laptop lain
❌ **Problem:** Serve hanya di localhost

✅ **Fix:** Run dengan IP:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Akses dari laptop lain: `http://{IP_LAPTOP}:8000`

---

## 📚 Dokumentasi API

Setelah setup selesai, baca dokumentasi lengkap:
- **User API:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Admin API:** [ADMIN_API_DOCUMENTATION.md](ADMIN_API_DOCUMENTATION.md)

---

## ✅ Checklist Setup

- [ ] Clone repository
- [ ] Install composer dependencies
- [ ] Copy .env.example ke .env
- [ ] Generate APP_KEY
- [ ] Update Gemini API key di .env
- [ ] Buat database MySQL
- [ ] Update DB credentials di .env
- [ ] Run migrations
- [ ] Seed database
- [ ] Create storage link
- [ ] Start server
- [ ] Test API endpoints

---

## 🎯 Quick Start (All Commands)

Kalau sudah paham, ini command lengkapnya:

```bash
# 1. Clone & Setup
git clone https://github.com/savanapaaa/palettopia-be.git
cd palettopia-be
composer install

# 2. Environment
copy .env.example .env
php artisan key:generate

# 3. Database (update .env dulu!)
php artisan migrate
php artisan db:seed --class=PaletteProductSeeder
php artisan db:seed --class=AdminUserSeeder

# 4. Storage & Server
php artisan storage:link
php artisan serve
```

**Done!** Backend ready di `http://127.0.0.1:8000` 🚀

---

## 💡 Tips

1. **Gunakan .env yang benar** - Jangan commit file .env ke Git!
2. **Backup Gemini API Key** - Simpan di tempat aman
3. **Database berbeda per environment** - Dev vs Production
4. **Clear cache** kalau update .env
5. **Check logs** di `storage/logs/laravel.log` kalau ada error

---

## 📞 Need Help?

Kalau ada masalah:
1. Cek `storage/logs/laravel.log` untuk error details
2. Run `php artisan optimize:clear`
3. Pastikan semua requirements sudah terinstall
4. Cek dokumentasi Laravel: https://laravel.com/docs

---

**Happy Coding!** 🎨✨
