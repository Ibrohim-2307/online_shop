# Loyiha Rivojlanish Holati (PROGRESS.md)

Loyiha: **Marketplace — Ko'p sotuvchili Chakana va Ulgurji Savdo Platformasi**  
Sana: 19-oktabr, 2026  
Boshlang'ich holat: Tayyorgarlik (Phase 00) yakunlandi, 01-bosqichga to'liq tayyor.

---

## 1. Talablar Xaritasi (Requirements Map)

| Modul / Yo'nalish | Asosiy Talablar | Texnik Yechim / Manba |
| :--- | :--- | :--- |
| **Backend Arxitekturasi** | Modulli Laravel monolit + MySQL (InnoDB, utf8mb4), yagona biznes qoidalar manbasi | Laravel 10.x, Service Layer, Form Requests, Sanctum |
| **Katalog & Narxlash** | Chakana va ulgurji (all-units), minimal buyurtma (MOQ), qadam (step), dona/quti/kg/t | `PricingService`, `DECIMAL` / integer tiyin aniqligi, Float taqiqlangan |
| **Zaxira & Ombor** | Ko'p omborli, race conditionlardan himoya, vaqtinchalik rezervatsiya (TTL) | MySQL `SELECT ... FOR UPDATE`, Row-level lock, Atomic reserve |
| **Savatcha & Checkout** | Ko'p sotuvchili buyurtma: Parent Order -> Seller Suborders -> Shipments | Idempotency kalitlari, Outbox pattern, Server Quote |
| **Moliya & To'lov** | To'lov adapteri, imzolangan webhook, takrorlanishdan himoya, o'zgarmas ledger | `PaymentAdapterInterface`, `LedgerService`, Double-entry ledger |
| **Panellar** | Xaridor, Sotuvchi kabineti, Admin/Moderator, Logistika operatori | Blade + Livewire 3 + Tailwind CSS, Role-based policy |
| **Kuzatuv (Tracking)** | Haqiqiy GPS koordinatalari, batch yuklash, stale indikator, ETA oynasi | WebSocket (production) / Polling (demo fallback), GPS validator |
| **Xaridor Mobil Ilovasi**| Haqiqiy Flutter ekranlari, offline ko'rish, savatcha drafti, Riverpod | Clean Architecture, Dio, Flutter Secure Storage, Hive |
| **Kuryer Mobil Ilovasi** | Alohida Flutter ilova, fon/aktiv GPS, offline queue, topshirish OTP/foto | Location service, Background service, Bounded event queue |
| **Deploy & Hosting** | Shared hosting demo (CLI/cron minimalligi) va Enterprise Production profillari | `schema.sql`, `demo_seed.sql`, izolyatsiya qilingan public webroot |

---

## 2. Repozitoriy Tuzilishi (Repository Structure)

```text
d:\OSPanel\domains\online_shop\
├── .git/                      # Git boshqaruvi
├── .gitignore                 # Xavfsizlik va vaqtinchalik fayllar filtri
├── preview.mp4                # Dizayn vizual reference (dorixona uslubi emas, vizual til)
├── docs/                      # Loyiha hujjatlari va spetsifikatsiyalar
│   ├── TECHNICAL_SPEC.md      # To'liq texnik topshiriq (yagona talablar manbasi)
│   ├── PROGRESS.md            # Ishlar holati va bosqichlar jurnali
│   ├── COMPATIBILITY.md       # Muhit, versiyalar va paketlar mosligi
│   └── ASSETS.md              # Dizayn tokenlari, ranglar, komponent standartlari
├── backend/                   # Laravel modulli monolit (API, Web, Panellar)
├── apps/
│   ├── customer_flutter/      # Flutter xaridor mobil ilovasi (Android/iOS)
│   └── courier_flutter/       # Flutter kuryer mobil ilovasi (Android/iOS)
├── packages/                  # Umumiy Dart modullari / DTO lar
├── infra/                     # Docker, Nginx, Redis, WebSocket konfiguratsiyalari
├── deploy/shared-hosting/     # Shared hosting uchun o'rnatish paketi va skriptlar
├── database/exports/          # schema.sql va demo_seed.sql
└── tests/load/                # Yuklama va stress test senariylari
```

---

## 3. Mahalliy Muhit Tekshiruvi Natijalari

1. **PHP:** `PHP 8.1.9` (`D:\OSPanel\modules\php\PHP_8.1\php.exe`). `php.ini` sozlangan (curl, fileinfo, gd, mbstring, mysqli, openssl, pdo_mysql, bcmath, zip faollashtirilgan).
2. **Composer:** `D:\OSPanel\userdata\composer\composer.phar` (v2.4-dev).
3. **Ma'lumotlar bazasi:** `MySQL 8.0` (`D:\OSPanel\modules\database\MySQL-8.0-Win10`). Port: 3306. OSPanel tray orqali boshqariladi.
4. **Node.js & NPM:** `v24.19.0` va `11.17.0` tizimda mavjud.
5. **Flutter & Dart:** Flutter `3.47.2` (stable) va Dart `3.13.2` (`D:\flutter\bin`). Android SDK 36.0.0 o'rnatilgan.
6. **Sinov Qurilmasi:** Android jismoniy qurilma `R3CN20LXXZA` ulangan.
7. **Git:** Repozitoriy ochildi, `.gitignore` yaratildi.

