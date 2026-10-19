# Dizayn Tizimi va Aktivlar (Design Assets & Tokens)

Ushbu hujjat `preview.mp4` video ma'lumotnomasi va `docs/TECHNICAL_SPEC.md` talablari asosidagi dizayn qoidalarini belgilaydi.

## 1. Vizual Reference Tahlili (`preview.mp4`)

- **Fayl:** `preview.mp4` (28.5 soniya, 1920×1080)
- **Umumiy kayfiyat:** Premium, toza, zamonaviy, nafas oluvchi bo'shliqlar, yumaloq burchaklar, qulay tipografiya.
- **Asosiy qoidalar:**
  - Oq fon va nozik pastel sirtlar.
  - Jonli laym-yashil urg'u (accent) va chuqur to'q yashil sarlavhalar.
  - Qattiq soyalar o'rniga nozik, yumshoq yorug'lik effektlari.
  - Keng oraliqlar (spacing: 4px, 8px, 16px, 24px, 32px).
  - 16–24 px kartochka radiuslari.
  - Video dorixona misolida bo'lsa-da, loyiha universal chakana + optom ko'p sotuvchili marketplace sifatida ishlab chiqiladi.

---

## 2. Dizayn Rang Palitrasi (Design Tokens)

| Token Nomi | Hex Kod | Foydalanish Maqsadi |
| :--- | :--- | :--- |
| **Background (Asosiy fon)** | `#FAFBF7` | Butun ilova va veb-sayt orqa foni |
| **Surface (Karta/Sirt foni)**| `#FFFFFF` | Mahsulot kartalari, panellar, modal oynalar |
| **Text Primary (Asosiy matn)**| `#172326` | To'q, yuqori kontrastli asosiy matnlar va narxlar |
| **Text Secondary (Ikkilamchi)**| `#596668` | Qo'shimcha tavsiflar, sotuvchi nomlari, sharh soni |
| **Brand Primary (Laym-yashil)**| `#C9F46B` | Asosiy harakat tugmalari (CTA), aksiya bannerlari |
| **Brand Dark (To'q yashil)** | `#123C2F` | Sarlavhalar, faol navigatsiya, muhim nishonlar |
| **Secondary Accent (Shaftoli)**| `#FFD9C8` | Ulgurji chegirma nishonlari, muhim e'tiborlar |
| **Danger / Error (Xatolik)** | `#E5484D` | Xatoliklar, bekor qilish, zaxira tugash signallari |
| **Success (Muvaffaqiyat)** | `#30A46C` | Tasdiqlangan buyurtma, to'langan to'lov, yetkazilgan |
| **Warning (Ogohlantirish)** | `#F1A10D` | Kam qolgan zaxira, pending to'lov, kutilayotgan moderatsiya |

---

## 3. Tipografiya va Radius

- **Shriftlar:** Inter / Plus Jakarta Sans / Roboto
- **Burchak radiusi:**
  - Tugmalar va inputlar: `12px` - `14px`
  - Mahsulot kartalari: `18px` - `20px`
  - Modal va katta bannerlar: `24px`
- **Minimal Bosish Maydoni (Tap Targets):** Kamida `48×48 px` mobil va veb-sensor ekranlar uchun.
