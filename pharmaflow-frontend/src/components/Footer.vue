<template>
    <footer class="bg-slate-900 text-white mt-24">
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid md:grid-cols-4 gap-10">
                <!-- Brand -->
                <div>
                    <h2 class="text-2xl font-bold mb-4">🏥 PharmaFlow</h2>

                    <p class="text-slate-400 leading-relaxed">
                        Platform apotek modern untuk pemesanan obat online yang
                        cepat, aman, dan terpercaya.
                    </p>
                </div>

                <!-- Menu -->
                <div>
                    <h3 class="font-bold mb-4">Menu</h3>

                    <div class="flex flex-col gap-3 text-slate-400">
                        <router-link to="/"> Beranda </router-link>

                        <router-link to="/products"> Produk </router-link>

                        <router-link to="/orders"> Pesanan </router-link>
                    </div>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="font-bold mb-4">Kontak</h3>

                    <div class="text-slate-400 space-y-2">
                        <p>📱 +62 812-3456-7890</p>
                        <p>✉️ info@pharmaflow.com</p>
                        <p>📍 Kota Jambi</p>
                    </div>
                </div>

                <!-- Operational -->
                <div>
                    <h3 class="font-bold mb-4">Jam Operasional</h3>

                    <div class="text-slate-400 space-y-2">
                        <p>Senin - Minggu</p>
                        <p>08:00 - 22:00 WIB</p>
                        <p>Layanan Online 24 Jam</p>
                    </div>
                </div>
            </div>

            <div
                class="border-t border-slate-800 mt-10 pt-6 text-center text-slate-500"
            >
                © 2026 PharmaFlow • Apotek RHD Farma
            </div>
        </div>
    </footer>
</template>
