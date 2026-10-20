<x-layouts.app title="Jonli Xarita — Buyurtmani kuzatish">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        .leaflet-container {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            border-radius: 1.5rem;
            z-index: 10;
        }
        .pulse-marker {
            position: relative;
        }
        .pulse-marker::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background-color: #10B981;
            border-radius: 50%;
            z-index: -1;
            opacity: 0.75;
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.7); opacity: 0; }
            100% { transform: scale(1.7); opacity: 0; }
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20">
        
        <!-- Header & Tracking Search Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-2.5 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Jonli GPS Kuzatuv (Live)
                    </span>
                    <span class="text-xs text-[#64748B]">Trek raqami: <strong class="font-mono text-[#123C2F]">{{ $shipment->tracking_number }}</strong></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight">
                    Yetkazib berish xaritasi
                </h1>
            </div>

            <!-- Tracking Number Search & Quick Select -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <form action="{{ route('tracking') }}" method="GET" class="relative flex-1 sm:w-72">
                    <input type="text" 
                           name="trackingNumber" 
                           placeholder="Trek raqamini kiriting..." 
                           value="{{ $shipment->tracking_number }}" 
                           class="w-full h-11 pl-4 pr-24 rounded-2xl bg-white border border-[#E8ECE4] text-xs font-mono font-bold text-[#172326] focus:border-[#123C2F] focus:ring-1 focus:ring-[#123C2F] outline-none">
                    <button type="submit" class="absolute inset-y-1 right-1 px-4 rounded-xl bg-[#123C2F] text-white text-xs font-bold hover:bg-[#0d2a21] transition-colors">
                        Kuzatish
                    </button>
                </form>

                @if(!empty($allShipments) && count($allShipments) > 1)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="h-11 px-3.5 rounded-2xl bg-white border border-[#E8ECE4] text-xs font-semibold text-[#172326] hover:bg-[#FAFBF7] flex items-center gap-2">
                            <span>Boshqa jo'natmalar ({{ count($allShipments) }})</span>
                            <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-[#E8ECE4] py-2 z-50">
                            <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Mavjud jo'natmalar</div>
                            @foreach($allShipments as $s)
                                <a href="{{ route('tracking', ['trackingNumber' => $s->tracking_number]) }}" class="flex items-center justify-between px-3 py-2 text-xs hover:bg-[#FAFBF7] {{ $s->tracking_number === $shipment->tracking_number ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-[#172326]' }}">
                                    <span class="font-mono">{{ $s->tracking_number }}</span>
                                    <span class="text-[10px] uppercase font-semibold text-[#64748B]">{{ $s->status }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Grid: Map & Live Tracking Panel -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left / Main: Interactive Leaflet Map Container (8 cols) -->
            <div class="lg:col-span-8 space-y-4">
                
                <!-- Map Card -->
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-3 shadow-xs relative overflow-hidden">
                    
                    <!-- Map Controls & Status Overlay -->
                    <div class="absolute top-6 left-6 z-20 flex flex-wrap items-center gap-2">
                        <div class="px-3.5 py-1.5 rounded-2xl bg-white/95 backdrop-blur-md shadow-md border border-[#E8ECE4] text-xs font-bold text-[#123C2F] flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span id="liveStatusText">Kuryer harakatlanmoqda</span>
                        </div>
                        <div class="px-3 py-1.5 rounded-2xl bg-[#123C2F]/90 backdrop-blur-md shadow-md text-white text-xs font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#C9F46B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>ETA: <strong class="text-[#C9F46B]">~12 daqiqa</strong></span>
                        </div>
                    </div>

                    <div class="absolute top-6 right-6 z-20 flex items-center gap-2">
                        <button id="btnCenterCourier" title="Kuryerga yaqinlashtirish" class="p-2.5 rounded-2xl bg-white/95 hover:bg-white shadow-md border border-[#E8ECE4] text-[#123C2F] transition-all hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                        <button id="btnFitBounds" title="Butun marshrutni ko'rsatish" class="p-2.5 rounded-2xl bg-white/95 hover:bg-white shadow-md border border-[#E8ECE4] text-[#123C2F] transition-all hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        </button>
                    </div>

                    <!-- The Actual Leaflet Map Canvas -->
                    <div id="liveMap" class="w-full h-[460px] sm:h-[540px] rounded-2xl"></div>

                    <!-- Map Legend / Waypoints Bar -->
                    <div class="mt-3 p-3 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-blue-500 text-white flex items-center justify-center font-bold text-xs">🏢</span>
                            <div>
                                <span class="font-bold text-[#172326]">Jo'natish punkti:</span>
                                <span class="text-[#64748B]">{{ $warehouse['name'] }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold text-xs">🚚</span>
                            <div>
                                <span class="font-bold text-[#172326]">Hozirgi joylashuv:</span>
                                <span class="text-[#64748B]" id="legendSpeedText">Tezlik: {{ $latest['speed'] ?? 35 }} km/soat</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-rose-500 text-white flex items-center justify-center font-bold text-xs">📍</span>
                            <div>
                                <span class="font-bold text-[#172326]">Yetkazish manzili:</span>
                                <span class="text-[#64748B]">{{ Str::limit($destination['address'], 30) }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Simulation & Control actions -->
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#C9F46B]/30 flex items-center justify-center text-[#123C2F] font-bold">
                            📡
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-[#172326]">Jonli telemetriya sinxronizatsiyasi</h4>
                            <p class="text-[11px] text-[#64748B]">Har 4 soniyada serverdan kuryer koordinatalari avtomatik yangilanadi.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button id="btnSimulatePing" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs font-bold text-[#123C2F] hover:bg-[#123C2F] hover:text-white transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Harakatni yangilash</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Right: Delivery Details, Courier Info & Timeline (4 cols) -->
            <div class="lg:col-span-4 space-y-5">
                
                <!-- Courier Profile Card -->
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs space-y-5">
                    <div class="flex items-center justify-between pb-4 border-b border-[#E8ECE4]">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Kuryer ma'lumotlari</span>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase">Tekshirilgan</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-[#123C2F] text-[#C9F46B] flex items-center justify-center text-xl font-extrabold shadow-sm">
                            🚚
                        </div>
                        <div class="flex-1">
                            <h3 class="font-extrabold text-base text-[#172326]">{{ $shipment->courier?->name ?? 'Rustam Kuryer' }}</h3>
                            <div class="flex items-center gap-2 text-xs text-[#64748B] mt-0.5">
                                <span class="text-amber-500 font-bold">★ 4.9</span>
                                <span>•</span>
                                <span>350+ yetkazilgan</span>
                            </div>
                            <p class="text-[11px] font-mono font-semibold text-[#123C2F] mt-1">Labo (01 A 777 AA)</p>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center gap-2">
                        <a href="tel:+998971234567" class="flex-1 py-3 px-4 rounded-2xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Qo'ng'iroq qilish</span>
                        </a>
                        <button type="button" @click="alert('Kuryerga SMS xabar jo\'natildi: Mijoz kutmoqda')" class="p-3 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-[#123C2F] hover:bg-white text-xs font-bold transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </button>
                    </div>

                    <!-- OTP Code Box for Safe Handover -->
                    <div class="p-4 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-center space-y-1.5">
                        <span class="text-[11px] font-semibold text-[#64748B]">Qabul qilish tasdiq kodi (OTP):</span>
                        <div class="text-2xl font-black font-mono tracking-widest text-[#123C2F]">
                            8492
                        </div>
                        <p class="text-[10px] text-[#64748B]">Tovarni qabul qilganingizda ushbu kodni kuryerga ayting.</p>
                    </div>
                </div>

                <!-- Package & Delivery Address Details -->
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs space-y-4">
                    <h3 class="font-extrabold text-sm text-[#172326] pb-3 border-b border-[#E8ECE4]">
                        Yetkazilayotgan buyurtma
                    </h3>

                    <!-- Destination card -->
                    <div class="space-y-1.5 text-xs">
                        <span class="font-bold text-[#64748B] uppercase text-[10px] tracking-wider">Yetkazish manzili:</span>
                        <p class="font-semibold text-[#172326]">{{ $destination['address'] }}</p>
                        <p class="text-[#64748B]">{{ $destination['recipient'] }} • {{ $destination['phone'] }}</p>
                    </div>

                    <!-- Ordered Products -->
                    <div class="pt-3 border-t border-[#E8ECE4] space-y-2">
                        <span class="font-bold text-[#64748B] uppercase text-[10px] tracking-wider">Tarkibi:</span>
                        @if($shipment->suborder && $shipment->suborder->items->count())
                            @foreach($shipment->suborder->items as $item)
                                <div class="flex items-center justify-between text-xs py-1">
                                    <span class="text-[#172326] font-medium truncate max-w-[200px]">{{ $item->product_name }}</span>
                                    <span class="font-bold text-[#123C2F]">×{{ $item->quantity }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[#172326]">Gipsokarton Knauf 9.5mm</span>
                                <span class="font-bold text-[#123C2F]">×50 dona</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[#172326]">Kafel Yelimi Knauf Perlfix 25kg</span>
                                <span class="font-bold text-[#123C2F]">×10 qop</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Delivery Timeline (Voqealar xronologiyasi) -->
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs space-y-4">
                    <h3 class="font-extrabold text-sm text-[#172326] pb-3 border-b border-[#E8ECE4]">
                        Yetkazib berish bosqichlari
                    </h3>

                    <div class="relative pl-6 space-y-5 text-xs before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#E8ECE4]">
                        
                        <!-- Step 4: Out for Delivery (Active) -->
                        <div class="relative">
                            <span class="absolute -left-6 top-0.5 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm ring-4 ring-emerald-100"></span>
                            <span class="font-bold text-[#172326] block">Yetkazib berilmoqda (Kuryer yo'lda)</span>
                            <p class="text-[11px] text-[#64748B] mt-0.5">Amir Temur shoh ko'chasida, manzilga 10-15 daqiqa qoldi</p>
                            <span class="text-[10px] font-mono text-emerald-700 font-bold">Hozir</span>
                        </div>

                        <!-- Step 3: Picked up -->
                        <div class="relative">
                            <span class="absolute -left-6 top-0.5 w-4 h-4 rounded-full bg-[#123C2F] border-2 border-white shadow-sm"></span>
                            <span class="font-bold text-[#172326] block">Ombordan qabul qilindi</span>
                            <p class="text-[11px] text-[#64748B] mt-0.5">Sergeli markaziy omboridan mashinaga yuklandi</p>
                            <span class="text-[10px] font-mono text-[#64748B]">30 daqiqa oldin</span>
                        </div>

                        <!-- Step 2: Preparing -->
                        <div class="relative">
                            <span class="absolute -left-6 top-0.5 w-4 h-4 rounded-full bg-[#123C2F] border-2 border-white shadow-sm"></span>
                            <span class="font-bold text-[#172326] block">Tayyorlandi va qadoqlandi</span>
                            <p class="text-[11px] text-[#64748B] mt-0.5">Sotuvchi tomonidan tekshirilib topshirildi</p>
                            <span class="text-[10px] font-mono text-[#64748B]">45 daqiqa oldin</span>
                        </div>

                        <!-- Step 1: Placed -->
                        <div class="relative">
                            <span class="absolute -left-6 top-0.5 w-4 h-4 rounded-full bg-[#123C2F] border-2 border-white shadow-sm"></span>
                            <span class="font-bold text-[#172326] block">Buyurtma rasmiylashtirildi</span>
                            <span class="text-[10px] font-mono text-[#64748B]">1 soat oldin</span>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Leaflet Map Initialization Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initial data from server
            const warehouse = @json($warehouse);
            const destination = @json($destination);
            const rawLocations = @json($locations);
            let latest = @json($latest);

            // Coordinates
            const warehouseLatLng = [warehouse.lat, warehouse.lng];
            const destLatLng = [destination.lat, destination.lng];
            let courierLatLng = [latest.lat, latest.lng];

            // 1. Initialize Map centered between points
            const map = L.map('liveMap', {
                zoomControl: true,
                attributionControl: false
            }).setView(courierLatLng, 13);

            // 2. OpenStreetMap High Quality Tile Layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                subdomains: ['a', 'b', 'c']
            }).addTo(map);

            // 3. Custom HTML Icons
            const createHtmlIcon = (emoji, bgColor, isPulse = false) => {
                return L.divIcon({
                    className: isPulse ? 'custom-div-icon pulse-marker' : 'custom-div-icon',
                    html: `<div style="
                        background-color: ${bgColor}; 
                        width: 40px; 
                        height: 40px; 
                        border-radius: 50%; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        font-size: 20px; 
                        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
                        border: 3px solid white;
                    ">${emoji}</div>`,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20],
                    popupAnchor: [0, -22]
                });
            };

            const warehouseIcon = createHtmlIcon('🏢', '#2563EB');
            const courierIcon = createHtmlIcon('🚚', '#10B981', true);
            const destIcon = createHtmlIcon('📍', '#EF4444');

            // 4. Add Warehouse Marker
            const warehouseMarker = L.marker(warehouseLatLng, { icon: warehouseIcon })
                .addTo(map)
                .bindPopup(`<strong>${warehouse.name}</strong><br><span style="font-size: 11px; color: #64748B;">${warehouse.address}</span>`);

            // 5. Add Destination Marker
            const destMarker = L.marker(destLatLng, { icon: destIcon })
                .addTo(map)
                .bindPopup(`<strong>Yetkazish manzili</strong><br><span style="font-size: 11px; color: #64748B;">${destination.address}</span>`);

            // 6. Add Courier Live Marker
            const courierMarker = L.marker(courierLatLng, { icon: courierIcon })
                .addTo(map)
                .bindPopup(`<strong>Kuryer: Rustam</strong><br><span style="font-size: 11px; color: #059669;">Harakat tezligi: ${latest.speed || 35} km/soat</span>`);

            // Open courier popup initially
            courierMarker.openPopup();

            // 7. Render Polyline Route
            const routePoints = [
                warehouseLatLng,
                ...rawLocations.map(p => [p.lat, p.lng]),
                destLatLng
            ];

            const routeLine = L.polyline(routePoints, {
                color: '#123C2F',
                weight: 5,
                opacity: 0.85,
                dashArray: '8, 8',
                lineJoin: 'round'
            }).addTo(map);

            // Fit bounds to display full route
            const allCoords = [warehouseLatLng, courierLatLng, destLatLng];
            map.fitBounds(L.latLngBounds(allCoords), { padding: [50, 50] });

            // Button actions
            document.getElementById('btnCenterCourier')?.addEventListener('click', function () {
                map.flyTo(courierLatLng, 15, { animate: true, duration: 1.2 });
                courierMarker.openPopup();
            });

            document.getElementById('btnFitBounds')?.addEventListener('click', function () {
                map.fitBounds(L.latLngBounds(allCoords), { padding: [50, 50], animate: true });
            });

            // 8. Live Real-time Simulation & Polling
            let step = 0;
            const simulatedSteps = [
                { lat: 41.2995, lng: 69.2725, speed: 34, note: 'Amir Temur shoh ko\'chasida' },
                { lat: 41.3025, lng: 69.2745, speed: 38, note: 'Toshkent Davlat Universiteti yaqinida' },
                { lat: 41.3060, lng: 69.2768, speed: 32, note: 'Mustaqillik maydoni chorrahasida' },
                { lat: 41.3090, lng: 69.2785, speed: 28, note: 'Manzilga yaqinlashmoqda (~500m)' },
                { lat: 41.3111, lng: 69.2797, speed: 15, note: 'Manzilga yetib keldi!' }
            ];

            function advanceCourier() {
                step = (step + 1) % simulatedSteps.length;
                const nextPt = simulatedSteps[step];
                courierLatLng = [nextPt.lat, nextPt.lng];

                // Smooth marker pan
                courierMarker.setLatLng(courierLatLng);
                courierMarker.setPopupContent(`<strong>Kuryer: Rustam</strong><br><span style="font-size: 11px; color: #059669;">${nextPt.note}<br>Tezlik: ${nextPt.speed} km/soat</span>`);
                
                const legendSpeed = document.getElementById('legendSpeedText');
                if (legendSpeed) {
                    legendSpeed.textContent = `Tezlik: ${nextPt.speed} km/soat (${nextPt.note})`;
                }

                // If close to destination, update badge
                if (step === simulatedSteps.length - 1) {
                    const statusText = document.getElementById('liveStatusText');
                    if (statusText) statusText.textContent = 'Manzilga yetib keldi!';
                }
            }

            document.getElementById('btnSimulatePing')?.addEventListener('click', function () {
                advanceCourier();
                map.panTo(courierLatLng, { animate: true });
            });

            // Automatic live simulation step every 8 seconds
            setInterval(function () {
                advanceCourier();
            }, 8000);
        });
    </script>
</x-layouts.app>