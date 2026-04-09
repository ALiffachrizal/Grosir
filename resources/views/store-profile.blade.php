<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Grosir IJAD — Grosir Terpercaya</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">

    {{-- ===== NAVBAR ===== --}}
    <nav class="fixed top-0 w-full z-50 bg-gray-900/95 backdrop-blur-sm border-b border-white/10">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🛒</span>
                <div>
                    <h1 class="text-white font-bold text-lg leading-tight">Toko Grosir IJAD</h1>
                    <p class="text-yellow-400 text-xs">Grosir Terpercaya</p>
                </div>
            </div>
            <a href="{{ route('login') }}"
               class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold
                      px-5 py-2 rounded-lg text-sm transition">
                Login Admin
            </a>
        </div>
    </nav>

    {{-- ===== HERO SECTION ===== --}}
    <section class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen
                    flex items-center justify-center pt-16">
        <div class="max-w-6xl mx-auto px-6 py-20 text-center">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 bg-yellow-400/20 border border-yellow-400/30
                        text-yellow-400 text-sm font-medium px-4 py-2 rounded-full mb-8">
                <span>⭐</span>
                <span>Terpercaya & Berkualitas</span>
            </div>

            {{-- Judul --}}
            <h2 class="text-5xl sm:text-6xl font-bold text-white mb-6 leading-tight">
                Toko Grosir
                <span class="text-yellow-400">IJAD</span>
            </h2>

            <p class="text-gray-300 text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
                Pusat grosir terpercaya dengan harga kompetitif dan produk lengkap.
                Melayani kebutuhan sembako, snack, minuman, dan kebutuhan rumah tangga.
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="#products"
                   class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold
                          px-8 py-3.5 rounded-xl text-sm transition">
                    Lihat Produk Kami
                </a>
                <a href="#contact"
                   class="bg-white/10 hover:bg-white/20 text-white border border-white/20
                          font-semibold px-8 py-3.5 rounded-xl text-sm transition">
                    Hubungi Kami
                </a>
            </div>

            {{-- Scroll indicator --}}
            <div class="mt-16 animate-bounce">
                <span class="text-gray-400 text-2xl">↓</span>
            </div>

        </div>
    </section>

    {{-- ===== KATEGORI PRODUK ===== --}}
    <section id="products" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-6">

            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Kategori Produk</h3>
                <p class="text-gray-500 max-w-xl mx-auto">
                    Kami menyediakan berbagai kategori produk berkualitas dengan harga grosir terbaik
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">

                {{-- Sembako --}}
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200
                            rounded-2xl p-6 text-center hover:shadow-lg hover:-translate-y-1
                            transition-all duration-300 group">
                    <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">🌾</div>
                    <h4 class="font-bold text-gray-800 text-lg mb-2">Sembako</h4>
                    <p class="text-gray-500 text-sm">Beras, gula, minyak, tepung & kebutuhan pokok lainnya</p>
                </div>

                {{-- Jajanan / Snack --}}
                <div class="bg-gradient-to-br from-pink-50 to-pink-100 border border-pink-200
                            rounded-2xl p-6 text-center hover:shadow-lg hover:-translate-y-1
                            transition-all duration-300 group">
                    <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">🍿</div>
                    <h4 class="font-bold text-gray-800 text-lg mb-2">Jajanan / Snack</h4>
                    <p class="text-gray-500 text-sm">Aneka snack, kue kering, dan camilan populer</p>
                </div>

                {{-- Minuman --}}
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200
                            rounded-2xl p-6 text-center hover:shadow-lg hover:-translate-y-1
                            transition-all duration-300 group">
                    <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">🥤</div>
                    <h4 class="font-bold text-gray-800 text-lg mb-2">Minuman</h4>
                    <p class="text-gray-500 text-sm">Air mineral, minuman kemasan, dan minuman segar</p>
                </div>

                {{-- Kebutuhan Rumah Tangga --}}
                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200
                            rounded-2xl p-6 text-center hover:shadow-lg hover:-translate-y-1
                            transition-all duration-300 group">
                    <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">🧴</div>
                    <h4 class="font-bold text-gray-800 text-lg mb-2">Kebutuhan RT</h4>
                    <p class="text-gray-500 text-sm">Sabun, deterjen, pembersih & perlengkapan rumah</p>
                </div>

            </div>

        </div>
    </section>

    {{-- ===== KEUNGGULAN ===== --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-6">

            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Mengapa Pilih Kami?</h3>
                <p class="text-gray-500">Keunggulan yang membuat kami menjadi pilihan utama</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">

                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center
                                text-3xl mx-auto mb-5">
                        💰
                    </div>
                    <h4 class="font-bold text-gray-800 text-xl mb-3">Harga Grosir</h4>
                    <p class="text-gray-500 leading-relaxed">
                        Dapatkan harga terbaik untuk pembelian dalam jumlah besar.
                        Semakin banyak beli, semakin hemat!
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center
                                text-3xl mx-auto mb-5">
                        📦
                    </div>
                    <h4 class="font-bold text-gray-800 text-xl mb-3">Produk Lengkap</h4>
                    <p class="text-gray-500 leading-relaxed">
                        Tersedia {{ $totalProducts }}+ produk dari berbagai kategori.
                        Semua kebutuhan toko Anda tersedia di sini.
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center
                                text-3xl mx-auto mb-5">
                        🤝
                    </div>
                    <h4 class="font-bold text-gray-800 text-xl mb-3">Pelayanan Terbaik</h4>
                    <p class="text-gray-500 leading-relaxed">
                        Tim kami siap melayani dengan ramah dan profesional.
                        Kepuasan pelanggan adalah prioritas kami.
                    </p>
                </div>

            </div>
        </div>
    </section>

    {{-- ===== STATISTIK ===== --}}
    <section class="py-20 bg-gradient-to-br from-gray-900 to-gray-800">
        <div class="max-w-6xl mx-auto px-6">

            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold text-white mb-4">Toko Kami dalam Angka</h3>
                <p class="text-gray-400">Fakta dan angka yang membuktikan kepercayaan Anda</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">

                <div class="text-center">
                    <div class="text-5xl font-bold text-yellow-400 mb-2">{{ $totalProducts }}+</div>
                    <p class="text-gray-400">Produk Tersedia</p>
                </div>

                <div class="text-center">
                    <div class="text-5xl font-bold text-yellow-400 mb-2">{{ $totalCategories }}+</div>
                    <p class="text-gray-400">Kategori Produk</p>
                </div>

                <div class="text-center">
                    <div class="text-5xl font-bold text-yellow-400 mb-2">100%</div>
                    <p class="text-gray-400">Produk Original</p>
                </div>

                <div class="text-center">
                    <div class="text-5xl font-bold text-yellow-400 mb-2">24/7</div>
                    <p class="text-gray-400">Siap Melayani</p>
                </div>

            </div>
        </div>
    </section>

    {{-- ===== KONTAK & JAM OPERASIONAL ===== --}}
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-6">

            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Hubungi Kami</h3>
                <p class="text-gray-500">Kami siap melayani pertanyaan dan pesanan Anda</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-4xl mx-auto">

                {{-- Alamat --}}
                <div class="bg-gray-50 rounded-2xl p-6 text-center">
                    <div class="text-4xl mb-4">📍</div>
                    <h4 class="font-bold text-gray-800 mb-2">Alamat</h4>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Jl. Contoh No. 123<br>
                        Kota Anda, Provinsi<br>
                        Indonesia
                    </p>
                </div>

                {{-- Jam Operasional --}}
                <div class="bg-gray-50 rounded-2xl p-6 text-center">
                    <div class="text-4xl mb-4">🕐</div>
                    <h4 class="font-bold text-gray-800 mb-2">Jam Operasional</h4>
                    <div class="text-gray-500 text-sm space-y-1">
                        <p><strong>Senin – Sabtu</strong></p>
                        <p>07.00 – 17.00 WIB</p>
                        <p class="mt-2"><strong>Minggu</strong></p>
                        <p>08.00 – 14.00 WIB</p>
                    </div>
                </div>

                {{-- Kontak --}}
                <div class="bg-gray-50 rounded-2xl p-6 text-center">
                    <div class="text-4xl mb-4">📱</div>
                    <h4 class="font-bold text-gray-800 mb-2">Kontak</h4>
                    <div class="text-gray-500 text-sm space-y-1">
                        <p>📞 (021) 1234-5678</p>
                        <p>📱 0812-3456-7890</p>
                        <p>✉️ info@tokogrosir.com</p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    {{-- ===== FOOTER ===== --}}
    <footer class="bg-gray-900 border-t border-white/10">
        <div class="max-w-6xl mx-auto px-6 py-10">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🛒</span>
                    <div>
                        <p class="text-white font-bold">Toko Grosir IJAD</p>
                        <p class="text-gray-400 text-xs">Grosir Terpercaya</p>
                    </div>
                </div>
                <p class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} Toko Grosir IJAD. All rights reserved.
                </p>
                <a href="{{ route('login') }}"
                   class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold
                          px-5 py-2 rounded-lg text-sm transition">
                    Login Admin →
                </a>
            </div>
        </div>
    </footer>

</body>
</html>