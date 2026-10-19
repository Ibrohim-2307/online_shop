# Marketplace — Antigravity uchun texnik topshiriq

Sana: 19-oktabr, 2026. Holat: amalga oshirish uchun loyiha talablari; tayyor yoki sinovdan o'tgan dastur emas.

## 1. Maqsad va boshlang'ich qarorlar

Amazon, Alibaba va Uzum Market funksiyalaridan ilhomlangan, o'z brendi va dizayniga ega ko'p sotuvchili marketplace yarating. Chakana va ulgurji savdo bir katalogda ishlasin. Xaridor veb-sayt yoki haqiqiy Flutter mobil ilovasida xarid qiladi; sotuvchi o'z mahsulotlari, buyurtmalari va mablag'larini boshqaradi; kuryer alohida Flutter ilovasida yetkazib berishni bajaradi; administrator butun tizimni boshqaradi.

Boshlang'ich farazlar: asosiy bozor O'zbekiston, asosiy til o'zbekcha, pul birligi UZS, interfeys keyinchalik ruscha va inglizchaga tarjima qilinadi. Ishchi nom Market; haqiqiy nom, logotip, to'lov provayderi, xarita provayderi, yetkazib berish hamkorlari va hosting imkoniyatlari sozlanadigan bo'lsin. Bu tanlovlar foydalanuvchi tasdiqlagan biznes faktlari emas.

Natijalar:
- Mobilga mos xaridor veb-sayti.
- PHP + MySQL backend va hujjatlashtirilgan API.
- Sotuvchi kabineti, administrator va logistika paneli.
- Flutter Android/iOS xaridor ilovasi va Flutter kuryer ilovasi. WebView ichiga solingan sayt qabul qilinmaydi.
- Hostingga o'rnatish arxivi, MySQL sxemasi va alohida demo ma'lumotlari.
- O'rnatish, zaxiralash, tiklash, yangilash va mobil build yo'riqnomalari.

## 2. Dizayn va reference video

Berilgan preview.mp4 — 28,5 soniyali, 1920×1080 o'lchamdagi dizayn reference. Tanlab ko'rilgan kadrlarda oq fon, laym-yashil katta banner, juda to'q sarlavhalar, pastel kartochkalar, yumaloq burchaklar, keng bo'sh joylar va sodda navigatsiya bor. Undagi dorixona mazmuni loyiha uchun funksional talab hisoblanmaydi. Brend, fotosurat va matnlarni aynan ko'chirmang.

Taklif qilinadigan tokenlar: fon #FAFBF7, sirt #FFFFFF, asosiy matn #172326, asosiy yashil #C9F46B, to'q yashil #123C2F, qo'shimcha shaftoli #FFD9C8. Rang juftliklarini kontrastga tekshiring. Kartochka radiusi 16–24 px; izchil 4/8 px oraliq tizimi; o'qilishi yaxshi shrift va katta, aniq narxlar. Qimmat mahsulotlarda uzun narxlar joylashuvni buzmasin.

Veb: qidiruv markaziy o'rinda, kategoriyalar, hudud tanlash, savatcha va profil; katta lekin ixcham aksiya banneri; chakana/ulgurji tanlovi; kategoriya bloklari; tavsiya mahsulotlar; sotuvchilar; xizmatlar va footer. Banner butun ekranni egallamasin.

Mobil: Bosh sahifa, Katalog, Savatcha, Buyurtmalar, Profil pastki navigatsiyasi. Xavfsiz ekran chetlari, klaviatura, back tugmasi, deep link va kichik ekranlar hisobga olinsin. Desktop sahifani kichraytirish yetarli emas.

Mahsulot kartasi: rasm, nom, sotuvchi, reyting va sharhlar soni, birlik, boshlang'ich narx, ulgurji narx chegarasi, mavjudlik. Mahsulot sahifasida variantlar, narx pog'onalari, minimal buyurtma, miqdor qadami, yetkazish taxmini, tavsif, media, hujjatlar, sharhlar va qaytarish shartlari bo'lsin.

Animatsiyalar: tugma javobi 120–180 ms, sahifa o'tishi 180–250 ms, yengil savatchaga qo'shish animatsiyasi, skeleton yuklanish. Bu sonlar dizayn maqsadi. Reduced motion rejimini hurmat qiling; ortiqcha parallax, uzluksiz animatsiya va videoni avtomatik yuklashdan saqlaning. Har ekranga loading, empty, error, offline, forbidden va success holatlari tayyorlansin. Klaviatura va screen reader bilan ishlash, matnni kattalashtirish, kamida 44–48 px bosish maydonlarini tekshiring.

