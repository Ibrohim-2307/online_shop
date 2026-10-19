# Loyiha Rivojlanish Holati (PROGRESS.md)

Loyiha: **Marketplace — Ko'p sotuvchili Chakana va Ulgurji Savdo Platformasi**  
Sana: 19-oktabr, 2026  
Joriy holat: **01-bosqich to'liq yakunlandi.** 02-bosqichga (Baza, foydalanuvchi va ruxsatlar) tayyor.

---

## 1. Talablar Xaritasi va Amalga Oshirilgan Arxitektura

| Modul / Qatlam | Tanlangan Yechim | Holat |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP **8.4.26** (x64 ZTS, `infra/php84/`, SHA256 tekshirilgan, OSPanelga tegilmagan) | Tayyor |
| **Paket Menejeri** | Composer **2.10.3** (stable, `infra/bin/composer.phar`, SHA256 tekshirilgan) | Tayyor |
| **Backend Framework**| **Laravel 13.37.0** (rasmiy versiya, PHP 8.4 talablariga to'liq mos) | O'rnatilgan |
| **Ma'lumotlar Bazasi** | MySQL 8.0 (`online_shop` bazasi, InnoDB, utf8mb4) | Ulangan, migratsiyalar bajarildi |
| **API Standarti** | `/api/v1`, `AssignRequestId` middleware, standart `ApiResponse` muvaffaqiyat va xatolik javoblari | Ishlamoqda |
| **Health Check** | `/api/v1/health` (JSON) va `/health` (Web Blade dashboard) | 200 OK |
| **Autentifikatsiya Bazas**| `laravel/sanctum: ^4.3` o'rnatildi, `personal_access_tokens` jadvali yaratildi | Tayyor |
| **Xaridor Mobil Ilova** | `apps/customer_flutter` (Flutter 3.47.2, Dart 3.13.2, Riverpod, HealthScreen) | Testlardan o'tdi (0 xato) |
| **Kuryer Mobil Ilova** | `apps/courier_flutter` (Flutter 3.47.2, Dart 3.13.2, Riverpod, CourierHealthScreen)| Testlardan o'tdi (0 xato) |
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

## 3. Qabul Mezonlari Dalillari (01-Bosqich)

1. **Backend MySQL ulanishi va testlar:**
   ```text
   PASS Tests\Unit\ExampleTest
   PASS Tests\Feature\ExampleTest
   PASS Tests\Feature\HealthCheckTest
   Tests: 6 passed (43 assertions), Duration: 0.96s
   ```
2. **Flutter `customer_flutter` tahlili va testi:**
   ```text
   flutter analyze: No issues found!
   flutter test: All tests passed! (CustomerApp renders health screen without crash)
   ```
3. **Flutter `courier_flutter` tahlili va testi:**
   ```text
   flutter analyze: No issues found!
   flutter test: All tests passed! (CourierApp renders health screen without crash)
   ```
4. **API Base URL moslashuvchanligi:**
   Mobil ilovalar `API_BASE_URL` manzilini build-time environment (`String.fromEnvironment('API_BASE_URL')`) orqali dinamik oladi.
