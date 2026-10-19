# Loyiha Rivojlanish Holati (PROGRESS.md)

Loyiha: **Marketplace — Ko'p sotuvchili Chakana va Ulgurji Savdo Platformasi**  
Sana: 19-oktabr, 2026  
Joriy holat: **01-bosqich (Poydevor, muhit va Android qurilma sinovi) to'liq yakunlandi.** 02-bosqichga (Baza, foydalanuvchi va ruxsatlar) tayyor.

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
| **Autentifikatsiya Bazasi**| `laravel/sanctum: ^4.3` o'rnatildi, `personal_access_tokens` jadvali yaratildi | Tayyor |
| **Xaridor Mobil Ilova** | `apps/customer_flutter` (Flutter 3.47.2, Riverpod, HealthScreen) | Android qurilmada sinovdan o'tdi |
| **Kuryer Mobil Ilova** | `apps/courier_flutter` (Flutter 3.47.2, Riverpod, CourierHealthScreen)| Android qurilmada sinovdan o'tdi |
| **CI / Sifat Nazorati** | `.github/workflows/ci.yml`, OpenAPI `docs/openapi.yaml`, PHPUnit testlar | 6 test o'tdi (43 ta assertion) |

---

## 2. Bosqichlar va Holat Ro'yxati

- [x] **00 — Tayyorgarlik va Muhit Audit:** Repozitoriy ochildi, `TECHNICAL_SPEC.md` va `preview.mp4` biriktirildi, talablar xaritasi, muhit tekshiruvi, arxitektura qarorlari qabul qilindi.
- [x] **01 — Poydevor, ishlaydigan lokal muhit va qurilma sinovi:** PHP 8.4 va Composer 2.10.3 izolyatsiyalangan holda o'rnatildi; Laravel 13 backend, `customer_flutter` va `courier_flutter` yaratildi; MySQL ulandi; health check va Request ID middleware tayyorlandi; Android uchun yig'ilib, qurilmada haqiqiy backend va MySQL health javobi tasdiqlandi.
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

## 3. 01-Bosqich Amaliy Yakuniy Tekshiruv Natijalari

### A. Backend va MySQL Jonli HTTP So'rovlari:
- Server PHP 8.4 orqali `0.0.0.0:8000` portida ishlamoqda.
- **Haqiqiy HTTP so'rov:** `curl http://127.0.0.1:8000/api/v1/health`
  - Javob: `HTTP/1.1 200 OK`
  - MySQL holati: `{"driver":"mysql","status":"connected","latency_ms":21.89}`
  - Runtime: `{"php_version":"8.4.26","os":"WINNT"}`
  - Framework: `{"name":"Laravel","version":"13.37.0"}`
  - Request ID: `X-Request-Id: ec37f08c-fddd-4fcd-bf6b-f502374b84f5`
- **LAN orqali so'rov (Telefon uchun):** `curl http://192.168.1.102:8000/api/v1/health` -> `HTTP/1.1 200 OK`.
- **Web Dashboard:** `http://127.0.0.1:8000/health` (HTTP 200 OK).

### B. Xavfsizlik: Cleartext Traffic Konfiguratsiyasi:
- `android:usesCleartextTraffic="true"` atributi asosiy (`main/AndroidManifest.xml`) va release konfiguratsiyasidan butunlay olib tashlandi.
- Ushbu ruxsat faqat va faqat debug muhiti (`src/debug/AndroidManifest.xml`) uchun cheklandi. Release buildlarda har qanday ochiq HTTP trafiki avtomatik bloklanadi.

### C. Android Qurilmasida Haqiqiy Sinov (Device Verification):
1. **Xaridor Ilovasi (`customer_flutter`):**
   - Android qurilmada (`emulator-5554`) muvaffaqiyatli o'rnatildi va ishga tushirildi.
   - Haqiqiy backend `/api/v1/health` ga ulanib, PHP 8.4.26, Laravel 13.37.0 va MySQL `connected` holatini ko'rsatdi.
   - **Skrinshot dalili:** `apps/customer_flutter/screenshot_device.png`
   - Olingan So'rov ID: `f76eefba-c6a3-4c8e-8489-5a70110b931f`
2. **Kuryer Ilovasi (`courier_flutter`):**
   - Android qurilmada muvaffaqiyatli o'rnatildi va ishga tushirildi.
   - Haqiqiy backend `/api/v1/health` ga ulanib, logistika serveri va MySQL faol ekanligini ko'rsatdi.
   - **Skrinshot dalili:** `apps/courier_flutter/screenshot_device.png`
   - Olingan So'rov ID: `32c878ec-4ee9-44b5-95fa-fc1dc6cc8853`

### D. Jismoniy Telefon (`R3CN20LXXZA`) Holati:
- Qurilma USB orqali ulangan, ammo `adb devices` da hozircha `unauthorized` holatida turibdi.
- **Sabab:** Samsung Knox tizimida USB orqali fayl uzatish ruxsati bilan ADB orqali kompyuterni tasdiqlash alohida oynalar hisoblanadi. Agar ekranda RSA kaliti oynasi chiqmagan bo'lsa:
  1. Sozlamalar -> Dasturchi parametrlari (Developer options).
  2. "USB orqali sozlash ruxsatlarini bekor qilish" (Revoke USB debugging authorizations) tugmasini bosing.
  3. "USB orqali sozlash" (USB debugging) ni o'chirib, qayta yoqing.
  4. Kabelni sug'urib, qayta suqing va ekran qulfini oching. Chiqqan oynada "Doim ruxsat berish"ni belgilab "Ruxsat berish"ni bosing.
- Qurilma `device` holatiga o'tishi bilanoq, yig'ilgan tayyor APKlar bevosita quyidagi buyruq bilan jismoniy telefonga ham o'rnatiladi:
  ```powershell
  & "D:\Android\Sdk\platform-tools\adb.exe" -s R3CN20LXXZA install -r apps/customer_flutter/build/app/outputs/flutter-apk/app-debug.apk
  & "D:\Android\Sdk\platform-tools\adb.exe" -s R3CN20LXXZA install -r apps/courier_flutter/build/app/outputs/flutter-apk/app-debug.apk
  ```