## 3. Rollar va chegaralar

- Mehmon: katalog, qidiruv, mahsulotlar; checkout uchun ro'yxatdan o'tish.
- Xaridor: profil, manzillar, sevimlilar, savatcha, checkout, to'lov, buyurtmalar, kuzatuv, qaytarish, sharh, yordam.
- Ulgurji xaridor: yuqoridagilar va kompaniya rekvizitlari, katta miqdor, narx taklifi so'rovi (RFQ), taklifni qabul qilish.
- Sotuvchi va uning xodimi: faqat o'z do'koni mahsulotlari, narxlari, omborlari, buyurtmalari, qaytarishlari, hisobotlari. Har xodimning alohida vakolati bo'lsin.
- Kuryer: faqat o'ziga biriktirilgan aktiv jo'natmalar, yo'nalish, zarur aloqa ma'lumotlari, topshirish tasdig'i.
- Logistika operatori: jo'natmalarni biriktirish, kechikish sababi, ombor hodisalari.
- Moderator/yordam xodimi: cheklangan vakolat; pul chiqarish yoki tizim sozlamalariga avtomatik ruxsat yo'q.
- Administrator: konfiguratsiya, moderatsiya, komissiyalar, nizolar, hisob-kitoblar va audit.

Har bir API serverda rol va resurs egasini tekshiradi. Tugmani yashirish xavfsizlik tekshiruvi emas.

## 4. Chakana va ulgurji narxlash

Mahsulot SKU/variant va sotuvchi bo'yicha narxlanadi. Savdo rejimlari: retail_only, wholesale_only, both. Har taklif uchun birlik, minimal miqdor, buyurtma qadami, chakana narx va pog'onali ulgurji narx saqlansin.

Misol A: 1–299 dona uchun 300 000 so'm/dona, 300–999 dona uchun 270 000 so'm/dona, 1 000+ uchun 250 000 so'm/dona. 300 dona = 81 000 000 so'm; 299 dona = 89 700 000 so'm. Miqdor o'zgarganda savatcha yangi pog'onaga qayta hisoblanadi. Bu all-units narxlash: tanlangan pog'ona narxi butun shu SKU miqdoriga tatbiq etiladi. Chegarada jami summa kamayishi mumkinligi sotuvchiga ogohlantiriladi. Boshqa narxlash modeli alohida talab bo'lsagina qo'shiladi.

Misol B: qimmat uskunaning chakana narxi 300 000 000 so'm, sotuvchi belgilagan ulgurji chegaradan boshlab 270 000 000 so'm/dona. Bu foydalanuvchi aytgan narx misolining talqini, universal tarif emas.

Qoidalar:
- Narx pog'onalari bir-birini qoplamasin; minimal miqdor va qadam serverda tekshirilsin.
- Bir SKU miqdori bir buyurtmada birlashtiriladi. Turli sotuvchi yoki variantlar miqdori avtomatik birlashtirilmaydi.
- Dona faqat butun son; og'irlik uchun DECIMAL. kg va tonna bir asosiy birlikka aylantiriladi: 1 t = 1 000 kg. Qutidagi dona soni mahsulotda beriladi.
- Pul float bilan hisoblanmaydi: minor birlikdagi integer yoki hujjatlashtirilgan fixed precision DECIMAL ishlatilsin; API aniq qiymatlarni string orqali uzatsin.
- Kuponlarning pog'onali narxga qo'shilish siyosati sozlanadi; standartda ulgurji narx bilan kupon yig'ilmaydi.
- Buyurtmada nom, SKU, birlik, miqdor, narx, chegirma, komissiya va boshqa summalar snapshot bo'lib qoladi.
- RFQ narxi sotuvchi tomonidan tasdiqlanadi, amal qilish muddati bor; qabul qilishda zaxira va shartlar qayta tekshiriladi.
- Freight/tonnalik yuk uchun yetkazish narxi alohida taklif talab qilishi mumkin; bunday checkout yakuniy narx tasdiqlanmaguncha to'lov olmaydi.

## 5. Savatcha, checkout, buyurtma va pul

