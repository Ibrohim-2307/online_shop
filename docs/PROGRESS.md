# Loyiha Rivojlanish Holati (PROGRESS.md)

Loyiha: **Marketplace — Ko'p sotuvchili Chakana va Ulgurji Savdo Platformasi**  
Sana: 19-oktabr, 2026  
Joriy holat: **01, 02, 03, 04 va 05-bosqichlar (Savatcha, ko'p sotuvchili buyurtma, atomar zaxira rezervatsiyasi, idempotency va outbox) to'liq bajarildi va alohida MySQL test bazasida 54 ta test bilan tasdiqlandi.** 06-bosqichga (To'lov, qaytarish va sotuvchi hisob-kitobi) tayyor. *(Jismoniy telefon sinovi `unauthorized` sababli alohida kutilmoqda)*.

---

## 1. Talablar Xaritasi va Amalga Oshirilgan Arxitektura

| Modul / Qatlam | Tanlangan Yechim | Holat |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP **8.4.26** (x64 ZTS, `infra/php84/`, SHA256 tekshirilgan, OSPanelga tegilmagan) | Tayyor |
| **Paket Menejeri** | Composer **2.10.3** (stable, `infra/bin/composer.phar`, SHA256 tekshirilgan) | Tayyor |
| **Backend Framework**| **Laravel 13.37.0** (rasmiy versiya, PHP 8.4 talablariga to'liq mos) | O'rnatilgan |
| **Ma'lumotlar Bazasi** | MySQL 8.0 (`online_shop` ishchi baza va `online_shop_test` alohida test baza, InnoDB, utf8mb4) | Ulangan, migratsiyalar bajarildi |
| **Sxema Eksporti** | `database/exports/schema.sql` (barcha 01, 02 va 03-bosqich jadvallari bilan) | Tayyor |
| **API Standarti** | `/api/v1`, `AssignRequestId` middleware, standart `ApiResponse` muvaffaqiyat va xatolik javoblari | Ishlamoqda |
| **Health Check** | `/api/v1/health` (JSON) va `/health` (Web Blade dashboard) | 200 OK (MySQL tekshiruvi bilan) |
| **Autentifikatsiya** | `laravel/sanctum: ^4.3`, Token revoke, Register, Login, Logout, /me | To'liq ishlamoqda |
| **Parolni Tiklash** | 15 daqiqali muddati cheklangan bir martalik token; qayta ishlatish (replay) rad etiladi | Ishlamoqda va testlangan |
| **Web Sessiya** | Web Login/Logout, CSRF himoyasi, login paytida `session()->regenerate()` (fixation himoyasi)| Tayyor va testlangan |
| **Admin MFA** | Administrator hisobiga kirishda 2-bosqichli OTP tasdiqlash talabi | Ishlamoqda va testlangan |
| **Rate Limiting** | API (60/m), Auth (10/m), OTP (5/m) limitlari | Ishlamoqda va testlangan |
| **OTP Xizmati** | `OtpServiceInterface`, `DemoOtpService` (5 min TTL, 5 xato urinish chegarasi, replay blok; Productionda demo taqiqlangan) | Tayyor va testlangan |
| **Rollar va Ruxsatlar** | 9 ta rol, 19 ta granulyar permission, registratsiyada vakolat o'g'irlash to'liq bloklangan | Seeder va middleware bilan faol |
| **Sotuvchilar (Sellers)** | `sellers`, `seller_members`, `seller_documents`, `audit_logs` | Bajarildi |
| **Multi-Tenant Izolyatsiya**| `SellerPolicy`, `ProductPolicy`, `WarehousePolicy` — Sotuvchilar bir-birining profili, xodimlari, mahsulotlari va omborlarini o'zgartira olmaydi | 403 Forbidden bilan himoyalangan |
| **Katalog va SKU** | `categories`, `products`, `product_variants` (SKU, barcode, atributlar JSON, moderatsiya holati) | Tayyor va testlangan |
| **Optom Narxlar (Tiers)** | `price_tiers` (min_qty, max_qty, unit_price), `PricingService` (All-units model, 299/300 va 999/1000 chegaralar) | Tayyor va testlangan |
| **Birliklar va Qadam** | `piece`, `box` (items_per_box), `kg`, `ton` (1000x kg), `unit_step` va `min_order_quantity` tekshiruvi | Qat'iy tekshiriladi |
| **Pul Arifmetikasi** | Float to'liq taqiqlangan; BCMath (`bcmul`, `bcadd`, `bcsub`, `bcdiv`) orqali xatosiz hisob | 0 yo'qotish bilan faol |
| **Ombor va Zaxiralar** | `warehouses`, `inventory_balances` (multi-warehouse, `lockForUpdate` atomar operatsiyalar, zaxira rezervatsiyasi) | Tayyor va testlangan |
| **Zaxira Harakat Auditi** | `inventory_movements` (inflow, outflow, adjustment, reservation, release; eski/yangi balans) | To'liq tarix yoziladi |
| **Bloklangan Tokenlar** | `EnsureUserIsActive` middleware — Bloklangan foydalanuvchining avval olingan tokeni ham rad etiladi | 403 Forbidden bilan himoyalangan |
| **Audit Tizimi** | `AuditService` — Muhim hisob, vakolat va do'kon o'zgarishlari loglanadi; parol/tokenlar avtomatik `[REDACTED]` qilinadi | Faol va testlangan |
| **Manzillar (Addresses)**| `addresses` jadvali, `AddressPolicy` orqali foydalanuvchi manzil daxlsizligi | 403 Forbidden bilan himoyalangan |
| **Dizayn Tizimi (preview.mp4)** | Laym-yashil (`#C9F46B`), To'q zumrad (`#123C2F`), Oq fon (`#FAFBF7`), Plus Jakarta Sans | Tailwind v4 @theme va Blade |
| **Xaridor Veb-Sayti** | Bosh sahifa, Katalog, Mahsulot (reaktiv narx kalkulyatori), Sotuvchi do'koni, Sevimlilar | To'liq backend ma'lumotlari bilan |
| **Xaridor Mobil Ilova** | `apps/customer_flutter` (Flutter 3.47.2, Riverpod, HealthScreen) | Emulyatorda sinovdan o'tdi (Jismoniy telefon kutilmoqda) |
| **Kuryer Mobil Ilova** | `apps/courier_flutter` (Flutter 3.47.2, Riverpod, CourierHealthScreen)| Emulyatorda sinovdan o'tdi (Jismoniy telefon kutilmoqda) |
| **CI / Sifat Nazorati** | `.github/workflows/ci.yml`, OpenAPI `docs/openapi.yaml`, PHPUnit testlar | **54 test o'tdi (343 ta assertion)** |

---

## 2. Bosqichlar va Holat Ro'yxati

- [x] **00 — Tayyorgarlik va Muhit Audit:** Repozitoriy ochildi, `TECHNICAL_SPEC.md` va `preview.mp4` biriktirildi, talablar xaritasi, muhit tekshiruvi, arxitektura qarorlari qabul qilindi.
- [x] **01 — Poydevor, ishlaydigan lokal muhit va Android emulyator sinovi:** PHP 8.4 va Composer 2.10.3 izolyatsiyalangan holda o'rnatildi; Laravel 13 backend, `customer_flutter` va `courier_flutter` yaratildi; MySQL ulandi; health check va Request ID middleware tayyorlandi; Android emulyatorida (`emulator-5554`) haqiqiy backend va MySQL health javobi tasdiqlandi. *(Jismoniy telefon sinovi `unauthorized` sababli alohida kutilmoqda)*.
- [x] **02 — Baza, foydalanuvchi, xavfsizlik va ruxsatlar:** Baza jadvallari, rollar, sotuvchi xodimlari, multi-tenant izolyatsiyasi, Sanctum auth, bir martalik parolni tiklash, web sessiya va CSRF, Admin MFA, OTP cheklovlari va demo himoyasi, bloklangan tokenlar nazorati, audit log tizimi to'liq yakunlandi (33 test, 201 assertion).
- [x] **03 — Katalog, ombor va optom narxlar:** Mahsulotlar, SKU variantlar, narx pog'onalari (all-units), PricingService (float ishlatilmaydi), ombor balansi, rezervatsiya va harakatlar auditi (`inventory_movements`), multi-tenant sotuvchi izolyatsiyasi to'liq yakunlandi va alohida MySQL test bazasida testlandi (42 test, 270 assertion). Tayyor `demo_seed.sql` eksport qilindi.
- [x] **04 — Dizayn tizimi va xaridor veb-sayti:** `preview.mp4` asosidagi zamonaviy dizayn tizimi, Tailwind v4 tokenlari, Plus Jakarta Sans, Blade layouts va komponentlari, Bosh sahifa, qidiruvli Katalog, reaktiv all-units narx kalkulyatorli Mahsulot sahifasi, Sotuvchi profili, Sevimlilar; responsivlik (1440px, 768px, 390px, 360px) skrinshotlar bilan tasdiqlandi (47 test, 296 assertion).
- [x] **05 — Savatcha va ishonchli checkout:** Multi-vendor cart, server quote, race condition zaxira rezervatsiyasi, idempotency key, outbox, 299 vs 300 narx yangilanishi va suborderlarga ajratish (54 test, 343 assertion).
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

## 4. 02-Bosqich Amaliy Natijalari (Baza, Foydalanuvchi, Xavfsizlik va Ruxsatlar)

### A. Ma'lumotlar Bazasi va Migratsiyalar:
- `users`: `phone` (unique), `is_active` (boolean), `last_login_at` maydonlari qo'shildi.
- `roles`: 9 ta standart rol yaratildi (`customer`, `wholesale_customer`, `seller_owner`, `seller_manager`, `seller_operator`, `courier`, `logistics_operator`, `moderator`, `admin`).
- `permissions`: 19 ta granulyar ruxsat yaratildi va tegishli rollarga biriktirildi.
- `role_permissions` va `user_roles`: Pivot jadvallar va constraintlar.
- `sellers`: Multi-vendor do'konlar (nomi, slug, legal_name, tax_id, status, commission_rate, rating).
- `seller_members`: Sotuvchi xodimlari va rollari (owner, manager, operator, accountant) va granulyar JSON ruxsatlar.
- `seller_documents`: Sotuvchi litsenziya va INN hujjatlari (pending/approved/rejected).
- `addresses`: Xaridor yetkazib berish manzillari (title, recipient, phone, full_address, lat, lng, is_default).
- `otp_codes`: Telefon raqamni tasdiqlash kodlari (phone, code, type, expires_at, used_at, attempts).
- `audit_logs`: Xavfsizlik va ma'lumotlar o'zgarishi auditi jadvali (action, entity, old_values, new_values, ip, user_agent).
- `database/exports/schema.sql`: phpMyAdmin orqali yangi bazaga yuklash uchun to'liq yangilangan SQL sxemasi.

### B. Tekshirilgan va Amalga Oshirilgan Xavfsizlik Talablari:
1. **Parolni tiklash:**
   - `POST /api/v1/auth/password/forgot` 15 daqiqali muddati cheklangan token yaratadi.
   - `POST /api/v1/auth/password/reset` yangi parolni o'rnatgach, tokenni bazadan darhol o'chiradi.
   - Xuddi shu token bilan ikkinchi marta murojaat qilinganda so'rov qat'iyan rad etiladi (`INVALID_TOKEN` — replay attackdan himoyalangan).
2. **Web sessiyasi:**
   - Web `GET /login`, `POST /login`, `POST /logout` yaratildi.
   - CSRF himoyasi to'liq faol.
   - Muvaffaqiyatli kirishda `session()->regenerate()` chaqirilib, sessiya identifikatori yangilanadi (session fixation himoyasi).
3. **Admin MFA oqimi:**
   - `admin` roli bor foydalanuvchi login qilganda to'g'ridan-to'g'ri admin token berilmaydi; `mfa_required: true` va vaqtinchalik `mfa_token` qaytariladi.
   - Tasdiqlanmagan token bilan admin APIlariga kirish taqiqlanadi (401).
   - `POST /api/v1/auth/mfa/verify` orqali OTP kodi tasdiqlangandan keyingina to'liq admin tokeni beriladi.
4. **Rate Limit va OTP xavfsizligi:**
   - Auth endpointlari uchun 10 so'rov/daqiqa, OTP uchun 5 so'rov/daqiqa chegarasi qo'yildi (6-so'rovda HTTP 429 Too Many Requests qaytadi).
   - OTP muddati o'tgan bo'lsa rad etiladi (`INVALID_OTP`).
   - 5 marta ketma-ket xato kod kiritilgach, hisob bloklanadi (brute-force himoyasi).
   - Bir marta ishlatilgan kod qayta ishlatilishi rad etiladi.
   - `DemoOtpService`: `APP_ENV=production` bo'lganda `RuntimeException` chiqarib, demo rejimining productionga chiqib ketishini 100% bloklaydi.
5. **Bloklangan foydalanuvchi tokeni:**
   - `EnsureUserIsActive` middleware orqali token avval olingan bo'lsa ham, agar foydalanuvchi keyinchalik admin tomonidan bloklansa (`is_active = false`), uning barcha API so'rovlari darhol 403 Forbidden (`ACCOUNT_DEACTIVATED`) bilan to'xtatiladi.
6. **Registratsiyada vakolat o'g'irlash (Privilege Escalation) himoyasi:**
   - `/api/v1/auth/register` ga yuborilgan har qanday `role=admin`, `roles=['admin']`, `is_admin=true` parametrlari server tomonidan to'liq chetlab o'tiladi; foydalanuvchi faqat ruxsat etilgan xaridor rolini oladi va admin yo'llariga kira olmaydi.
7. **Sotuvchi xodimining vakolatlari va to'xtatilgan do'kon cheklovi:**
   - `operator` boshqa xodim qo'sha olmaydi (403 Forbidden).
   - Hech kim xodimni `owner` roliga ko'tara olmaydi (422 xatosi).
   - Faoliyati to'xtatilgan (`status = 'suspended'`) do'kon uchun barcha boshqaruv amallari 403 Forbidden bilan cheklanadi.
8. **Audit tizimi va maxfiylik:**
   - Rollar o'zgarishi, foydalanuvchi va do'kon statuslari yangilanishi, parolni tiklash `audit_logs` jadvaliga yoziladi.
   - Parol, token va maxfiy ma'lumotlar avtomatik ravishda `[REDACTED]` holatiga keltirilib, loglarga ochiq holda tushishi bartaraf etildi.

> [!IMPORTANT]
> **Cheklovlar va kelgusi bosqichlar eslatmasi:**  
> Multi-tenant ma'lumotlar izolyatsiyasi hozirgacha mavjud bo'lgan barcha resurslar bo'yicha (**do'kon profili**, **do'kon xodimlari**, **foydalanuvchi manzillari**, **mahsulotlar va narxlar**, **omborlar va zaxiralar**) to'liq tekshirildi va tasdiqlandi. Hali yaratilmagan buyurtmalar (orders), to'lovlar (payments) va kuryerlik trackingi bo'yicha tenant izolyatsiyasi ushbu modullar yaratiladigan 05, 06, 07, 08-bosqichlarda amalga oshiriladi va testlanadi.
> 
> Haqiqiy SMS/Email provayderi (Eskiz, PlayMobile, Twilio yoki SMTP) hozircha ulanmagan; `DemoOtpService` lokal va test muhiti uchun xavfsiz ishlatilmoqda (`production` muhitida demo OTP qat'iyan taqiqlangan).

---

## 5. 03-Bosqich Amaliy Natijalari (Katalog, Ombor va Optom Narxlar)

### A. Ma'lumotlar Bazasi va Migratsiyalar:
- **`categories`**: Ko'p bosqichli kategoriya daraxti (`parent_id`, `slug`, `icon_url`, `sort_order`, `is_active`).
- **`products`**: Ko'p sotuvchili mahsulotlar (`seller_id`, `category_id`, `trade_mode` [retail_only, wholesale_only, both], `unit` [piece, box, kg, ton], `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`).
- **`product_variants`**: SKU variantlar (`product_id`, `sku`, `name`, `attributes` JSON, `barcode`, `is_active`).
- **`price_tiers`**: Hajmga qarab pog'onali optom narxlar (`product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`).
- **`warehouses`**: Sotuvchiga tegishli omborlar (`seller_id`, `name`, `code`, `address`, `is_active`).
- **`inventory_balances`**: Ombor zaxira qoldig'i (`warehouse_id`, `product_variant_id`, `physical_quantity`, `reserved_quantity`, unique `[warehouse_id, product_variant_id]`).
- **`inventory_movements`**: Zaxiradagi har bir o'zgarish tarixi (`warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`).
- **`database/exports/schema.sql`**: Barcha jadvallar eksport qilinib yangilandi.

### B. Tekshirilgan Amaliy Talablar:
1. **Alohida MySQL test bazasi (`online_shop_test`) va ishchi baza daxlsizligi:**
   - Testlar `phpunit.xml` orqali alohida `DB_DATABASE=online_shop_test` bazasida bajariladi.
   - Ishchi baza `online_shop`dagi ma'lumotlar (9 ta rol, mavjud foydalanuvchilar va sozlamalar) daxlsiz saqlandi va hech qanday o'chirish/truncation sodir bo'lmadi.
2. **299, 300, 999 va 1000 dona chegaralarida narx to'g'ri hisoblanishi (All-Units Pricing):**
   - 1 - 299 dona: 300,000 UZS / dona.
   - 300 - 999 dona: 270,000 UZS / dona.
   - 1000+ dona: 250,000 UZS / dona (`TECHNICAL_SPEC.md` dagi namuna bo'yicha).
   - **Chegara sinovi:**
     - 299 dona x 300,000 = 89,700,000 UZS.
     - 300 dona x 270,000 = 81,000,000 UZS (All-units modelida 300 dona xarid qilish 299 donadan arzonroq bo'lishi amalda tasdiqlandi).
     - 999 dona x 270,000 = 269,730,000 UZS.
     - 1000 dona x 250,000 = 250,000,000 UZS.
   - **Sotuvchi tomonidan sozlanadigan narxlar:** Narx pog'onalari tizimda qotirilgan konstanta emas. Sotuvchi o'z mahsulotlari uchun ixtiyoriy pog'ona va narxlarni API (`/api/v1/seller/variants/{id}/price-tiers`) orqali dinamik sozlay oladi va bu darhol savatcha hamda quote hisoblarida aks etadi (`test_seller_can_configure_custom_price_tiers_dynamically`).
3. **Birliklar va miqdor qadami tekshirilishi:**
   - `piece` birligida kasr miqdor (masalan, 2.5 dona) qat'iy rad etiladi.
   - `box` birligida `items_per_box` orqali donaga to'g'ri ko'paytiriladi.
   - `ton` birligida 1 tonna = 1000 kg ga to'liq mos konvertatsiya qilinadi.
   - `min_order_quantity` va `unit_step` qoidasiga to'g'ri kelmaydigan miqdorlar (masalan, min=50, step=10 bo'lganda 55 dona) server tomonidan xato bilan rad etiladi.
4. **Pul hisobida float ishlatilmasligi va katta sonlar hisob-kitobi:**
   - Narx va summalar hech qayerda PHP `float` tipiga o'girilmaydi; BCMath (`bcmul`, `bcadd`, `bcsub`, `bcdiv`) orqali 0 yo'qotish bilan saqlanadi.
   - **A. Chegirmasiz katta son hisobi:**
     - 100,000 dona x 300,000,000 UZS = **30,000,000,000,000 UZS** (30 trillion UZS).
     - Float eksponentiga (`3.0E+13`) aylanmasdan aniq string sifatida qaytishi alohida test bilan tasdiqlandi (`test_large_number_calculation_without_discount_strictly_avoids_float`).
   - **B. Chegirmali katta son hisobi (`TECHNICAL_SPEC.md` Misol B):**
     - Boshlang'ich (chakana) narx (1-9 dona): **300,000,000 UZS** / dona (`base_unit_price`).
     - Qo'llangan (ulgurji) narx (10+ dona): **270,000,000 UZS** / dona (`unit_price`).
     - Yakuniy summa: 100,000 dona x 270,000,000 UZS = **27,000,000,000,000 UZS** (27 trillion UZS, `total_price`).
     - Boshlang'ich narx, qo'llangan narx va yakuniy summa alohida ko'rsatilib testlandi (`test_large_number_calculation_with_tiered_discount_strictly_avoids_float`).
5. **Sotuvchi izolyatsiyasi (Multi-Tenant Isolation):**
   - Sotuvchi A Sotuvchi B ning mahsulotini, SKU variantlarini, narx pog'onalarini yoki omborlarini ko'ra olmaydi, yangilay olmaydi va o'chira olmaydi (403 Forbidden).
   - Sotuvchi A Sotuvchi B ning omboridagi zaxirasini o'zgartira olmaydi (403 Forbidden).
6. **Zaxira o'zgarishlari auditi (`inventory_movements`):**
   - Har bir zaxira kiritish (`inflow`), chiqarish (`outflow`) yoki tuzatish (`adjustment`) da `balance_before` va `balance_after` yoziladi.
   - Xaridor buyurtma qilganda zaxirani band qilish (`reservation`) va bekor bo'lganda bo'shatish (`release`) atomar ravishda `lockForUpdate` orqali amalga oshiriladi; mavjud qoldiqdan ortiq band qilish rad etiladi.
7. **Demo ma'lumotlari (`database/exports/demo_seed.sql`):**
   - `DemoCatalogSeeder` yaratildi va ishchi bazaga xavfsiz tatbiq etildi (`php artisan db:seed --class=DemoCatalogSeeder`).
   - `TECHNICAL_SPEC.md` dagi barcha namunalar (sement 1000+ dona @ 250,000 UZS, sanoat generatori 10+ dona @ 270,000,000 UZS, mineral suv box birligi, armatura tonna birligi) `database/exports/demo_seed.sql` (20.8 KB) fayliga eksport qilindi.

---

### C. PHPUnit Avtomatlashtirilgan Test Natijalari (42 ta test):
Barcha testlar `.\infra\artisan.bat test` orqali alohida `online_shop_test` MySQL bazasida 100% muvaffaqiyatli yakunlandi:

```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true                                                             0.01s  

   PASS  Tests\Feature\AuthAndPermissionTest
  ✓ customer can register and receives sanctum token                              4.55s  
  ✓ seller can register store and becomes seller owner                            0.26s  
  ✓ user can login with valid credentials                                         0.32s  
  ✓ inactive user cannot login                                                    0.23s  
  ✓ otp send and verify flow                                                      0.34s  
  ✓ authenticated user can view me and logout                                     0.30s  
  ✓ customer can create and read own address                                      0.29s  
  ✓ user cannot view or update another users address                              0.34s  
  ✓ seller a cannot view seller b profile or members                              0.33s  
  ✓ seller a cannot update seller b store profile                                 0.36s  
  ✓ seller a cannot remove seller b staff member                                  0.35s  
  ✓ seller can manage own store and add staff                                     0.40s  
  ✓ customer cannot access admin endpoints                                        0.31s  
  ✓ customer cannot access seller endpoints                                       0.30s  
  ✓ admin can access admin endpoints and moderate sellers                         0.37s  

   PASS  Tests\Feature\AuthSecurityHardeningTest
  ✓ password reset succeeds and token replay is rejected                          0.40s  
  ✓ expired password reset token is rejected                                      0.40s  
  ✓ web session login regenerates session and logout invalidates                  0.52s  
  ✓ admin login requires mfa and blocks unverified access                         0.43s  
  ✓ otp expiration wrong attempts limit and replay rejection                      0.45s  
  ✓ login and otp endpoints enforce rate limiting                                 0.53s  
  ✓ demo otp throws exception in production                                       0.39s  
  ✓ deactivated user previously issued token is immediately blocked               0.34s  
  ✓ registration privilege escalation is strictly neutralized                     0.34s  
  ✓ seller operator cannot add staff or promote to owner                          0.47s  
  ✓ suspended store management operations are blocked                             0.28s  
  ✓ audit logs record security events without passwords or tokens                 0.34s  

   PASS  Tests\Feature\CatalogAndPricingTest
  ✓ pricing tiers at 299 300 999 and 1000 thresholds                             0.49s  
  ✓ seller can configure custom price tiers dynamically                           0.47s  
  ✓ large number calculation without discount strictly avoids float               0.41s  
  ✓ large number calculation with tiered discount strictly avoids float           0.29s  
  ✓ units and unit step constraints validation                                    0.31s  
  ✓ seller cannot view update or delete another sellers product                   0.38s  
  ✓ seller cannot view update or adjust another sellers warehouse                 0.38s  
  ✓ inventory movements history recorded on every stock change                    0.32s  
  ✓ inventory reservation and release tracking                                    0.37s  

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response                                 0.08s  

   PASS  Tests\Feature\HealthCheckTest
  ✓ api v1 health endpoint returns success                                        0.09s  
  ✓ custom request id is preserved                                                0.07s  
  ✓ web health dashboard returns ok                                               0.10s  
  ✓ unknown api route returns standardized json error                             0.08s  

  Tests:    42 passed (270 assertions)
  Duration: 19.31s
```

---

## 6. 04-Bosqich Amaliy Natijalari (Dizayn Tizimi va Xaridor Veb-Sayti)

### A. Dizayn Tizimi va Tokenlar (`preview.mp4` Asosida):
- **Ranglar:** Laym-yashil (`#C9F46B`), Chuqur to'q zumrad (`#123C2F`), Yumshoq oq fon (`#FAFBF7`), Qoramtir matn (`#172326`), Shaftoli chegirma urg'usi (`#FFD9C8`).
- **Tipografiya:** Google Fonts `Plus Jakarta Sans` (400, 500, 600, 700, 800) — SIL Open Font License.
- **Burchak Radiusi:** Karta radiuslari 20px (`rounded-3xl`), tugmalar 12-14px (`rounded-xl` / `rounded-2xl`).
- **A11y:** Skip-to-content havola, oflayn rejim detektori, minimal 44x44px bosish zonalari (tap targets), WCAG 2.1 AA kontrast darajasi.
- **Kompilyatsiya:** Vite 7.0 + Tailwind CSS v4 + Alpine.js 3.14 (`public/build/assets/`).

### B. Foydalanuvchi Sahifalari va Haqiqiy Backend Ulanishi:
1. **Bosh Sahifa (`/`)**:
   - Yangi avlod marketplace qahramon banneri, ommabop toifalar ro'yxati, saralangan tovarlar (chakana va optom kartalar), tasdiqlangan sotuvchilar vitrinasi.
2. **Katalog va Qidiruv (`/catalog`)**:
   - Qidiruv (`?q=...`), toifalar filtri (`?category=...`), savdo rejimi (`?trade_mode=wholesale_only|retail_only|both`), saralash va sahifalash. Natija topilmaganda chiroyli bo'sh holat (empty state).
3. **Mahsulot Tafsiloti (`/products/{slug}`)**:
   - SKU, shtrixkod, brend, og'irlik, variantlar selektori.
   - **Interaktiv Optom Narx Kalkulyatori (Alpine.js)**: 299 dona va 300 dona chegarasidagi jami summa tejovi vizual ko'rsatiladi (300 dona xarid qilganda 299 donadan arzonroq tushish effekti va 1000 dona chegara).
   - Haqiqiy ko'p omborli qoldiqlar (`warehouses` va `inventory_balances`) jadvali.
4. **Sotuvchi Do'koni (`/sellers/{slug}`)**:
   - Do'kon nomi, INN/STIR, tasdiqlanganlik nishoni, reyting va sotuvchining barcha mahsulotlari.
5. **Sevimlilar (`/wishlist`)**:
   - Brauzer `localStorage` asosidagi reaktiv sevimlilar ro'yxati, hisoblagich va bo'sh holat ekrani.

### C. Responsiv Brauzer Sinovlari va Skrinshotlar:
Google Chrome orqali real backend (`http://127.0.0.1:8000`) sahifalari quyidagi o'lchamlarda skrinshot qilindi va saqlandi:
- `docs/screenshots/screenshot_desktop_home.png` (1440×900 px — Desktop Home)
- `docs/screenshots/screenshot_desktop_catalog.png` (1440×900 px — Desktop Catalog)
- `docs/screenshots/screenshot_desktop_product.png` (1440×900 px — Desktop Product Detail & All-Units Pricing)
- `docs/screenshots/screenshot_desktop_seller.png` (1440×900 px — Desktop Seller Storefront)
- `docs/screenshots/screenshot_tablet_catalog.png` (768×1024 px — Tablet Catalog)
- `docs/screenshots/screenshot_mobile_home.png` (390×844 px — Mobile Home & Sticky Bottom Bar)
- `docs/screenshots/screenshot_mobile_product.png` (390×844 px — Mobile Product Detail)
- `docs/screenshots/screenshot_mobile_360.png` (360×780 px — Small Mobile View)
Barcha ko'rinishlarda gorizontal siljish (horizontal scroll/overflow) 0 ekanligi tekshirildi va tasdiqlandi.

### D. Demo Seed Izolyatsiyasi (`database/exports/demo_seed.sql`):
- `infra/export_demo_seed.php` eksport skripti faqat sun'iy demo hisoblar (`@market.uz`, `+998901234567`) va namuna tovarlarni eksport qiladi.
- Real foydalanuvchilar, sessiyalar, shaxsiy access tokenlar, parollar va audit loglar eksport qilinmaydi.

### E. PHPUnit Test Natijalari (01–04 Bosqichlar):
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\AuthAndPermissionTest (15 tests)
   PASS  Tests\Feature\AuthSecurityHardeningTest (12 tests)
   PASS  Tests\Feature\CatalogAndPricingTest (9 tests)
   PASS  Tests\Feature\ExampleTest (1 test)
   PASS  Tests\Feature\HealthCheckTest (4 tests)

   PASS  Tests\Feature\StorefrontWebTest
  ✓ home page returns ok and displays real catalog
  ✓ catalog search and category filtering
  ✓ product detail page displays all units tiers and warehouse stock
  ✓ seller profile page displays store info and products
  ✓ wishlist page returns ok with empty state

  Tests:    47 passed (296 assertions)
  Duration: 17.52s
```

---

## 7. 05-Bosqich Amaliy Natijalari (Savatcha va Ishonchli Checkout)

### A. Ma'lumotlar Bazasi va Arxitektura:
- `carts` va `cart_items`: Foydalanuvchi yoki sessiya asosidagi savatcha (MOQ va qadam validationi).
- `orders`: Asosiy ota buyurtma (`order_number`, `user_id`, `status` [pending_payment], `currency`, `grand_total`, `shipping_address` JSON snapshot, `idempotency_key`).
- `suborders`: Sotuvchilar bo'yicha ajratilgan ichki buyurtmalar (`seller_id`, `suborder_number`, `items_subtotal`, `shipping_cost`, `total_amount`, `commission_amount`).
- `order_items`: Sotib olingan mahsulotlarning narx, SKU, atributlar va toifasi bilan muzlatilgan snapshot nusxasi.
- `shipments`: Har bir suborder uchun avtomatik yaratilgan logistika kuzatuv yozuvi (`tracking_number`, `status` [pending], `pickup_address`, `delivery_address`).
- `inventory_reservations`: Buyurtma vaqtidagi zaxirani band qilish (TTL = 15 daqiqa, status: reserved/committed/released/expired).
- `idempotency_keys`: Qayta takroriy so'rovlar nazorati (hash tekshiruvi, replay kesh, payload mismatch blokirovkasi).
- `outbox_events`: Tranzaksion outbox xabarlar jurnali (`OrderCreated`).

### B. Biznes Mantiqi va Xizmatlar:
- `CartService`: Ko'p sotuvchili savatcha hisobi, PricingService orqali live quote, alohida yetkazish hisobi.
- `InventoryReservationService`: Row lock (`lockForUpdate`) va BCMath orqali atomar zaxira band qilish; muddati o'tgan rezervatsiyalarni bir marta va xatosiz qaytarish (`releaseExpiredReservations`).
- `CheckoutService`: Klient narxiga ishonmasdan server narxini qayta hisoblash, 299 vs 300 dona narx yangilanishi, Idempotency-Key nazorati, tranzaksiyada xatolik bo'lsa to'liq rollback.
- `PaymentAdapterInterface`: 06-bosqich uchun unifikatsiyalangan to'lov adapteri interfeysi.

### C. Veb Do'kon Oqimi:
- `/cart`: Ko'p sotuvchili savatcha ko'rinishi, optom chegirmalar ko'rsatkichi, do'konlar bo'yicha alohida yetkazish narxi.
- `/checkout`: Qabul qiluvchi manzili, 15 daqiqali zaxira taymeri, buyurtma tarkibi va tasdiqlash tugmasi.
- `/orders/{orderNumber}/success`: Buyurtma qabul qilinganligi tasdig'i, suborderlar va TRK kuzatuv raqamlari.

### D. PHPUnit Test Natijalari (01–05 Bosqichlar):
```text
   PASS  Tests\Unit\ExampleTest
   PASS  Tests\Feature\AuthAndPermissionTest (15 tests)
   PASS  Tests\Feature\AuthSecurityHardeningTest (12 tests)
   PASS  Tests\Feature\CatalogAndPricingTest (9 tests)
   PASS  Tests\Feature\ExampleTest (1 test)
   PASS  Tests\Feature\HealthCheckTest (4 tests)
   PASS  Tests\Feature\StorefrontWebTest (5 tests)

   PASS  Tests\Feature\CartAndCheckoutTest
  ✓ two parallel checkouts cannot double sell last sku
  ✓ idempotency key replay returns same order
  ✓ idempotency key mismatch payload is rejected
  ✓ multi seller order splits into suborders with individual shipping
  ✓ tiered pricing recalculated at 299 vs 300 boundary
  ✓ expired reservation releases stock back to available balance
  ✓ web cart and checkout flow

  Tests:    54 passed (343 assertions)
  Duration: 17.20s
```

