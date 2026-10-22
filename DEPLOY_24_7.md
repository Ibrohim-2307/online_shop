# 🌐 Online Shop Loyihasini 24/7 Doimiy Internet Serveriga Joylashtirish Qo'llanmasi

Ushbu loyiha kompyuteringiz o'chganida ham butun dunyodan 24 soat to'xtovsiz ishlashi uchun doimiy bulutli serverga (Cloud / VPS) o'rnatiladi.

Loyihaning barcha kodlari rasmiy GitHub repozitoriyingizga yuklangan:
👉 **https://github.com/Ibrohim-2307/online_shop**

---

## 🚀 1-Variant: Render.com orqali 100% BEPUL va 3 daqiqada o'rnatish (Eng osoni)

Render bulutli platformasi loyihangizni to'g'ridan-to'g'ri GitHub'dan oladi va avtomatik tarzda bepul doimiy HTTPS manzil (`https://online-shop-xxxx.onrender.com`) beradi.

### Qadamlar:
1. **[render.com](https://render.com)** saytiga kiring.
2. **"Sign Up with GitHub"** orqali GitHub profilingiz (`Ibrohim-2307`) bilan tizimga kiring.
3. Yuqori o'ng burchakdagi **"New +"** tugmasini bosing va **"Web Service"** ni tanlang.
4. Ro'yxatdan **`Ibrohim-2307/online_shop`** repozitoriyini topib, yonidagi **"Connect"** tugmasini bosing.
5. Sozlamalar:
   - **Name:** `online-shop` (yoki xohlagan nomingiz)
   - **Region:** `Frankfurt (EU Central)` *(O'zbekistonga eng yaqin va tezkor server)*
   - **Runtime:** `Docker` *(Repository ichidagi Dockerfile avtomat tanlanadi)*
   - **Instance Type:** `Free` ($0/oy)
6. **"Deploy Web Service"** tugmasini bosing!
7. **Natija:** 2-3 daqiqa ichida do'koningiz to'liq ishga tushadi va sizga doimiy havola beriladi!

---

## ⚡ 2-Variant: Railway.app orqali o'rnatish

1. **[railway.app](https://railway.app)** ga kiring va GitHub orqali kiring.
2. **"New Project"** -> **"Deploy from GitHub repo"** tugmasini bosing.
3. **`Ibrohim-2307/online_shop`** repozitoriyasini tanlang.
4. "Deploy Now" tugmasini bosing — tizim avtomatik ishga tushadi.

---

## 🖥 3-Variant: O'zingizning Shaxsiy VPS Serveringizga (Ubuntu / Debian) o'rnatish

Agar shaxsiy VPS (masalan DigitalOcean, Timeweb, Hetzner) va shaxsiy domeningiz (`.uz`) bo'lsa:

```bash
# 1. Serverga kirish va papkaga o'tish
cd /var/www

# 2. GitHub dan klon qilish
git clone https://github.com/Ibrohim-2307/online_shop.git
cd online_shop/backend

# 3. Paketlarni o'rnatish
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

# 4. .env fayliga MySQL ma'lumotlarini yozish
nano .env

# 5. Migratsiya va ma'lumotlarni to'ldirish
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link

# 6. Huquqlarni sozlash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Nginx sozlamasi:
```nginx
server {
    listen 80;
    server_name sizning-domeningiz.uz www.sizning-domeningiz.uz;
    root /var/www/online_shop/backend/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
}
```

SSL sertifikat ulash:
```bash
sudo certbot --nginx -d sizning-domeningiz.uz
```

---

## 💻 4-Variant: O'z kompyuteringizda tezkor Internetga chiqarish

Kompyuteringiz yoqilgan paytda internetga chiqarib turish uchun:
- Papkadagi **`start.bat`** faylini 2 marta bosing.
- U avtomatik tarzda MySQL, Laravel va Cloudflare tunnelini yoqib beradi!