Ko'p sotuvchili savatcha bitta xarid oqimida parent order va har sotuvchi uchun suborder yaratadi. Har suborder bir necha shipmentga bo'linishi mumkin. Xaridor qaysi jo'natmada qaysi mahsulot borligini ko'rsin.

Checkout: manzil → serverda narx/zaxira tekshiruvi → yetkazish usuli va narxi → yakuniy hisob → to'lov. Pul yechilishi, buyurtma holati va shipment holati alohida holat mashinalari bo'lsin.

Buyurtma oqimi: pending_payment → confirmed → processing → completed; tegishli bosqichlarda cancelled. Shipment: created → packed → handed_over → in_transit → out_for_delivery → delivered; exception va returned alohida. To'lov: pending → authorized/captured yoki failed; partial_refund/refunded alohida. Har o'tish uchun ruxsat, sabab va timestamp talab qilinadi. Parent order holati suborderlardan hisoblanadi.

Zaxira: MySQL tranzaksiya va row-level lock bilan vaqtinchalik reservation; muddati tugaganda bo'shatish. Parallel so'rovlarda oxirgi mahsulot ikki kishiga sotilmasin. Reservation expiry va kechikkan payment webhook to'qnashuvi deterministik hal qilinsin: zaxira qayta tasdiqlanmasa avtomatik fulfillment bo'lmasin, refund/reconciliation oqimiga o'tsin.

Checkout, to'lov, webhook va refund idempotent bo'lsin. Bir kalit boshqa payload bilan kelsa rad etilsin. To'lov muvaffaqiyati brauzer redirectiga qarab belgilanmasin; imzolangan provayder webhook va zarur server tekshiruvi bilan tasdiqlansin. Callback takrorlanishi, tartibsiz kelishi va timeout hisobga olinsin. Outbox orqali commitdan keyingi xabarlar yo'qolmasin.

Payment adapter interfeysi va soxta to'lov uchun faqat demo adapter yarating. Haqiqiy provayder foydalanuvchi shartnomasi va credentiallari bilan alohida ulanadi. Karta ma'lumotlarini o'z bazangizda saqlamang. Sotuvchi komissiyasi, platforma tushumi, refund va payout o'zgarmas ledger yozuvlari bilan yuritilsin; sotuvchi balansi oddiy qo'lda o'zgartiriladigan raqam bo'lmasin. Yetkazilganidan keyin sozlanadigan ushlab turish muddati va nizolarni hisobga olib payout yarating.

Qisman bekor qilish, qisman refund, qaytarilgan zaxirani tekshirish, yo'qolgan jo'natma va to'lovni moslashtirish (reconciliation) oqimlari bo'lsin. Sharh faqat yetkazilgan mahsulot xaridoridan qabul qilinsin.

## 6. Haqiqiy xaritada kuzatish

Xarita real koordinatalarni ko'rsatadi. Marker animatsiyasi yoki demo marshrut haqiqiy GPS o'rnini bosa olmaydi.

Manbalar: o'z kuryerining Flutter ilovasi, logistika hamkorining API/webhooki yoki ombor skan hodisalari. GPS bo'lmagan tashuvda xaridor oxirgi tasdiqlangan nuqta va hodisani ko'radi; yo'lda taxminiy nuqta real joylashuv sifatida ko'rsatilmaydi.

Kuryer ilovasi aktiv yetkazish vaqtida ruxsat bilan GPS yuboradi: lat, lng, accuracy, speed, heading, recorded_at, event_id. Server received_at saqlaydi. Harakat va batareyaga mos namuna olish; dastlabki maqsad aktiv safarda 5–15 soniya, lekin OS, tarmoq va batareya tufayli kafolat emas. Fon rejimi uchun Android/iOS ruxsatlari va platforma talablari alohida amalga oshiriladi va haqiqiy qurilmada tekshiriladi.

Server faqat biriktirilgan kuryerni qabul qiladi; coordinate range, timestamp, aniqlik, mumkin bo'lmagan sakrash, rate limit va takror event_id ni tekshiradi. Kech kelgan nuqta markerning orqaga sakrashiga sabab bo'lmasin. Offline nuqtalar vaqt belgisi bilan kichik navbatda saqlanib keyin yuborilsin; eski nuqta jonli deb ko'rsatilmasin.