---

## 4. Arxitektura Qarorlari (Key Architectural Decisions)

1. **Yagona Backend Haqiqat Manbasi:** Barcha hisob-kitoblar (optom narx pog'onalari, savatcha narxlari, buyurtma jami, zaxira ajratish, to'lovlar) faqat va faqat serverda bajariladi. Klient (veb yoki mobil) narx yoki zaxira holatini serverga diktovka qilolmaydi.
2. **Narx va Miqdor Aniqligi:**
   - Float taqiqlanadi.
   - Pul birliklari tiyinda butun son (integer minor units) yoki `DECIMAL(15,2)` ko'rinishida saqlanadi.
   - Og'irliklar (kg, tonna) uchun `DECIMAL(12,3)` bazaviy kilogrammga keltiriladi.
3. **Paralellizm va Zaxira Xavfsizligi:**
   - MySQL tranzaksiyalari + `SELECT ... FOR UPDATE` row lock.
   - TTL bilan cheklangan zaxira rezervatsiyasi (`inventory_reservations`).
4. **Ko'p Sotuvchili Buyurtma Ierarxiyasi:**
   - Bitta `orders` (parent order) ostida har bir sotuvchi uchun alohida `seller_orders` (suborder).
   - Har bir suborder o'z `shipments` jo'natmalariga bo'linadi.
5. **Moliya:** O'zgarmas ikki yoqlama buxgalteriya yozuvi (`ledger_entries`). Sotuvchi balansi bevosita ledger agregatsiyasidan hisoblanadi.
6. **Flutter Arxitekturasi:** Feature-based Clean Architecture (Presentation, Domain, Data) va Riverpod state management.

---

## 5. Bosqichlar va Holat Ro'yxati

- [x] **00 — Tayyorgarlik va Muhit Audit:** Repozitoriy ochildi, `TECHNICAL_SPEC.md` va `preview.mp4` biriktirildi, talablar xaritasi, muhit tekshiruvi, arxitektura qarorlari qabul qilindi.
- [ ] **01 — Poydevor va ishlaydigan lokal muhit:** Laravel 10 backend, `customer_flutter` va `courier_flutter` skeletonlari, health endpoint, API v1 standarti, CI/format asoslari.
- [ ] **02 — Baza, foydalanuvchi va ruxsatlar:** Migrations, rollar (xaridor, sotuvchi, moderator, admin, kuryer), multi-tenant seller izolyatsiyasi, Sanctum auth, OTP adapter.
- [ ] **03 — Katalog, ombor va optom narxlar:** Mahsulotlar, SKU variantlar, narx pog'onalari (all-units), PricingService, inventar harakatlari va testlar.
- [ ] **04 — Dizayn tizimi va xaridor veb-sayti:** Laym-yashil / to'q sarlavha / oq fon tokenlari, Blade + Livewire komponentlari, katalog, qidiruv, responsive testlar.
- [ ] **05 — Savatcha va ishonchli checkout:** Multi-vendor cart, server quote, race condition zaxira rezervatsiyasi, idempotency key, outbox.
- [ ] **06 — To'lov, qaytarish va sotuvchi hisob-kitobi:** Payment adapter, demo to'lov oqimi, webhook signature tekshiruvi, immutable ledger, refund & dispute.
- [ ] **07 — Seller, admin va logistika panellari:** Sotuvchi kabineti, admin boshqaruvi, logistika operatori, RFQ takliflari, izolyatsiya testlari.
- [ ] **08 — Haqiqiy tracking backend:** Location batch API, koordinata va sakrash validationi, WebSocket va polling fallback, tracking timeline.
- [ ] **09 — Flutter xaridor ilovasi:** Native Flutter UI, Riverpod, typed API client, offline kesh, Android build sinovi.
- [ ] **10 — Flutter kuryer va real GPS tekshiruvi:** Alohida kuryer ilovasi, aktiv safar GPS tracking, fon rejimi, topshirish OTP/foto isboti.
- [ ] **11 — Hosting uchun o'rnatish paketi:** Shared hosting zip eksporti, schema.sql va demo_seed.sql, cron va scheduler integratsiyasi.
- [ ] **12 — Yakuniy tekshiruv va topshirish:** ACCEPTANCE.md, yuklama sinovlari, xavfsizlik va izolyatsiya auditi, topshirish paketi.

---

## 6. Joriy Holat

**Bosqich 00 muvaffaqiyatli yakunlandi.**  
Keyingi qadam: **01-bosqich (Poydevor va ishlaydigan lokal muhit)**.
