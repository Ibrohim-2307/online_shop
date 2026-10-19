# Muhit va Bog'liqliklar Mosligi (Compatibility Matrix)

Ushbu hujjat loyiha uchun tanlangan dasturiy ta'minot, kutubxonalar va versiyalar mosligini rasmiy hujjatlar asosida qayd etadi.

## 1. Dasturiy Muhit Holati (Local Environment Audit)

| Komponent | Aniqlangan Manzil / Buyruq | Versiya | Holat / Izoh |
| :--- | :--- | :--- | :--- |
| **Operatsion Tizim** | Windows 11 Pro (Build 26200 x64) | Win 11 (25H2) | Mos |
| **Open Server Panel** | `D:\OSPanel\Open Server Panel.exe` | 5.4.3 / 651 | Ishga tushgan, trayda. Modullar: PHP 8.1 / MySQL 8.0 |
| **PHP CLI** | `D:\OSPanel\modules\php\PHP_8.1\php.exe` | **8.1.9** (x64 ZTS) | `php.ini` sozlangan (curl, mbstring, openssl, pdo_mysql, bcmath, fileinfo, gd, zip faol) |
| **Composer** | `D:\OSPanel\userdata\composer\composer.phar` | **2.4-dev** | Ishlaydi, PHP 8.1 orqali chaqiriladi |
| **MySQL Server** | `D:\OSPanel\modules\database\MySQL-8.0-Win10` | **MySQL 8.0** | InnoDB, utf8mb4. Port: 3306 (OSPanel orqali start qilinadi) |
| **Node.js** | System PATH | **v24.19.0** | Mos |
| **NPM** | System PATH | **11.17.0** | Mos |
| **Flutter SDK** | `D:\flutter\bin\flutter.bat` | **3.47.2** (stable) | Mos |
| **Dart SDK** | `D:\flutter\bin\dart.bat` | **3.13.2** | Mos |
| **Android SDK** | System / Android Studio | **API 36.0.0** | Flutter toolchain orqali aniqlangan |
| **Sinov Qurilmasi** | USB Debugging | `R3CN20LXXZA` | Jismoniy Android qurilma mavjud |
| **Git** | System PATH | **2.55.0.windows.5**| Repozitoriy initsializatsiya qilingan |

---

## 2. Framework va Dependency Qarorlari

### Backend (Laravel)
- **Tanlangan Framework:** Laravel 10.x LTS
  - **Moslik:** PHP 8.1.9 Laravel 10 talablariga to'liq mos keladi (PHP >= 8.1.0).
  - **Ma'lumotlar bazasi:** MySQL 8.0 (InnoDB, JSON qo'llab-quvvatlash, Window functions, CTE).
  - **Web Stack:** Blade + Livewire 3 + Tailwind CSS (hosting talablariga qulay, doimiy Node server talab qilmaydi).
  - **API Autentifikatsiya:** Laravel Sanctum (Web sessiya va mobil SPA/API tokenlarini xavfsiz boshqarish).

### Mobil Ilovalar (Flutter)
- **Tanlangan Versiya:** Flutter 3.47.2 / Dart 3.13.2
- **Arxitektura:** Clean Architecture / Feature-based (Presentation -> Domain -> Data).
- **State Management:** **Riverpod** (`flutter_riverpod: ^2.6.1`)
  - **Tanlov sababi:** BLoC bilan solishtirganda kamroq boilerplate, compile-time xavfsizlik, global context talab qilmaydigan dependency injection va asinxron holatlarni (`AsyncValue`) ajoyib boshqarish.
- **Tarmoq va API:** `dio` (Interceptorlar, token refresh, request ID, timeout va offline queue uchun)
- **Xavfsiz Xotira:** `flutter_secure_storage` (auth tokenlar uchun)
- **Lokal Kesh:** `hive` / `shared_preferences` (offline katalog va savatcha uchun)

---

## 3. Lokal Ishga Tushirish va PATH Qulayligi

OSPanel PHP va Flutter tizim global PATH o'zgaruvchisida bo'lmagani sababli, loyihada ishlab chiqish va sinovlar uchun quyidagi buyruq ko'rinishlari ishlatiladi:
- **PHP:** `& "D:\OSPanel\modules\php\PHP_8.1\php.exe"`
- **Composer:** `& "D:\OSPanel\modules\php\PHP_8.1\php.exe" "D:\OSPanel\userdata\composer\composer.phar"`
- **Artisan:** `& "D:\OSPanel\modules\php\PHP_8.1\php.exe" artisan`
- **Flutter:** `& "D:\flutter\bin\flutter.bat"`
- **Dart:** `& "D:\flutter\bin\dart.bat"`
