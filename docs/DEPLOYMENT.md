# O'rnatish va Joylashtirish Qo'llanmasi (Deployment & Operations Guide)

Ushbu hujjat ko'p sotuvchili chakana va optom bozor tizimini (Marketplace) **Shared Hosting (cPanel, DirectAdmin, Plesk)** hamda **VPS / Production Cloud** muhitlariga o'rnatish, sozlash va boshqarish bo'yicha to'liq amaliy qo'llanmadir.

---

## 1. Hosting Talablari va Muhit Mosligi

Loyiha barqaror ishlashi uchun server quyidagi minimal talablarga javob berishi shart:

| Komponent | Minimal Talab | Tavsiya etilgan | Izoh |
| :--- | :--- | :--- | :--- |
| **PHP Versiyasi** | **8.4.0+** | PHP 8.4.26+ | Zamonaviy PHP 8.4 sintaksisi va xavfsizlik patchlari |
| **PHP Kengaytmalari** | `pdo_mysql`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl` | + `opcache`, `intl`, `fileinfo` | **BCMath** float xatoliksiz optom pul va qoldiq hisobi uchun shart |
| **Ma'lumotlar Bazasi**| MySQL 8.0+ yoki MariaDB 10.5+ | MySQL 8.0.30+ | `utf8mb4_unicode_ci`, Row lock (`lockForUpdate`), JSON ustunlar |
| **Web Server** | Apache 2.4+ / LiteSpeed | Apache mod_rewrite / Nginx | `.htaccess` orqali URL yo'naltirish |
| **Node.js Daemon** | **Talab qilinmaydi** | — | Tailwind CSS va barcha frontend aktivlar oldindan statik yig'ilgan |
| **HTTPS Protokoli** | SSL Sertifikat (Let's Encrypt) | SSL / TLS 1.3 | Webhooklar, OTP va cookie xavfsizligi uchun majburiy |

---

## 2. Shared Hosting uchun Fayllar Tuzilishi (Webroot Layout)

Shared hostingda xavfsizlikning eng muhim qoidasi — **`backend/public` papkasigina internetga ochiq bo'lishi kerak**. `.env`, PHP kodlari, vendor kutubxonalari va ma'lumotlar bazasi eksportlari internetdan to'g'ridan-to'g'ri chaqirib bo'lmaydigan tashqi hududda qolishi shart.

### A Variant (Tavsiya etilgan: DocumentRoot o'zgartiriladigan hostinglar)
Agar hosting panelida (masalan, cPanel Addon Domain yoki vhost sozlamasida) domenni papkaga yo'naltirish imkoni bo'lsa:
```text
/home/user/market/
├── .env                  <-- Internetdan yopiq
├── app/
├── bootstrap/
├── config/
├── database/
├── public/               <-- DOMAIN DOCUMENT ROOT SHU YERGA YO'NALTIRILADI
│   ├── .htaccess
│   ├── css/app.css
│   ├── index.php
│   └── favicon.ico
├── storage/
└── vendor/
```

### B Variant (DocumentRoot `public_html` bo'lgan qat'iy hostinglar)
Agar hosting DocumentRootni o'zgartirishga ruxsat bermasa, loyiha arxividagi ildiz `.htaccess` fayli avtomatik himoya va yo'naltirishni ta'minlaydi:
```text
public_html/
├── .htaccess             <-- Barcha so'rovlarni backend/public ga yo'naltiradi, .env va kodlarni 403 bilan to'sadi
├── .env.example
├── README_DEPLOY.md
├── backend/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   │   ├── .htaccess
│   │   ├── css/app.css
│   │   └── index.php
│   ├── storage/
│   └── vendor/
└── database/
    └── exports/
        ├── schema.sql
        └── demo_seed.sql
