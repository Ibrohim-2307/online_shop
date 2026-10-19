# Marketplace — Ko'p Sotuvchili Chakana va Ulgurji Platforma

Amazon, Alibaba va Uzum Market xususiyatlaridan ilhomlangan, o'z brendi va dizayn tizimiga ega ko'p sotuvchili chakana va ulgurji savdo marketplace platformasi.

---

## 1. Loyiha Arxitekturasi

- **Backend:** Modulli Laravel 13 + MySQL 8.0 (PHP 8.4 runtime)
- **Web:** Blade + Livewire + Tailwind CSS (hosting talablariga mos)
- **Mobil Ilovalar:**
  - `apps/customer_flutter`: Xaridorlar uchun Flutter mobil ilovasi (Android/iOS)
  - `apps/courier_flutter`: Kuryerlar va logistika kuzatuv ilovasi (Android/iOS)
- **State Management:** Riverpod
- **API Standarti:** RESTful `/api/v1` + Sanctum token autentifikatsiyasi + `X-Request-Id` tracing

---

## 2. Ishga Tushirish Yo'riqnomasi

### Backend va API:
Loyiha ichiga izolyatsiyalangan PHP 8.4 va Composer 2.10 kiritilgan (OSPanel yoki boshqa loyihalarga ta'sir qilmaydi):

1. **Migratsiyalarni ishga tushirish:**
   ```powershell
   .\infra\artisan.bat migrate
   ```

2. **Lokal serverni ishga tushirish:**
   ```powershell
   .\infra\artisan.bat serve --port=8000
   ```
   - Web Salomatlik ekrani: [http://localhost:8000/health](http://localhost:8000/health)
   - API Salomatlik JSON: [http://localhost:8000/api/v1/health](http://localhost:8000/api/v1/health)

3. **Backend testlarini ishga tushirish:**
   ```powershell
   .\infra\artisan.bat test
   ```

---

### Flutter Mobil Ilovalari:

1. **Xaridor ilovasi (`customer_flutter`):**
   ```powershell
   cd apps/customer_flutter
   & "D:\flutter\bin\flutter.bat" pub get
   & "D:\flutter\bin\flutter.bat" test
   & "D:\flutter\bin\flutter.bat" run --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
   ```

2. **Kuryer ilovasi (`courier_flutter`):**
   ```powershell
   cd apps/courier_flutter
   & "D:\flutter\bin\flutter.bat" pub get
   & "D:\flutter\bin\flutter.bat" test
   & "D:\flutter\bin\flutter.bat" run --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
   ```

---

## 3. Hosting Talablari (Production & Shared Hosting)

> [!IMPORTANT]
> **PHP Versiyasi:** Ushbu loyiha Laravel 13 asosida qurilgan bo'lib, serverda **PHP kamida 8.3 yoki tavsiya etilgan PHP 8.4** bo'lishi shart.
> **MySQL:** MySQL 8.0 yoki MariaDB 10.5+ (InnoDB va `utf8mb4_unicode_ci`).
