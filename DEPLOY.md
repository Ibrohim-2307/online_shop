# 🚀 Online Shop & Marketplace - Internetga Joylash va GitHub Qo'llanmasi

Ushbu hujjat loyihani butun dunyo bo'ylab Internetga chiqarish va GitHub-ga yuklash bo'yicha to'liq ko'rsatmalarni o'z ichiga oladi.

---

## 🌐 1. Tizimni Butun Internetga Joylash (Cloudflare Tunnel - Jonli Rejim)

Loyiha papkasida `cloudflared.exe` utilitasi sozlangan bo'lib, u orqali hech qanday to'lovsiz, domen sotib olmasdan va port ochmasdan turib loyihani to'g'ridan-to'g'ri xavfsiz HTTPS orqali butun dunyoga ulash mumkin.

### ⚡ 1-bosishda ishga tushirish:
Loyiha ildizidagi **`start.bat`** faylini sichqoncha bilan 2 marta bosing!
U avtomatik ravishda:
1. MySQL 8.0 ma'lumotlar bazasini tekshiradi;
2. Laravel backend va veb-serverni ishga tushiradi (Port 8000);
3. Cloudflare Internet Tunnelini ulaydi va sizga jonli HTTPS havolani taqdim etadi!

### Hozirda faol jonli Internet havolalari:
* 🛍️ **Katalog va Vitrina:** [https://brunswick-advanced-citations-boxed.trycloudflare.com/catalog](https://brunswick-advanced-citations-boxed.trycloudflare.com/catalog)
* 👑 **Super Admin Paneli:** [https://brunswick-advanced-citations-boxed.trycloudflare.com/admin](https://brunswick-advanced-citations-boxed.trycloudflare.com/admin)
* 🏪 **Sotuvchi Kabineti:** [https://brunswick-advanced-citations-boxed.trycloudflare.com/seller](https://brunswick-advanced-citations-boxed.trycloudflare.com/seller)

*(Ushbu havolani telefoningizda, do'stlaringizda yoki istalgan kompyuterda ochib ko'rishingiz mumkin!)*

---

## 🐙 2. Loyihani GitHub-ga Yuklash (Push to GitHub)

Loyiha uchun barcha git commitlar tayyorlangan va quyidagi repozitoriyga yo'naltirilgan:
* **Foydalanuvchi:** `Ibrohim-2307`
* **Repozitoriy manzili:** `https://github.com/Ibrohim-2307/online_shop.git`

### GitHub-ga joylash bo'yicha 2 ta oson usul:

#### 1-usul: PowerShell yoki Git Bash orqali 1 ta buyruq:
Loyiha papkasida terminal oching va quyidagilarni bajaring:
```bash
git branch -M main
git push -u origin main
```
> Ekranda Windows Git Credential Manager oynasi chiqadi, "Sign in with your browser" tugmasini bossangiz, 1 soniyada avtorizatsiya bo'lib barcha fayllar GitHub-ga tushadi.

#### 2-usul: Agar Personal Access Token (PAT) bilan yuklamoqchi bo'lsangiz:
1. GitHub hisobingizdan [GitHub Settings -> Developer Settings -> Personal access tokens](https://github.com/settings/tokens) ga kiring.
2. `Generate new token (classic)` ni bosing va `repo` katagiga galochka qo'ying.
3. Hosil bo'lgan tokenni nusxalab, terminalda quyidagini kiriting:
```bash
git remote set-url origin https://Ibrohim-2307:SIZNING_TOKENINGIZ@github.com/Ibrohim-2307/online_shop.git
git push -u origin main
```

---

## 🔑 Sinov Hisoblari (Demo Logins):

| Rol | Manzil | Login | Parol |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `/admin` | `admin@market.uz` | `Admin123!` |
| **Sotuvchi (Seller)** | `/seller` | `seller@market.uz` | `Seller123!` |
| **Xaridor (Customer)**| `/login` | `customer@market.uz` | `Customer123!` |
| **Kuryer (Logistika)**| `/login` | `courier@market.uz` | `Courier123!` |