```

#### Ildiz `.htaccess` Himoya Qoidalari:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # 1. Yashirin nuqta fayllar va nozik kengaytmali fayllarni to'liq bloklash (403 Forbidden)
    RewriteRule ^\.(.*)$ - [F,L]
    RewriteRule ^(.*)\.(env|json|lock|md|xml|yml|yaml|sql|bat|ps1|sh)$ - [F,L]

    # 2. Dastur manba kodlari va konfiguratsiyalariga bevosita HTTP orqali kirishni to'sish
    RewriteRule ^(backend/(app|bootstrap|config|database|resources|routes|storage|tests|vendor)|infra|docs|apps)/ - [F,L]

    # 3. Foydalanuvchi so'rovlarini xavfsiz backend/public ga yo'naltirish
    RewriteCond %{REQUEST_URI} !^/backend/public/
    RewriteRule ^(.*)$ backend/public/$1 [L]
</IfModule>

Options -Indexes
```

---

## 3. O'rnatish Bosqichlari (Step-by-Step Installation)

### 1-qadam: Paketni yuklash
1. `dist/marketplace_shared_hosting.zip` arxivini hostingdagi do'kon papkasiga yuklang.
2. Fayllarni arxivdan chiqaring (Extract).

### 2-qadam: Ma'lumotlar bazasi va muhit sozlamalari
1. cPanel / MySQL Database Wizard orqali yangi baza va foydalanuvchi oching:
   - Baza nomi: `cpaneluser_market`
   - Foydalanuvchi: `cpaneluser_dbadmin`
   - Barcha huquqlarni (ALL PRIVILEGES) bering.
2. `.env.example` faylidan nusxa olib `.env` yarating:
   ```env
   APP_NAME="Marketplace"
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_URL=https://sizning-saytingiz.uz

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cpaneluser_market
   DB_USERNAME=cpaneluser_dbadmin
   DB_PASSWORD=SizningMurakkabParolingiz123!

   INSTALL_TOKEN=JudaMurakkabMaxfiyToken_9988776655
   ```

### 3-qadam: O'rnatish ustasini ishga tushirish

#### Usul A: Brauzer orqali (Web Wizard)
1. Brauzerda `https://sizning-saytingiz.uz/install` manzilini oching.
2. Tizim avtomatik ravishda PHP 8.4 va talab qilingan barcha PHP kengaytmalari hamda `storage` yozish ruxsatlarini tekshiradi.
3. `.env` faylidagi `INSTALL_TOKEN`ni, bosh administrator ismi, emaili va parolini kiriting.
4. "Tizimni O'rnatish va Qulflash" tugmasini bosing.
5. Jarayon yakunlangach `storage/installed.lock` fayli yaratiladi va o'rnatuvchi xavfsizlik nuqtai nazaridan qayta ochilmaydigan qilib qulflanadi (403 Forbidden).

#### Usul B: SSH Terminal orqali (CLI)
Hostingda SSH terminal mavjud bo'lsa:
```bash
cd backend
php artisan app:install --admin-email=admin@market.uz --admin-password=MurakkabParol123! --seed-demo
```

#### Usul C: phpMyAdmin orqali to'g'ridan-to'g'ri import
Agar web installer ishlatilmasa:
1. phpMyAdmin orqali ochilgan bo'sh bazani tanlang.
2. **Import** bo'limiga o'tib, avval `database/exports/schema.sql` faylini import qiling (barcha 46 ta jadval va migratsiya metadata yaratiladi).
3. Ixtiyoriy ravishda namuna tovarlar uchun `database/exports/demo_seed.sql` faylini import qiling.
4. SSH orqali `php artisan key:generate` va admin parolini sozlang.

---

## 4. Shared Hostingda Cron Vazifalari

Marketplace zaxiralarni muddati o'tganda bo'shatish (15 daqiqali TTL inventory reservation), buyurtmalar monitoringi va eski tracking koordinatalarini tozalash uchun har daqiqalik Laravel rejalashtiruvchisini ishlatadi.

Hosting panelidagi **Cron Jobs** bo'limiga quyidagi qatorni qo'shing:
```bash
* * * * * cd /home/user/market/backend && php artisan schedule:run >> /dev/null 2>&1
```