Xaridor ekrani: shipment tanlash, xarita, oxirgi nuqta va yangilanish vaqti, yo'nalish mavjud bo'lsa, status timeline, taxminiy kelish oralig'i, kechikish sababi. 60 soniyadan eski nuqta uchun eskirgan indikator, 5 daqiqadan keyin aloqa yo'q xabari — boshlang'ich sozlanadigan qiymatlar. Aloqa tiklanganda avtomatik yangilansin.

ETA marshrut provayderi, masofa, mavjud trafik va to'xtashlar asosida taxmin qilinadi; manba bo'lmasa taxminiy yetkazish oynasi ko'rsatiladi. Aniq vaqt yoki kechikish sababi uydirilmaydi. GPS ishlayotgan kuryer nuqtasi xaridorga faqat uning aktiv yetkazish oynasida ko'rsatiladi; boshqa xaridorlarning manzillari oshkor qilinmaydi.

Production: autentifikatsiyali private WebSocket kanallari; kanalga kirishda order ownership tekshiruvi. Demo: 10–15 soniyali polling, ekranni yopganda to'xtash, backoff va oxirgi event cursor. Xarita tile/routing provayderi alohida adapter, credentiallar va kvotalar bilan. Ochiq xarita serverlarini cheksiz bepul infratuzilma deb qabul qilmang.

GPS tarixining saqlash muddati sozlansin; boshlang'ich taklif 30 kun, keyin agregatsiya/o'chirish. Bu yuridik muddat emas. Topshirishda OTP yoki ruxsatli foto isboti, maxfiy saqlash va audit talab qilinadi.

## 7. Texnologiya va loyiha tuzilishi

Backend: qo'llab-quvvatlanayotgan Laravel/PHP kombinatsiyasi, MySQL InnoDB va utf8mb4. Implementatsiya boshlanishida hosting PHP versiyasi/extensions va framework compatibility tekshirilsin, dependency versiyalari lock fayllarida qotirilsin. Avval modulli monolit: Identity, Catalog, Pricing, Inventory, Cart, Orders, Payments, Shipping, Tracking, Sellers, Ledger, Notifications, Support.

Veb: Laravel Blade + Livewire + Tailwind yo'nalishi hostingga mos boshlang'ich tanlov. Biznes qoidalari web komponentlarda takrorlanmaydi; umumiy application/domain servislaridan foydalaniladi. Build aktivlari oldindan yig'iladi; oddiy hostingda doimiy Node server talab qilinmaydi.

Mobil: Flutter/Dart, feature-based tuzilma, presentation/domain/data qatlamlari, Riverpod yoki BLoC dan bittasi tanlanadi, router, typed API client, xavfsiz token saqlash. Katalogning oxirgi ko'rilgan qismi va savatcha drafti offline o'qiladi. To'lov yoki tasdiqlangan buyurtma offline uydirilmaydi; qayta ulanishda narx va zaxira serverda tekshiriladi.

Repo:
```text
backend/                 Laravel API, web, seller va admin
apps/customer_flutter/   Android/iOS xaridor
apps/courier_flutter/    Android/iOS kuryer
packages/                Zarur bo'lsa umumiy Dart turlari
docs/                    Talablar, OpenAPI, arxitektura, ishga tushirish
infra/                   Lokal va production konfiguratsiyalar
deploy/shared-hosting/   Tayyorlash skriptlari va yo'riqnoma
database/exports/        schema.sql va demo_seed.sql
tests/load/              Yuklama senariylari
```

API /api/v1 prefiksi; OpenAPI kontrakt; izchil xatolar, request ID, cursor pagination, ruxsat etilgan filter/sortlar. Muhim yo'llar: auth, products, categories, sellers, cart, checkout/quote, orders, shipments/{id}/tracking, courier/location-batches, payments/webhooks/{provider}, returns, seller/*, admin/*. Taxminiy quote muddati va narx o'zgargan holat uchun alohida xato kodi bo'lsin.

## 8. Ma'lumotlar modeli

Asosiy jadvallar: users, roles, permissions, user_roles, addresses, sellers, seller_members, seller_documents, categories, products, product_variants, product_media, attributes, variant_attributes, seller_offers, price_tiers, warehouses, inventory_balances, inventory_movements, inventory_reservations, carts, cart_items, checkout_quotes, orders, seller_orders, order_items, payments, payment_events, refunds, shipments, shipment_items, shipment_events, courier_assignments, tracking_points, delivery_proofs, coupons, coupon_redemptions, rfqs, rfq_items, quotations, quotation_items, return_requests, return_items, reviews, notifications, support_tickets, ledger_entries, payout_batches, audit_logs, idempotency_keys, outbox_events.

