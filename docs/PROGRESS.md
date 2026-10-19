# Loyiha Rivojlanish Holati (PROGRESS.md)

Loyiha: **Marketplace — Ko'p sotuvchili Chakana va Ulgurji Savdo Platformasi**  
Sana: 19-oktabr, 2026  
Joriy holat: **01-bosqich (Poydevor, muhit va Android emulyator sinovi) hamda 02-bosqich (Baza, foydalanuvchi va ruxsatlar) to'liq bajarildi.** 03-bosqichga (Katalog, ombor va optom narxlar) tayyor. *(Jismoniy telefon sinovi `unauthorized` sababli alohida kutilmoqda)*.

---

## 1. Talablar Xaritasi va Amalga Oshirilgan Arxitektura

| Modul / Qatlam | Tanlangan Yechim | Holat |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP **8.4.26** (x64 ZTS, `infra/php84/`, SHA256 tekshirilgan, OSPanelga tegilmagan) | Tayyor |
| **Paket Menejeri** | Composer **2.10.3** (stable, `infra/bin/composer.phar`, SHA256 tekshirilgan) | Tayyor |
| **Backend Framework**| **Laravel 13.37.0** (rasmiy versiya, PHP 8.4 talablariga to'liq mos) | O'rnatilgan |
| **Ma'lumotlar Bazasi** | MySQL 8.0 (`online_shop` bazasi, InnoDB, utf8mb4) | Ulangan, barcha migratsiyalar bajarildi |
| **Sxema Eksporti** | `database/exports/schema.sql` (phpMyAdmin uchun tayyor export) | Tayyor |
| **API Standarti** | `/api/v1`, `AssignRequestId` middleware, standart `ApiResponse` muvaffaqiyat va xatolik javoblari | Ishlamoqda |
| **Health Check** | `/api/v1/health` (JSON) va `/health` (Web Blade dashboard) | 200 OK (MySQL tekshiruvi bilan) |
| **Autentifikatsiya** | `laravel/sanctum: ^4.3`, Token revoke, Register (customer, wholesale, seller), Login, Logout, /me | To'liq ishlamoqda |
| **OTP Xizmati** | `OtpServiceInterface`, `DemoOtpService` (5 daqiqali TTL, brute-force urinishlar nazorati) | Tayyor va testlangan |
| **Rollar va Ruxsatlar** | 9 ta rol (`customer`, `wholesale_customer`, `seller_owner`, `seller_manager`, `seller_operator`, `courier`, `logistics_operator`, `moderator`, `admin`), 19 ta granulyar permission | Seeder va middleware bilan faol |
| **Do'konlar (Sellers)** | `sellers`, `seller_members`, `seller_documents` jadvallari, ko'p sotuvchili model | Bajarildi |
| **Multi-Tenant Izolyatsiya**| `SellerPolicy`, `EnsureSellerTenant` — Sotuvchilar bir-birining ma'lumotlarini ko'ra yoki o'zgartira olmaydi | 403 Forbidden bilan himoyalangan |
| **Manzillar (Addresses)**| `addresses` jadvali, `AddressPolicy` orqali foydalanuvchi manzil daxlsizligi | 403 Forbidden bilan himoyalangan |
| **Xaridor Mobil Ilova** | `apps/customer_flutter` (Flutter 3.47.2, Riverpod, HealthScreen) | Emulyatorda sinovdan o'tdi (Jismoniy telefon kutilmoqda) |
| **Kuryer Mobil Ilova** | `apps/courier_flutter` (Flutter 3.47.2, Riverpod, CourierHealthScreen)| Emulyatorda sinovdan o'tdi (Jismoniy telefon kutilmoqda) |
| **CI / Sifat Nazorati** | `.github/workflows/ci.yml`, OpenAPI `docs/openapi.yaml`, PHPUnit testlar | 21 test o'tdi (128 ta assertion) |

---

## 2. Bosqichlar va Holat Ro'yxati

- [x] **00 — Tayyorgarlik va Muhit Audit:** Repozitoriy ochildi, `TECHNICAL_SPEC.md` va `preview.mp4` biriktirildi, talablar xaritasi, muhit tekshiruvi, arxitektura qarorlari qabul qilindi.
- [x] **01 — Poydevor, ishlaydigan lokal muhit va Android emulyator sinovi:** PHP 8.4 va Composer 2.10.3 izolyatsiyalangan holda o'rnatildi; Laravel 13 backend, `customer_flutter` va `courier_flutter` yaratildi; MySQL ulandi; health check va Request ID middleware tayyorlandi; Android emulyatorida (`emulator-5554`) haqiqiy backend va MySQL health javobi tasdiqlandi. *(Jismoniy telefon sinovi `unauthorized` sababli alohida kutilmoqda)*.
- [x] **02 — Baza, foydalanuvchi va ruxsatlar:** Migrations, rollar (xaridor, ulgurji xaridor, sotuvchi, moderator, admin, kuryer, logist), multi-tenant seller izolyatsiyasi, Sanctum auth, OTP adapteri va permission testlari to'liq yakunlandi (21 test, 128 ta assertion).
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

## 3. 01-Bosqich Qurilma Sinovi Tafsilotlari

### A. Android Emulyatorida Haqiqiy Sinov (Emulator Verification):
1. **Xaridor Ilovasi (`customer_flutter`):**
   - Android emulyatorda (`emulator-5554`) muvaffaqiyatli o'rnatildi va ishga tushirildi.
   - Haqiqiy backend `/api/v1/health` ga ulanib, PHP 8.4.26, Laravel 13.37.0 va MySQL `connected` holatini ko'rsatdi.
   - **Skrinshot dalili:** `apps/customer_flutter/screenshot_device.png`
   - Olingan So'rov ID: `f76eefba-c6a3-4c8e-8489-5a70110b931f`
2. **Kuryer Ilovasi (`courier_flutter`):**
   - Android emulyatorda muvaffaqiyatli o'rnatildi va ishga tushirildi.
   - Haqiqiy backend `/api/v1/health` ga ulanib, logistika serveri va MySQL faol ekanligini ko'rsatdi.
   - **Skrinshot dalili:** `apps/courier_flutter/screenshot_device.png`
   - Olingan So'rov ID: `32c878ec-4ee9-44b5-95fa-fc1dc6cc8853`

### B. Jismoniy Telefon (`R3CN20LXXZA`) Sinovi Kutilmoqda (Unauthorized):
- Qurilma USB orqali ulangan, ammo `adb devices` da hozircha `unauthorized` holatida turgani uchun jismoniy telefonda sinov hali yakunlanmagan.
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

---

## 4. 02-Bosqich Amaliy Natijalari (Baza, Foydalanuvchi va Ruxsatlar)

### A. Ma'lumotlar Bazasi va Migratsiyalar:
- `users`: `phone` (unique), `is_active` (boolean), `last_login_at` maydonlari qo'shildi.
- `roles`: 9 ta standart rol yaratildi (`customer`, `wholesale_customer`, `seller_owner`, `seller_manager`, `seller_operator`, `courier`, `logistics_operator`, `moderator`, `admin`).
- `permissions`: 19 ta granulyar ruxsat yaratildi va tegishli rollarga biriktirildi.
- `role_permissions` va `user_roles`: Pivot jadvallar va constraintlar.
- `sellers`: Multi-vendor do'konlar (name, slug, legal_name, tax_id, status: pending/active/suspended/rejected, commission_rate, rating).
- `seller_members`: Sotuvchi xodimlari va rollari (owner, manager, operator, accountant) va granulyar JSON ruxsatlar.
- `seller_documents`: Sotuvchi litsenziya va INN hujjatlari (pending/approved/rejected).
- `addresses`: Xaridor yetkazib berish manzillari (title, recipient, phone, full_address, lat, lng, is_default).
- `otp_codes`: Telefon raqamni tasdiqlash kodlari (phone, code, type, expires_at, used_at, attempts).
- `database/exports/schema.sql`: phpMyAdmin orqali yuklash uchun butun sxema eksport qilindi.

### B. Xavfsizlik va Multi-Tenant Izolyatsiyasi:
1. **Sotuvchilar Ma'lumotlari Izolyatsiyasi (`SellerPolicy`, `EnsureSellerTenant`):**
   - Sotuvchi A sotuvchi B ning profili yoki xodimlari ro'yxatini ko'rishga uringanda server `403 Forbidden` (`FORBIDDEN`) qaytaradi.
   - Sotuvchi A sotuvchi B ning do'kon parametrlarini o'zgartirishga yoki xodimini o'chirishga uringanda server `403 Forbidden` bilan so'rovni qat'iy rad etadi.
2. **Manzillar Daxlsizligi (`AddressPolicy`):**
   - Foydalanuvchi A o'zidan boshqa istalgan foydalanuvchining manzilini ko'rish, yangilash yoki o'chirishga uringanda server `403 Forbidden` qaytaradi.
3. **Rol Ruxsatlari Chegarasi (`EnsureRole`):**
   - Oddiy xaridor yoki sotuvchi administrator yo'llariga (`/api/v1/admin/*`, masalan foydalanuvchilar ro'yxati yoki do'kon holatini tasdiqlash) kirishga uringanda `403 Forbidden` qaytariladi.
   - Bloklangan (`is_active = false`) foydalanuvchi login qila olmaydi (403 Forbidden).

### C. PHPUnit Avtomatlashtirilgan Test Natijalari:
Barcha testlar `.\infra\artisan.bat test` buyrug'i orqali to'liq ishga tushirildi:
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\AuthAndPermissionTest
  ✓ customer can register and receives sanctum token                              0.79s
  ✓ seller can register store and becomes seller owner                            0.10s
  ✓ user can login with valid credentials                                         0.11s
  ✓ inactive user cannot login                                                    0.09s
  ✓ otp send and verify flow                                                      0.12s
  ✓ authenticated user can view me and logout                                     0.09s
  ✓ customer can create and read own address                                      0.07s
  ✓ user cannot view or update another users address                              0.06s
  ✓ seller a cannot view seller b profile or members                              0.07s
  ✓ seller a cannot update seller b store profile                                 0.07s
  ✓ seller a cannot remove seller b staff member                                  0.11s
  ✓ seller can manage own store and add staff                                     0.11s
  ✓ customer cannot access admin endpoints                                        0.08s
  ✓ customer cannot access seller endpoints                                       0.06s
  ✓ admin can access admin endpoints and moderate sellers                         0.06s

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response                                 0.04s

   PASS  Tests\Feature\HealthCheckTest
  ✓ api v1 health endpoint returns success                                        0.03s
  ✓ custom request id is preserved                                                0.02s
  ✓ web health dashboard returns ok                                               0.04s
  ✓ unknown api route returns standardized json error                             0.03s

  Tests:    21 passed (128 assertions)
  Duration: 2.80s
```