> [!NOTE]
> Shared hostingda WebSocket daemoni (Swoole yoki Node.js) bo'lmaganda, xaridor ilovasi va veb sahifalari avtomatik ravishda **Polling Fallback (10–15 soniyalik interval)** rejimiga o'tadi va real vaqt kuzatuvini davom ettiradi.

---

## 5. Zaxira Nusxa (Backup), Yangilash va Qaytarish (Rollback) Runbook

### Ma'lumotlar bazasini zaxiralash:
```bash
mysqldump -u cpaneluser_dbadmin -p cpaneluser_market --single-transaction --routines --triggers > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Versiyani yangilash jarayoni (Non-destructive Updates):
1. Saytni xizmat ko'rsatish rejimiga o'tkazish:
   ```bash
   php artisan down --secret="admin-bypass-key"
   ```
2. Yangi kodlarni yuklash va migratsiyalarni yurgizish:
   ```bash
   php artisan migrate --force
   ```
3. Keshni tozalash va optimallashtirish:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Saytni yana jonli holatga chiqarish:
   ```bash
   php artisan up
   ```

### Xatolik yuz berganda qaytarish (Rollback):
Agar oxirgi migratsiyada nomuvofiqlik yuz bersa:
```bash
php artisan migrate:rollback --step=1
```
Baza to'liq buzilgan holatda zaxiradan tiklash:
```bash
mysql -u cpaneluser_dbadmin -p cpaneluser_market < backup_YYYYMMDD_HHMMSS.sql
```

---

## 6. Flutter Mobil Ilovalarini Yig'ish va Imzolash (Mobile Release Guide)

Loyihada 2 ta Flutter ilovasi mavjud:
- `apps/customer_flutter` (Xaridor ilovasi)
- `apps/courier_flutter` (Kuryer ilovasi)

### A. Android Release Yig'ish va Imzolash

1. **Keystore yaratish:**
   ```bash
   keytool -genkey -v -keystore marketplace-upload-key.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
   ```
2. **`android/key.properties` faylini yaratish:**
   ```properties
   storePassword=sizning_parolingiz
   keyPassword=sizning_parolingiz
   keyAlias=upload
   storeFile=../marketplace-upload-key.jks
   ```
3. **API server manzilini ko'rsatib APK yig'ish:**
   ```bash
   cd apps/customer_flutter
   flutter build apk --release --dart-define=API_BASE_URL=https://sizning-saytingiz.uz/api/v1
   ```
4. **Google Play uchun App Bundle (AAB):**
   ```bash
   flutter build appbundle --release --dart-define=API_BASE_URL=https://sizning-saytingiz.uz/api/v1
   ```
   Yig'ilgan fayl: `build/app/outputs/bundle/release/app-release.aab`

### B. iOS Release Yig'ish va Imzolash
*Talab: macOS operatsion tizimi va Xcode 15+*
1. Apple Developer hisobida App ID va Provisioning Profile yarating.
2. `cd apps/customer_flutter/ios && pod install`
3. Xcode orqali loyihani oching, Signing & Capabilities bo'limida sertifikatni biriktiring.
4. `flutter build ipa --release --dart-define=API_BASE_URL=https://sizning-saytingiz.uz/api/v1`

---

## 7. Production Stack Kengaytmalari (VPS / Dedicated Serverlar uchun)

Agar tizim yuqori yuklamali (High-Load) rejimda ishlatilsa, shared hostingdan VPS ga o'tish tavsiya etiladi:
- **Kesh va Sessiyalar:** Redis 7.0+ (`CACHE_STORE=redis`, `SESSION_DRIVER=redis`)
- **Navbatlar (Queue Workers):** `php artisan queue:work redis --tries=3 --timeout=90` (Supervisor orqali boshqariladi)
- **Real-Time WebSocket Kuzatuv:** Laravel Reverb yoki Soketi orqali to'g'ridan-to'g'ri 1-soniyalik live GPS yangilanishi
- **Fayllar va Media Saqlash:** AWS S3 yoki MinIO ob'ektlar xotirasi (`FILESYSTEM_DISK=s3`)
