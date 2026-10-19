# Loyiha Rivojlanish Holati (PROGRESS.md)

Loyiha: **Marketplace — Ko'p sotuvchili Chakana va Ulgurji Savdo Platformasi**  
Sana: 19-oktabr, 2026  
Joriy holat: **01-bosqich amalda yakuniy tekshirildi.** 02-bosqichga (Baza, foydalanuvchi va ruxsatlar) to'liq tayyor.

---

## 1. Talablar Xaritasi va Amalga Oshirilgan Arxitektura

| Modul / Qatlam | Tanlangan Yechim | Holat |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP **8.4.26** (x64 ZTS, `infra/php84/`, SHA256 tekshirilgan, OSPanelga tegilmagan) | Tayyor |
| **Paket Menejeri** | Composer **2.10.3** (stable, `infra/bin/composer.phar`, SHA256 tekshirilgan) | Tayyor |
| **Backend Framework**| **Laravel 13.37.0** (rasmiy versiya, PHP 8.4 talablariga to'liq mos) | O'rnatilgan |
| **Ma'lumotlar Bazasi** | MySQL 8.0 (`online_shop` bazasi, InnoDB, utf8mb4) | Ulangan, migratsiyalar bajarildi |
| **API Standarti** | `/api/v1`, `AssignRequestId` middleware, standart `ApiResponse` muvaffaqiyat va xatolik javoblari | Ishlamoqda |
| **Health Check** | `/api/v1/health` (JSON) va `/health` (Web Blade dashboard) | 200 OK (MySQL tekshiruvi bilan) |
| **Autentifikatsiya Bazas**| `laravel/sanctum: ^4.3` o'rnatildi, `personal_access_tokens` jadvali yaratildi | Tayyor |
| **Xaridor Mobil Ilova** | `apps/customer_flutter` (Flutter 3.47.2, Dart 3.13.2, Riverpod, HealthScreen) | APK yig'ildi (`assembleDebug`) |
| **Kuryer Mobil Ilova** | `apps/courier_flutter` (Flutter 3.47.2, Dart 3.13.2, Riverpod, CourierHealthScreen)| APK yig'ildi (`assembleDebug`) |
| **CI / Sifat Nazorati** | `.github/workflows/ci.yml`, OpenAPI `docs/openapi.yaml`, PHPUnit testlar | 6 test o'tdi (43 ta assertion) |

---

## 2. Bosqichlar va Holat Ro'yxati

- [x] **00 — Tayyorgarlik va Muhit Audit:** Repozitoriy ochildi, `TECHNICAL_SPEC.md` va `preview.mp4` biriktirildi, talablar xaritasi, muhit tekshiruvi, arxitektura qarorlari qabul qilindi.
- [x] **01 — Poydevor va ishlaydigan lokal muhit:** PHP 8.4 va Composer 2.10.3 izolyatsiyalangan holda o'rnatildi, Laravel 13 backend, `customer_flutter` va `courier_flutter` yaratildi, MySQL ulandi, health check va Request ID middleware tayyorlandi, barcha testlar muvaffaqiyatli o'tdi.
- [ ] **02 — Baza, foydalanuvchi va ruxsatlar:** Migrations, rollar (xaridor, ulgurji xaridor, sotuvchi, moderator, admin, kuryer, logist), multi-tenant seller izolyatsiyasi, Sanctum auth, OTP adapteri va permission testlari.
- [ ] **03 — Katalog, ombor va optom narxlar:** Mahsulotlar, SKU variantlar, narx pog'onalari (all-units), PricingService, ombor balansi va audit.
- [ ] **04 — Dizayn tizimi va xaridor veb-sayti:** Laym-yashil / to'q sarlavha / oq fon tokenlari, Blade + Livewire komponentlari, katalog, qidiruv, responsive moslashuv.
- [ ] **05 — Savatcha va ishonchli checkout:** Multi-vendor cart, server quote, race condition zaxira rezervatsiyasi, idempotency key, outbox.
- [ ] **06 — To'lov, qaytarish va sotuvchi hisob-kitobi:** Payment adapter, demo to'lov oqimi, webhook signature tekshiruvi, immutable ledger, refund & dispute.
- [ ] **07 — Seller, admin va logistika panellari:** Sotuvchi kabineti, admin boshqaruvi, logistika operatori, RFQ takliflari, ma'lumotlar izolyatsiyasi.
- [ ] **08 — Haqiqiy tracking backend:** Location batch API, koordinata va sakrash validationi, WebSocket va polling fallback, tracking timeline.
- [ ] **09 — Flutter xaridor ilovasi:** Native Flutter UI, Riverpod, typed API client, offline kesh, Android build sinovi.
- [ ] **10 — Flutter kuryer va real GPS tekshiruvi:** Alohida kuryer ilovasi, aktiv safar GPS tracking, fon rejimi, topshirish OTP/foto isboti.
- [ ] **11 — Hosting uchun o'rnatish paketi:** Shared hosting zip eksporti, schema.sql va demo_seed.sql, cron va scheduler integratsiyasi.
- [ ] **12 — Yakuniy tekshiruv va topshirish:** ACCEPTANCE.md, yuklama sinovlari, xavfsizlik va izolyatsiya auditi, topshirish paketi.

---

## 3. Amaliy Yakuniy Tekshiruv Natijalari (01-Bosqich Tekshiruvi)

### A. Backend va MySQL Jonli HTTP So'rovlari:
- Server PHP 8.4 orqali `0.0.0.0:8000` portida ishga tushirildi.
- **Haqiqiy HTTP so'rov:** `curl http://127.0.0.1:8000/api/v1/health`
  - Javob: `HTTP/1.1 200 OK`
  - MySQL holati: `{"driver":"mysql","status":"connected","latency_ms":21.89}`
  - Runtime: `{"php_version":"8.4.26","os":"WINNT"}`
  - Framework: `{"name":"Laravel","version":"13.37.0"}`
  - Request ID: `X-Request-Id: ec37f08c-fddd-4fcd-bf6b-f502374b84f5`
- **LAN orqali so'rov (Telefon uchun):** `curl http://192.168.1.102:8000/api/v1/health`
  - Javob: `HTTP/1.1 200 OK` (LAN orqali to'liq ishlaydi).
- **Web Dashboard:** `http://127.0.0.1:8000/health` (HTTP 200 OK, laym-yashil va to'q yashil dizayn tokenlari bilan).

### B. Android Litsenziyalari:
- `flutter doctor --android-licenses` avtomatlashtirilgan tarzda bajarildi:
  - Natija: `All SDK package licenses accepted`.
  - `flutter doctor` da Android toolchain to'liq yashil tasdiqlandi (`[√] Android toolchain`).

### C. Android APK Yig'ish (assembleDebug):
- `apps/customer_flutter`:
  - `flutter build apk --debug --dart-define=API_BASE_URL=http://192.168.1.102:8000/api/v1` muvaffaqiyatli yig'ildi (`128.9s`).
  - Fayl: `apps/customer_flutter/build/app/outputs/flutter-apk/app-debug.apk`
- `apps/courier_flutter`:
  - `flutter build apk --debug --dart-define=API_BASE_URL=http://192.168.1.102:8000/api/v1` muvaffaqiyatli yig'ildi (`71.7s`).
  - Fayl: `apps/courier_flutter/build/app/outputs/flutter-apk/app-debug.apk`
- Tarmoq xavfsizligi: Har ikki ilovaga `android.permission.INTERNET` va development uchun `android:usesCleartextTraffic="true"` qo'shildi.

### D. Qurilmada Ishga Tushirish Holati (Real Device Status):
- **Bajarilgan qism:** APKlar to'liq yig'ilgan, tarmoq manzili to'g'ri sozlangan, litsenziyalar qabul qilingan.
- **Hozircha tekshirilmagan / Kutilayotgan qism:** APKni telefonga to'g'ridan-to'g'ri o'rnatish.
- **Sababi:** Ulangan jismoniy Android telefon (`R3CN20LXXZA`) `adb` ro'yxatida `unauthorized` holatida turibdi. Android OS xavfsizlik siyosatiga ko'ra, foydalanuvchi telefon ekranidagi "Allow USB debugging?" oynasida ruxsat berish tugmasini bosmaguncha kompyuterdan telefonga ilova o'rnatib bo'lmaydi (`adb: device unauthorized`).
- **Yechim:** Foydalanuvchi telefonda ruxsatni tasdiqlashi bilanoq, tayyor APKlar quyidagi buyruqlar orqali telefonga o'rnatiladi:
  ```powershell
  & "D:\Android\Sdk\platform-tools\adb.exe" install apps/customer_flutter/build/app/outputs/flutter-apk/app-debug.apk
  & "D:\Android\Sdk\platform-tools\adb.exe" install apps/courier_flutter/build/app/outputs/flutter-apk/app-debug.apk
  ```