Schema foreign key, unique va check constraintlar bilan himoyalansin. Inventory balance uchun warehouse+SKU unique; webhook uchun provider+event_id unique; tracking uchun courier+event_id unique; narx pog'onasi qoidalari servisda ham tekshirilsin. Order/payment/ledger yozuvlari tasodifiy cascade delete bilan o'chmasin. Asosiy so'rovlarga mos compound indekslar va EXPLAIN natijalari hujjatlashtirilsin. tracking_points va audit o'sishini retention va keyinchalik partition bilan boshqaring. UTC saqlansin, foydalanuvchiga Asia/Tashkent bo'yicha ko'rsatilsin.

## 9. Panelga yuklash va demo

Oddiy hostingda PHP fayllarini public papkaga nusxalashning o'zi barcha production xizmatlarini ishga tushirmaydi. Package shunga mos tayyorlansin:

1. Hosting PHP/extensions, MySQL, HTTPS, cron, document root, upload limit va CLI mavjudligini tekshirish ro'yxati.
2. Lokal/CI muhitda Composer production dependencylari va frontend aktivlarini yig'ish; vendor va build aktivlari bilan zip.
3. Ilova kodi public_html tashqarisida, faqat backend/public vebga ochiq. Hosting subfolder bilan ishlasa base URL, asset va API yo'llari tekshirilsin. Butun loyiha, .env va vendorni ommaga ochmang.
4. .env.example qiymatlarini to'ldirish, APP_KEY yaratish, APP_DEBUG=false, yozish huquqlarini faqat kerakli papkalarga berish.
5. phpMyAdmin orqali yangi bazaga schema.sql, keyin ixtiyoriy demo_seed.sql. Eksport migrationlar bilan mos bo'lsin. Yangilash mavjud bazani drop qilmasin; alohida migration va backup jarayoni bo'lsin.
6. Standart admin parolini zipga qo'shmang. Bir martalik setup token/CLI orqali admin yaratilsin; web installer bo'lsa HTTPS, kuchli token va muvaffaqiyatdan keyin doimiy lock talab qilinsin.
7. Cron bo'lsa scheduler va bir-birini bosmaydigan bounded queue worker. Cron bo'lmasa demo cheklovlari ko'rinsin; rezervlar har checkoutda ham tekshirilsin, muhim ishlar jim yo'qolmasin.
8. Demo GPS, demo payment va soxta buyurtmalarda DEMO belgisi. Seed shaxsiy haqiqiy ma'lumot ishlatmasin. Demo rejimini productionda tasodifan yoqish bloklansin.
9. Smoke test: login, katalog, 300 donalik narx, checkout, sotuvchi ishlovi, demo tracking, bekor qilish/refund.

Production profil: PHP app instansiyalari, MySQL, Redis cache/queue, worker/scheduler, WebSocket servisi, obyekt saqlash va CDN, reverse proxy/load balancer, monitoring va backup. Bir xil kod konfiguratsiya orqali profil tanlasin. Shared hostingga bu xizmatlar sig'masa boshqariladigan serverga ko'chirish yo'li hujjatlashtirilsin.

Flutter build alohida: Android APK demo, AAB release; iOS uchun macOS/Xcode va Apple signing zarur. Backend zip ichiga Dart fayl tashlash telefonga ilova o'rnatmaydi. API URL muhit bo'yicha beriladi; server secretlari mobil ilovaga joylanmaydi.

## 10. Xavfsizlik va operatsiyalar

Parameterized query/ORM, input validation, CSRF himoyasi web oqimlarda, XSS escaping, cheklangan CORS, token revoke, sessiya rotatsiyasi, brute-force/rate limit, admin MFA, maxfiy fayllarga signed URL, upload hajmi va haqiqiy MIME tekshiruvi. Fayllarni bajariladigan public PHP sifatida saqlamang. Audit: narx, zaxira, vakolat, refund, payout va buyurtma o'zgarishlari.

