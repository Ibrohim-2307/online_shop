# Dizayn Tizimi va Aktivlar (Design Assets & Tokens)

Ushbu hujjat `preview.mp4` video ma'lumotnomasi va `docs/TECHNICAL_SPEC.md` talablari asosidagi dizayn tizimi, tokenlar, komponentlar va skrinshotlar katalogini belgilaydi.

---

## 1. Vizual Reference Tahlili (`preview.mp4`)

- **Manba Fayl:** `preview.mp4` (28.5 soniya, 1920×1080)
- **Umumiy Kayfiyat:** Premium, toza, zamonaviy, nafas oluvchi bo'shliqlar, yumaloq burchaklar, qulay tipografiya.
- **Asosiy Vizual Qoidalar:**
  - Oq fon va nozik pastel sirtlar (`#FAFBF7` va `#FFFFFF`).
  - Jonli laym-yashil urg'u (accent: `#C9F46B`) va chuqur to'q zumrad sarlavhalar (`#123C2F`).
  - Qattiq soyalar o'rniga nozik, yumshoq yorug'lik effektlari (`shadow-xs` / `shadow-sm`).
  - Keng va qulay oraliqlar (spacing: 4px, 8px, 12px, 16px, 24px, 32px, 48px).
  - 18–24 px kartochka radiuslari (`rounded-2xl` / `rounded-3xl`).
  - Video dorixona misolida bo'lsa-da, loyiha universal chakana + optom ko'p sotuvchili marketplace sifatida ishlab chiqildi.

---

## 2. Dizayn Rang Palitrasi (Design Tokens)

Barcha tokenlar `backend/resources/css/app.css` faylida Tailwind CSS v4 `@theme` spetsifikatsiyasi orqali e'lon qilingan:

| Token Nomi | Hex Kod | Foydalanish Maqsadi |
| :--- | :--- | :--- |
| **`--color-market-bg`** | `#FAFBF7` | Butun ilova va veb-sayt orqa foni (Soft off-white) |
| **`--color-market-surface`** | `#FFFFFF` | Mahsulot kartalari, panellar, modal oynalar |
| **`--color-market-text`** | `#172326` | To'q, yuqori kontrastli asosiy matnlar va narxlar |
| **`--color-market-muted`** | `#596668` | Qo'shimcha tavsiflar, sotuvchi nomlari, xira matnlar |
| **`--color-market-lime`** | `#C9F46B` | Asosiy harakat tugmalari (CTA), aksiya bannerlari, nishonlar |
| **`--color-market-emerald`** | `#123C2F` | Sarlavhalar, faol navigatsiya, tugma matnlari, nishonlar |
| **`--color-market-peach`** | `#FFD9C8` | Ulgurji chegirma nishonlari, muhim e'tiborlar |
| **`--color-market-border`** | `#E8ECE4` | Karta va bloklarning nozik ajratuvchi chiziqlari |
| **`--color-market-danger`** | `#E5484D` | Xatoliklar, bekor qilish, zaxira tugash signallari |
| **`--color-market-success`** | `#30A46C` | Tasdiqlangan buyurtma, to'langan to'lov, omborda bor |
| **`--color-market-warning`** | `#F1A10D` | Kam qolgan zaxira, pending to'lov, kutilayotgan moderatsiya |

---

## 3. Tipografiya va Radius

- **Asosiy Shrift:** `Plus Jakarta Sans`, sans-serif (Google Fonts, SIL Open Font License).
  - Zaxira shriftlar: `Inter`, `Roboto`, `system-ui`.
  - Shrift vaznlari: Regular (400), Medium (500), SemiBold (600), Bold (700), ExtraBold (800).
- **Burchak Radiusi (Border Radius):**
  - Tugmalar va inputlar: `12px` - `14px` (`rounded-xl` / `rounded-2xl`).
  - Mahsulot kartalari: `20px` (`rounded-3xl` / `--radius-card: 20px`).
  - Modal va katta bannerlar: `24px` - `32px`.
- **A11y & Sensor Qulayligi (Tap Targets):**
  - Barcha tugmalar va interaktiv havolalar minimal `44×44 px` o'lchamga ega.
  - Skip to content havolasi (`#main-content`) klaviatura foydalanuvchilari uchun mavjud.
  - Ofline rejim xabarnomasi brauzer tarmog'i uzilganda xabar beradi.

---

## 4. Foydalanuvchi Veb-sayti Sahifalari (Storefront Architecture)

