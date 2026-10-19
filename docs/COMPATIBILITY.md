# Muhit va Bog'liqliklar Mosligi (Compatibility Matrix)

Ushbu hujjat loyiha uchun tanlangan dasturiy ta'minot, kutubxonalar va versiyalar mosligini rasmiy hujjatlar va SHA256 xeshlar asosida qayd etadi.

---

## 1. Dasturiy Muhit Holati (Environment Audit)

| Komponent | Lokal Manzil / Ijrochi | Versiya | Yaxlitlik (SHA256) / Manba | Holat |
| :--- | :--- | :--- | :--- | :--- |
| **PHP Runtime** | `infra/php84/php.exe` | **8.4.26 (cli x64 ZTS)** | `6e56f0e932e92bfdce208d3a7e6068f6e2f7a19fc2922a5f856fb085f61673f3`<br>*(windows.php.net rasmiy arxividan)* | Faol, OSPanel va boshqa loyihalarga daxlsiz izolyatsiyalangan |
| **Composer** | `infra/bin/composer.phar` | **2.10.3 (stable)** | `7a2d379d5b8ffdaa028580ef26494c36d2feef4b178d3dd1473a4dbc5e17c8d6`<br>*(getcomposer.org rasmiy nashridan)* | Faol, PHP 8.4 bilan ishlaydi |
| **Framework** | `backend/composer.json` | **Laravel 13.37.0** | Packagist `laravel/framework:^13.0` | O'rnatilgan, barcha testlar o'tdi |
| **Ma'lumotlar bazasi** | `127.0.0.1:3306` | **MySQL 8.0** | OSPanel `MySQL-8.0-Win10` | `online_shop` bazasi yaratildi, migratsiyalar bajarildi |
| **Flutter SDK** | `D:\flutter\bin\flutter.bat` | **3.47.2 (stable)** | Upstream flutter.git | `customer_flutter` va `courier_flutter` yaratildi |
| **Dart SDK** | `D:\flutter\bin\dart.bat` | **3.13.2** | Flutter toolchain | Tahlildan ogohlantirishsiz o'tdi |
| **Node.js & NPM** | Tizim PATH | **v24.19.0** / **11.17.0**| Rasmiy Node.js | Frontend aktivlarini yig'ishga tayyor |

---

## 2. Framework va PHP 8.4 Mosligi

- **Laravel 13 Talabi:** `php: ^8.3`, `ext-ctype`, `ext-filter`, `ext-hash`, `ext-mbstring`, `ext-openssl`, `ext-session`, `ext-tokenizer`.
- **Loyiha Muhiti:** PHP `8.4.26` ushbu talabni to'liq qanoatlantiradi (`^8.3`). Hech qanday platforma talablarini chetlab o'tish (`--ignore-platform-reqs`) ishlatilmagan.
- **Faol Kengaytmalar:** `bcmath`, `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `mysqli`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `sqlite3`, `sodium`, `zip`.
- **API Xavfsizligi:** `laravel/sanctum: ^4.3` o'rnatildi va `personal_access_tokens` jadvali yaratildi.

---

## 3. Hostingga O'rnatish Talablari (Hosting Requirements)

> [!IMPORTANT]
> **PHP Versiyasi Sharti:**
> Ushbu loyiha Laravel 13 asosida qurilganligi sababli, production yoki shared hosting serverida **PHP kamida 8.3 yoki tavsiya etilgan PHP 8.4** versiyasi yoqilgan bo'lishi shart. PHP 8.1 yoki 8.2 da Laravel 13 ishga tushmaydi.
> 
> **Server Kengaytmalari:**
> - `pdo_mysql`
> - `openssl`
> - `mbstring`
> - `tokenizer`
> - `xml`
> - `ctype`
> - `json`
> - `bcmath`
> - `curl`
> - `fileinfo`
> - `intl`
> 
> **MySQL:** MySQL 8.0+ yoki MariaDB 10.5+ (InnoDB va utf8mb4 qo'llab-quvvatlashi bilan).

---

## 4. Qulay Ishga Tushirish Skriptlari

Loyiha ildizida quyidagi izolyatsiyalangan skriptlar mavjud:
- **PHP:** `.\infra\php.bat -v`
- **Composer:** `.\infra\composer.bat <buyruq>`
- **Artisan:** `.\infra\artisan.bat <buyruq>` (avtomatik `backend/` katalogiga o'tib ishlaydi)
- **Flutter:** `& "D:\flutter\bin\flutter.bat"`
