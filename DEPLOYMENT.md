# Deployment — Website RSUD Lubuk Basung

Panduan deploy ke server **Ubuntu 20.04/22.04/24.04** untuk production.

## Daftar Isi

- [1. Persiapan Server](#1-persiapan-server)
- [2. Clone & Setup Aplikasi](#2-clone--setup-aplikasi)
- [3. Konfigurasi Environment](#3-konfigurasi-environment)
- [4. Database](#4-database)
- [5. Izin & Storage](#5-izin--storage)
- [6. Optimasi Laravel](#6-optimasi-laravel)
- [7. Queue Worker](#7-queue-worker)
- [8. Scheduler (Cron)](#8-scheduler-cron)
- [9. Nginx](#9-nginx)
- [10. SSL (HTTPS)](#10-ssl-https)
- [11. Verifikasi](#11-verifikasi)
- [Cheat Sheet](#cheat-sheet)

---

## 1. Persiapan Server

### 1.1 Update & Install Dependencies

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server redis-server curl wget git unzip \
  software-properties-common
```

### 1.2 PHP 8.3 (via PPA ondrej)

```bash
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common \
  php8.3-mysql php8.3-redis php8.3-gd php8.3-curl php8.3-mbstring \
  php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl php8.3-opcache
```

> **Alternatif:** Kalau ga mau PPA, bisa pake PHP 8.1 dari repo default Ubuntu.

### 1.3 Composer

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
```

### 1.4 Node.js 20+

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 1.5 Cek Versi

```bash
php -v
node -v
npm -v
composer -V
nginx -v
mysql --version
redis-cli --version
```

---

## 2. Clone & Setup Aplikasi

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone <url-repository> rsud-lubas
sudo chown -R $USER:www-data rsud-lubas
cd rsud-lubas
```

### Install Dependensi

```bash
cp .env.example .env
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

---

## 3. Konfigurasi Environment

Edit `.env`:

```bash
nano .env
```

Wajib diubah untuk production:

| Variabel | Value | Keterangan |
|---|---|---|
| `APP_ENV` | `production` | Mode production |
| `APP_DEBUG` | `false` | Matikan debug |
| `APP_URL` | `https://domain-lo.com` | URL domain lo |
| `DB_DATABASE` | `rsud_lubas` | Nama database |
| `DB_USERNAME` | `rsud_user` | Jangan pakai root |
| `DB_PASSWORD` | `password-kuat` | Password database |
| `SESSION_DRIVER` | `redis` | Redis lebih cepat |
| `CACHE_STORE` | `redis` | Cache pake Redis |
| `QUEUE_CONNECTION` | `redis` | Queue pake Redis |
| `SESSION_SECURE_COOKIE` | `true` | Cookie cuma via HTTPS |

Generate key:

```bash
php artisan key:generate
```

---

## 4. Database

### 4.1 Buat Database & User

```bash
sudo mysql
```

```sql
CREATE DATABASE rsud_lubas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rsud_user'@'localhost' IDENTIFIED BY 'password-kuat';
GRANT ALL PRIVILEGES ON rsud_lubas.* TO 'rsud_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4.2 Migrasi & Seeder

```bash
php artisan migrate --force
php artisan db:seed --force
```

> **User default admin:** cek di `database/seeders/DatabaseSeeder.php` untuk email & password.

---

## 5. Izin & Storage

```bash
sudo chown -R www-data:www-data storage bootstrap/cache public
chmod -R 775 storage bootstrap/cache public
php artisan storage:link
```

---

## 6. Optimasi Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache
php artisan filament:upgrade
```

---

## 7. Queue Worker

Buat file `/etc/systemd/system/rsud-worker.service`:

```ini
[Unit]
Description=RSUD Lubas Queue Worker
After=network.target redis-server.service mysql.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/rsud-lubas
ExecStart=/usr/bin/php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5
StandardOutput=append:/var/log/rsud-worker.log
StandardError=append:/var/log/rsud-worker.log

[Install]
WantedBy=multi-user.target
```

Aktifkan:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now rsud-worker
sudo systemctl status rsud-worker
```

---

## 8. Scheduler (Cron)

```bash
sudo crontab -u www-data -e
```

Tambahkan:

```cron
* * * * * cd /var/www/rsud-lubas && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Nginx

Buat file `/etc/nginx/sites-available/rsud-lubas`:

```nginx
server {
    listen 80;
    server_name domain-lo.com www.domain-lo.com;
    root /var/www/rsud-lubas/public;

    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(?:css|js|webp|svg|eot|ttf|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

Aktifkan:

```bash
sudo ln -sf /etc/nginx/sites-available/rsud-lubas /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

---

## 10. SSL (HTTPS)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domain-lo.com -d www.domain-lo.com
sudo systemctl restart nginx
```

Setelah SSL aktif, pastikan `.env`:

```ini
APP_URL=https://domain-lo.com
SESSION_SECURE_COOKIE=true
```

Lalu re-cache:

```bash
php artisan config:cache
```

---

## 11. Verifikasi

```bash
# Cek service
sudo systemctl status nginx php8.3-fpm mysql redis-server rsud-worker

# Cek cron
sudo crontab -u www-data -l

# Cek HTTP response
curl -I https://domain-lo.com

# Cek storage link
ls -la /var/www/rsud-lubas/public/storage

# Cek sitemap
curl https://domain-lo.com/sitemap.xml
```

Buka `https://domain-lo.com/admin` dan login.

---

## Cheat Sheet

```bash
# Restart semua service
sudo systemctl restart nginx php8.3-fpm redis-server rsud-worker

# Lihat log error
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/rsud-worker.log

# Re-optimize setelah ubah config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Backup database
php artisan backup:run

# Generate sitemap manual
php artisan sitemap:generate
```

## Troubleshooting

| Masalah | Solusi |
|---|---|
| `apt update` gagal resolve | Set DNS di `/etc/netplan/*.yaml` ke `1.1.1.1` dan `8.8.8.8`, lalu `sudo netplan apply` |
| Package `php8.3` tidak ditemukan | Tambah PPA: `sudo add-apt-repository ppa:ondrej/php` |
| 502 Bad Gateway | Cek PHP-FPM: `sudo systemctl restart php8.3-fpm` |
| 403 Forbidden | Cek izin: `sudo chown -R www-data:www-data storage bootstrap/cache` |
| White screen / error | Cek `storage/logs/laravel.log` |
| Queue ga jalan | Cek `sudo systemctl status rsud-worker` |
| Gambar ga muncul | Cek `php artisan storage:link` udah dijalanin |