Loglarda parol/token, to'liq to'lov ma'lumoti yoki keraksiz GPS va telefon raqami bo'lmasin. Health/readiness endpointlar, error tracking, API latency, database saturation, queue lag, GPS freshness, payment mismatch, oversell va failed jobs bo'yicha monitoring.

Zaxira nusxalar shifrlansin; tiklash sinovi majburiy. Dastlabki production maqsadi RPO ≤15 daqiqa va RTO ≤4 soat — infratuzilma, backup/PITR hamda tiklash sinovi bilan tasdiqlanmaguncha kafolat emas. Tashqi integratsiyalar timeout, retry+backoff, circuit breaker va idempotency bilan ishlasin.

## 11. Masshtab va o'lchanadigan qabul

Millionlab ro'yxatdan o'tgan foydalanuvchi millionlab bir vaqtning o'zida aktiv foydalanuvchi degani emas. Bitta arzon shared hostingga bunday kafolat berilmaydi. Sig'im test shartlari va natijalari bilan isbotlanadi.

Dastlabki test ma'lumotlari: 100 000 mahsulot, 1 000 sotuvchi, 1 000 000 test order yozuvi. Boshlang'ich yuklama: 1 000 parallel sessiya, 100 katalog so'rovi/s, 10 checkout/s, alohida 1 000 kuryer × har 10 soniyada GPS ≈100 update/s. Bu capacity va'dasi emas, sinov profili; test muhiti konfiguratsiyasi va warm/cold cache holati yozilsin.

Maqsadlar: katalog API p95 <500 ms, checkoutning ichki qismi p95 <1,5 s, kutilmagan 5xx <0,1%; tashqi to'lov va marshrut vaqtlari alohida o'lchanadi. Qulay tarmoqda GPS o'lchanganidan ekranga chiqishigacha p95 <20 s. Web LCP <2,5 s va CLS <0,1 maqsadi belgilangan o'rta sinf qurilma/tarmoqda tekshirilsin. Flutter animatsiyalari real o'rta sinf telefonda profile/release rejimida o'lchansin.

Katalog caching, pagination, rasm o'lchamlari/lazy loading, N+1 ni yo'qotish, hot query indekslari, asinxron ishlar va CDN birinchi bosqichdan. Qidiruv dastlab indekslangan MySQL bilan; hajm va sifat talabiga ko'ra alohida qidiruv servisi. Read replica eventual consistency hisobga olinadi; checkout va stock authoritative primarydan ishlaydi. Microservice/sharding faqat o'lchangan tor joy asosida ajratiladi.

Majburiy senariylar:
- 299/300/999/1 000 chegaralari, quti va tonna konversiyasi, noto'g'ri qadam, manfiy/katta qiymatlar.
- Bir qolgan SKU uchun parallel xaridlar, takror checkout va expiry/payment race.
- Ko'p sotuvchi, qisman shipment, qisman bekor/refund va ledger tengligi.
- Boshqa sotuvchi/order/GPSga ruxsatsiz kirish rad etilishi.
- Webhook qayta yuborilishi va tartibsiz kelishi duplicate pul yoki order yaratmasligi.
- Internet uzilishi, GPS ruxsati rad etilishi, stale koordinata, app background/kill va batareya cheklovlari.
- Toza shared hosting install, bazani import qilish, yangilash va backupdan tiklash.

## 12. Yakuniy topshirish mezoni

Har bosqichda ishlaydigan kod, tegishli test, ishga tushirish buyrug'i va cheklovlar ro'yxati bo'lsin. Bajarilmagan integratsiya bajarildi deb belgilanmasin. Production tayyor deb aytishdan oldin real to'lov sandbox testi, haqiqiy ikki qurilmada GPS, permission/tenant isolation, yuklama, restore, monitoring va release tekshiruvlari bajarilsin.

## Texnik manbalar

Quyidagi rasmiy hujjatlar texnik yo'nalishni tekshirish uchun ishlatildi; biznes qoidalari va yuklama raqamlari ushbu loyiha uchun taklifdir.
- Laravel tranzaksiyalari: https://laravel.com/framework/docs/database/config
- Laravel navbatlari va commitdan keyingi ishlar: https://laravel.com/framework/docs/queues
- Flutter arxitekturasi: https://docs.flutter.dev/app-architecture/guide
- Flutter offline-first: https://docs.flutter.dev/app-architecture/design-patterns/offline-first
- Android fon joylashuvi: https://developer.android.com/develop/sensors-and-location/location/background