Sayt Laravel Blade va Alpine.js reaktiv kutubxonasi yordamida yaratilgan bo'lib, to'liq backend ma'lumotlariga ulangan:

1. **Bosh Sahifa (`/`)**:
   - Yangi avlod marketplace qahramon banneri (Laym-yashil fon, CTA tugmalari).
   - Ommabop toifalar bloki (Qurilish materiallari, Sanoat uskunalari, Oziq-ovqat va ichimliklar).
   - Ommabop tovarlar galereyasi (Chakana va optom kartalar, dinamik narxlar).
   - Optom savdo afzalliklari (All-units narxlar, ombor nazorati, kuryer monitoringi).
   - Tasdiqlangan birinchi qo'l sotuvchilar karuseli.

2. **Katalog & Qidiruv (`/catalog`)**:
   - Qidiruv so'rovi (`q=...`), toifa filtri (`category=...`), savdo rejimi (`trade_mode=wholesale_only|retail_only|both`) va saralash (`sort=price_asc|price_desc|latest`).
   - Natijalar bo'yicha sahifalash (Pagination).
   - Natija topilmagandagi toza bo'sh holat (Empty state).

3. **Mahsulot Sahifasi (`/products/{slug}`)**:
   - SKU, shtrixkod, brend, og'irlik va toifa ma'lumotlari.
   - Variantlar selektori (rang, o'lcham, qadoq).
   - **Interaktiv Optom Narx Kalkulyatori (Alpine.js)**: 299 vs 300 chegara effekti, 1000+ chegara, bitta bosishda sinov tugmalari (299, 300, 999, 1000).
   - Haqiqiy omborlar balansi (`warehouses` va `inventory_balances` orqali manzil va qoldiq ko'rsatiladi).
   - O'xshash tovarlar tavsiyasi.

4. **Sotuvchi Do'koni (`/sellers/{slug}`)**:
   - Sotuvchi rasmiy nomi, INN/STIR, tasdiqlanganlik belgisi, o'rtacha reyting va ombor manzili.
   - Sotuvchining to'liq mahsulot katalogi.

5. **Sevimlilar (`/wishlist`)**:
   - Alpine.js `$store.wishlist` orqali brauzer `localStorage` xotirasida saqlanadi.
   - Bo'sh holat va saqlangan tovarlar hisoblagichi.

---

## 5. Responsiv Skrinshotlar Katalogi (`docs/screenshots/`)

Brauzerning turli ekran o'lchamlari bo'yicha real sinovdan o'tkazilgan va saqlangan skrinshotlar:

| Skrinshot Fayli | Ruxsat / O'lcham | Ko'rinish Tavsifi |
| :--- | :--- | :--- |
| `screenshot_desktop_home.png` | 1440×900 px | Desktop Bosh sahifa: qahramon banner, toifalar, optom bloklar |
| `screenshot_desktop_catalog.png` | 1440×900 px | Desktop Katalog: filtrlar paneli, mahsulotlar to'ri, saralash |
| `screenshot_desktop_product.png` | 1440×900 px | Desktop Mahsulot: optom narx jadvali, kalkulyator, omborlar |
| `screenshot_desktop_seller.png` | 1440×900 px | Desktop Sotuvchi: do'kon ma'lumotlari, INN, reyting, mahsulotlar |
| `screenshot_tablet_catalog.png` | 768×1024 px | Planshet Katalog: ixchamlashtirilgan filtrlar va 2 ustunli to'r |
| `screenshot_mobile_home.png` | 390×844 px | Mobil Bosh sahifa: ixcham header, qidiruv, pastki navigatsiya paneli |
| `screenshot_mobile_product.png`| 390×844 px | Mobil Mahsulot: gorizontal siljishsiz (zero horizontal overflow) |
| `screenshot_mobile_360.png` | 360×780 px | Kichik Mobil ekran: barcha bloklar ekran chegarasida moslashgan |

---

## 6. Litsenziyalar va Huquqiy Ma'lumotlar

- **Shriftlar:** `Plus Jakarta Sans` — SIL Open Font License (OFL) 1.1 ostida ochiq litsenziyalangan.
- **Ikonkalar:** Heroicons SVG to'plami — MIT License ostida.
- **Vektor Grafika va Logotip:** Loyiha uchun maxsus SVG vektor shakllari sifatida yaratilgan, tashqi pullik kutubxona yoki litsenziyaga bog'liq emas.
