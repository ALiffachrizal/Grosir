<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Toko Grosir IJAD — Grosir Terpercaya</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="bg-gray-50 antialiased">

    {{-- ========================================================= --}}
    {{-- NAVBAR --}}
    {{-- ========================================================= --}}
    <nav class="fixed top-0 left-0 right-0 z-50
                bg-slate-950/90 backdrop-blur-md
                border-b border-white/10">

        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10
                    h-20 flex items-center justify-between">

            {{-- Logo --}}
            <a href="#home"
               class="flex items-center gap-3 group">

                <div class="w-11 h-11 rounded-xl
                            bg-white/5 border border-white/10
                            flex items-center justify-center
                            group-hover:bg-yellow-400/10
                            group-hover:border-yellow-400/30
                            transition">

                    <span class="text-2xl">
                        🛒
                    </span>
                </div>

                <div>
                    <h1 class="text-white font-bold text-xl leading-tight">
                        Toko Grosir
                        <span class="text-yellow-400">
                            IJAD
                        </span>
                    </h1>

                    <p class="text-yellow-400 text-xs font-medium mt-0.5">
                        Grosir Terpercaya
                    </p>
                </div>
            </a>

            {{-- Login --}}
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2
                      bg-yellow-400 hover:bg-yellow-300
                      text-slate-950 font-bold
                      px-5 sm:px-6 py-3 rounded-xl
                      shadow-lg shadow-yellow-400/10
                      transition-all duration-200
                      hover:-translate-y-0.5">

                <span>
                    👤
                </span>

                <span class="hidden sm:inline">
                    Login Admin
                </span>

                <span class="sm:hidden">
                    Login
                </span>
            </a>
        </div>
    </nav>

    {{-- ========================================================= --}}
    {{-- HERO SECTION --}}
    {{-- ========================================================= --}}
    <section id="home"
             class="relative min-h-screen
                    flex items-center justify-center
                    overflow-hidden pt-20">

        {{-- Foto Grosir --}}
        <div class="absolute inset-0
                    bg-cover bg-center bg-no-repeat"
             style="
                background-image:
                url('{{ asset('images/toko-grosir-ijad.jpeg') }}');
             ">
        </div>

        {{-- Overlay gelap --}}
        <div class="absolute inset-0 bg-slate-950/65"></div>

        {{-- Gradient overlay --}}
        <div class="absolute inset-0
                    bg-gradient-to-b
                    from-slate-950/45
                    via-slate-950/45
                    to-slate-950/90">
        </div>

        {{-- Efek cahaya --}}
        <div class="absolute -top-32 left-1/2
                    -translate-x-1/2
                    w-[700px] h-[700px]
                    bg-yellow-400/10
                    rounded-full blur-3xl">
        </div>

        {{-- Konten Hero --}}
        <div class="relative z-10
                    max-w-5xl mx-auto
                    px-6 py-24 text-center">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2
                        bg-yellow-400/10
                        border border-yellow-400/40
                        text-yellow-300
                        text-sm font-semibold
                        px-5 py-2.5 rounded-full
                        mb-8 backdrop-blur-sm">

                <span>
                    ⭐
                </span>

                <span>
                    Terpercaya & Berkualitas
                </span>
            </div>

            {{-- Judul --}}
            <h2 class="text-5xl sm:text-6xl lg:text-7xl
                       font-extrabold text-white
                       leading-tight tracking-tight">

                Toko Grosir

                <span class="text-yellow-400">
                    IJAD
                </span>
            </h2>

            {{-- Deskripsi --}}
            <p class="text-gray-200
                      text-base sm:text-xl
                      max-w-3xl mx-auto
                      mt-7 leading-relaxed">

                Pusat grosir terpercaya dengan harga kompetitif
                dan produk lengkap.

                <span class="block mt-1">
                    Melayani kebutuhan sembako, snack, minuman,
                    dan kebutuhan rumah tangga.
                </span>
            </p>

            {{-- Tombol --}}
            <div class="flex flex-col sm:flex-row
                        items-center justify-center
                        gap-4 mt-10">

                <a href="#products"
                   class="w-full sm:w-auto
                          inline-flex items-center
                          justify-center gap-2
                          bg-yellow-400 hover:bg-yellow-300
                          text-slate-950 font-bold
                          px-8 py-4 rounded-xl
                          shadow-xl shadow-yellow-400/10
                          transition-all duration-200
                          hover:-translate-y-1">

                    <span>
                        📦
                    </span>

                    Lihat Produk Kami
                </a>

                <a href="#contact"
                   class="w-full sm:w-auto
                          inline-flex items-center
                          justify-center gap-2
                          bg-slate-950/40
                          hover:bg-slate-950/70
                          border border-yellow-400/60
                          text-white font-semibold
                          px-8 py-4 rounded-xl
                          backdrop-blur-sm
                          transition-all duration-200
                          hover:-translate-y-1">

                    <span>
                        📞
                    </span>

                    Hubungi Kami
                </a>
            </div>

            {{-- Scroll Indicator --}}
            <a href="#products"
               class="inline-flex flex-col
                      items-center gap-2
                      text-gray-300
                      hover:text-yellow-300
                      mt-16 transition">

                <span class="text-2xl animate-bounce">
                    ↓
                </span>

                <span class="text-xs sm:text-sm">
                    Scroll untuk melihat lebih banyak
                </span>
            </a>
        </div>

        {{-- Transisi bagian bawah --}}
        <div class="absolute bottom-0 left-0 right-0
                    h-24 bg-gradient-to-t
                    from-white to-transparent">
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- KATEGORI PRODUK --}}
    {{-- ========================================================= --}}
    <section id="products"
             class="py-20 bg-white">

        <div class="max-w-6xl mx-auto px-6">

            <div class="text-center mb-12">

                <span class="inline-flex items-center
                             bg-yellow-100 text-yellow-700
                             text-xs font-semibold
                             px-4 py-2 rounded-full mb-4">
                    Produk Kami
                </span>

                <h3 class="text-3xl sm:text-4xl
                           font-bold text-gray-900 mb-4">

                    Kategori Produk
                </h3>

                <p class="text-gray-500
                          max-w-xl mx-auto
                          leading-relaxed">

                    Kami menyediakan berbagai kategori produk
                    berkualitas dengan harga grosir terbaik.
                </p>
            </div>

            <div class="grid grid-cols-1
                        sm:grid-cols-2
                        lg:grid-cols-4 gap-6">

                {{-- Sembako --}}
                <div class="bg-gradient-to-br
                            from-yellow-50 to-yellow-100
                            border border-yellow-200
                            rounded-2xl p-6 text-center
                            hover:shadow-xl
                            hover:-translate-y-1
                            transition-all duration-300
                            group">

                    <div class="w-16 h-16 mx-auto mb-5
                                bg-white rounded-2xl
                                flex items-center justify-center
                                shadow-sm
                                group-hover:scale-110
                                transition-transform">

                        <span class="text-4xl">
                            🌾
                        </span>
                    </div>

                    <h4 class="font-bold text-gray-800
                               text-lg mb-2">

                        Sembako
                    </h4>

                    <p class="text-gray-500
                              text-sm leading-relaxed">

                        Beras, gula, minyak, tepung,
                        dan kebutuhan pokok lainnya.
                    </p>
                </div>

                {{-- Jajanan --}}
                <div class="bg-gradient-to-br
                            from-pink-50 to-pink-100
                            border border-pink-200
                            rounded-2xl p-6 text-center
                            hover:shadow-xl
                            hover:-translate-y-1
                            transition-all duration-300
                            group">

                    <div class="w-16 h-16 mx-auto mb-5
                                bg-white rounded-2xl
                                flex items-center justify-center
                                shadow-sm
                                group-hover:scale-110
                                transition-transform">

                        <span class="text-4xl">
                            🍿
                        </span>
                    </div>

                    <h4 class="font-bold text-gray-800
                               text-lg mb-2">

                        Jajanan / Snack
                    </h4>

                    <p class="text-gray-500
                              text-sm leading-relaxed">

                        Aneka snack, kue kering,
                        dan camilan populer.
                    </p>
                </div>

                {{-- Minuman --}}
                <div class="bg-gradient-to-br
                            from-blue-50 to-blue-100
                            border border-blue-200
                            rounded-2xl p-6 text-center
                            hover:shadow-xl
                            hover:-translate-y-1
                            transition-all duration-300
                            group">

                    <div class="w-16 h-16 mx-auto mb-5
                                bg-white rounded-2xl
                                flex items-center justify-center
                                shadow-sm
                                group-hover:scale-110
                                transition-transform">

                        <span class="text-4xl">
                            🥤
                        </span>
                    </div>

                    <h4 class="font-bold text-gray-800
                               text-lg mb-2">

                        Minuman
                    </h4>

                    <p class="text-gray-500
                              text-sm leading-relaxed">

                        Air mineral, minuman kemasan,
                        dan minuman segar.
                    </p>
                </div>

                {{-- Kebutuhan Rumah Tangga --}}
                <div class="bg-gradient-to-br
                            from-green-50 to-green-100
                            border border-green-200
                            rounded-2xl p-6 text-center
                            hover:shadow-xl
                            hover:-translate-y-1
                            transition-all duration-300
                            group">

                    <div class="w-16 h-16 mx-auto mb-5
                                bg-white rounded-2xl
                                flex items-center justify-center
                                shadow-sm
                                group-hover:scale-110
                                transition-transform">

                        <span class="text-4xl">
                            🧴
                        </span>
                    </div>

                    <h4 class="font-bold text-gray-800
                               text-lg mb-2">

                        Kebutuhan Rumah Tangga
                    </h4>

                    <p class="text-gray-500
                              text-sm leading-relaxed">

                        Sabun, deterjen, pembersih,
                        dan perlengkapan rumah.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- KEUNGGULAN --}}
    {{-- ========================================================= --}}
    <section class="py-20 bg-gray-50">

        <div class="max-w-6xl mx-auto px-6">

            <div class="text-center mb-12">

                <span class="inline-flex items-center
                             bg-blue-100 text-blue-700
                             text-xs font-semibold
                             px-4 py-2 rounded-full mb-4">

                    Keunggulan
                </span>

                <h3 class="text-3xl sm:text-4xl
                           font-bold text-gray-900 mb-4">

                    Mengapa Pilih Kami?
                </h3>

                <p class="text-gray-500">
                    Keunggulan yang membuat kami
                    menjadi pilihan utama.
                </p>
            </div>

            <div class="grid grid-cols-1
                        md:grid-cols-3 gap-8">

                {{-- Harga --}}
                <div class="bg-white rounded-2xl
                            p-8 shadow-sm
                            border border-gray-100
                            hover:shadow-lg
                            hover:-translate-y-1
                            transition-all duration-300">

                    <div class="w-16 h-16
                                bg-yellow-100 rounded-2xl
                                flex items-center justify-center
                                text-3xl mb-5">

                        💰
                    </div>

                    <h4 class="font-bold text-gray-800
                               text-xl mb-3">

                        Harga Grosir
                    </h4>

                    <p class="text-gray-500 leading-relaxed">
                        Dapatkan harga terbaik untuk pembelian
                        dalam jumlah besar. Semakin banyak beli,
                        semakin hemat.
                    </p>
                </div>

                {{-- Produk --}}
                <div class="bg-white rounded-2xl
                            p-8 shadow-sm
                            border border-gray-100
                            hover:shadow-lg
                            hover:-translate-y-1
                            transition-all duration-300">

                    <div class="w-16 h-16
                                bg-blue-100 rounded-2xl
                                flex items-center justify-center
                                text-3xl mb-5">

                        📦
                    </div>

                    <h4 class="font-bold text-gray-800
                               text-xl mb-3">

                        Produk Lengkap
                    </h4>

                    <p class="text-gray-500 leading-relaxed">
                        Tersedia {{ $totalProducts }}+ produk
                        dari berbagai kategori untuk memenuhi
                        kebutuhan toko Anda.
                    </p>
                </div>

                {{-- Pelayanan --}}
                <div class="bg-white rounded-2xl
                            p-8 shadow-sm
                            border border-gray-100
                            hover:shadow-lg
                            hover:-translate-y-1
                            transition-all duration-300">

                    <div class="w-16 h-16
                                bg-green-100 rounded-2xl
                                flex items-center justify-center
                                text-3xl mb-5">

                        🤝
                    </div>

                    <h4 class="font-bold text-gray-800
                               text-xl mb-3">

                        Pelayanan Terbaik
                    </h4>

                    <p class="text-gray-500 leading-relaxed">
                        Kami siap melayani pelanggan
                        dengan ramah dan profesional.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- STATISTIK --}}
    {{-- ========================================================= --}}
    <section class="relative py-20
                    bg-gradient-to-br
                    from-slate-950 to-slate-900
                    overflow-hidden">

        <div class="absolute inset-0 opacity-10"
             style="
                background-image:
                url('{{ asset('images/toko-grosir-ijad.jpeg') }}');

                background-size: cover;
                background-position: center;
             ">
        </div>

        <div class="absolute inset-0 bg-slate-950/80"></div>

        <div class="relative z-10
                    max-w-6xl mx-auto px-6">

            <div class="text-center mb-12">

                <h3 class="text-3xl sm:text-4xl
                           font-bold text-white mb-4">

                    Toko Kami dalam Angka
                </h3>

                <p class="text-gray-400">
                    Fakta dan angka yang menggambarkan
                    pelayanan Toko Grosir IJAD.
                </p>
            </div>

            <div class="grid grid-cols-2
                        lg:grid-cols-4 gap-6">

                <div class="bg-white/5
                            border border-white/10
                            rounded-2xl p-6 text-center
                            backdrop-blur-sm">

                    <div class="text-4xl sm:text-5xl
                                font-bold text-yellow-400 mb-2">

                        {{ $totalProducts }}+
                    </div>

                    <p class="text-gray-300">
                        Produk Tersedia
                    </p>
                </div>

                <div class="bg-white/5
                            border border-white/10
                            rounded-2xl p-6 text-center
                            backdrop-blur-sm">

                    <div class="text-4xl sm:text-5xl
                                font-bold text-yellow-400 mb-2">

                        {{ $totalCategories }}+
                    </div>

                    <p class="text-gray-300">
                        Kategori Produk
                    </p>
                </div>

                <div class="bg-white/5
                            border border-white/10
                            rounded-2xl p-6 text-center
                            backdrop-blur-sm">

                    <div class="text-4xl sm:text-5xl
                                font-bold text-yellow-400 mb-2">

                        100%
                    </div>

                    <p class="text-gray-300">
                        Produk Berkualitas
                    </p>
                </div>

                <div class="bg-white/5
                            border border-white/10
                            rounded-2xl p-6 text-center
                            backdrop-blur-sm">

                    <div class="text-4xl sm:text-5xl
                                font-bold text-yellow-400 mb-2">

                        7 Hari
                    </div>

                    <p class="text-gray-300">
                        Siap Melayani
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- KONTAK --}}
    {{-- ========================================================= --}}
    <section id="contact"
             class="py-20 bg-white">

        <div class="max-w-6xl mx-auto px-6">

            <div class="text-center mb-12">

                <span class="inline-flex items-center
                             bg-green-100 text-green-700
                             text-xs font-semibold
                             px-4 py-2 rounded-full mb-4">

                    Informasi Toko
                </span>

                <h3 class="text-3xl sm:text-4xl
                           font-bold text-gray-900 mb-4">

                    Hubungi Kami
                </h3>

                <p class="text-gray-500">
                    Kami siap melayani pertanyaan
                    dan kebutuhan pesanan Anda.
                </p>
            </div>

            <div class="grid grid-cols-1
                        md:grid-cols-3 gap-6
                        max-w-5xl mx-auto">

                {{-- Alamat --}}
                <div class="bg-gray-50
                            border border-gray-100
                            rounded-2xl p-7
                            text-center
                            hover:shadow-lg transition">

                    <div class="w-14 h-14
                                bg-red-100 rounded-2xl
                                flex items-center justify-center
                                text-3xl mx-auto mb-5">

                        📍
                    </div>

                    <h4 class="font-bold text-gray-800 mb-3">
                        Alamat
                    </h4>

                    <p class="text-gray-500
                              text-sm leading-relaxed">

                        Kp Cipadang Desa Kanoman Kecamatan Cibeber Kabupaten Cianjur<br>
                        Toko Grosir IJAD.
                    </p>
                </div>

                {{-- Jam --}}
                <div class="bg-gray-50
                            border border-gray-100
                            rounded-2xl p-7
                            text-center
                            hover:shadow-lg transition">

                    <div class="w-14 h-14
                                bg-blue-100 rounded-2xl
                                flex items-center justify-center
                                text-3xl mx-auto mb-5">

                        🕐
                    </div>

                    <h4 class="font-bold text-gray-800 mb-3">
                        Jam Operasional
                    </h4>

                    <div class="text-gray-500
                                text-sm space-y-1">

                        <p class="font-medium text-gray-700">
                            Senin – Sabtu
                        </p>

                        <p>
                            07.00 – 17.00 WIB
                        </p>

                        <p class="font-medium
                                  text-gray-700 mt-3">

                            Minggu
                        </p>

                        <p>
                            08.00 – 14.00 WIB
                        </p>
                    </div>
                </div>

                {{-- Kontak --}}
                <div class="bg-gray-50
                            border border-gray-100
                            rounded-2xl p-7
                            text-center
                            hover:shadow-lg transition">

                    <div class="w-14 h-14
                                bg-green-100 rounded-2xl
                                flex items-center justify-center
                                text-3xl mx-auto mb-5">

                        📱
                    </div>

                    <h4 class="font-bold text-gray-800 mb-3">
                        Kontak
                    </h4>

                    <div class="text-gray-500
                                text-sm space-y-2">

                        <p>
                            📞 083827161385
                        </p>

                        <p>
                            📱 083827161385
                        </p>

                        <p>
                            ✉️ ijad@gmail.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}
    <footer class="bg-slate-950
                   border-t border-white/10">

        <div class="max-w-7xl mx-auto
                    px-6 py-10">

            <div class="flex flex-col
                        md:flex-row
                        items-center justify-between
                        gap-6">

                <div class="flex items-center gap-3">

                    <div class="w-11 h-11 rounded-xl
                                bg-white/5
                                flex items-center justify-center">

                        <span class="text-2xl">
                            🛒
                        </span>
                    </div>

                    <div>
                        <p class="text-white font-bold">
                            Toko Grosir IJAD
                        </p>

                        <p class="text-yellow-400 text-xs">
                            Grosir Terpercaya
                        </p>
                    </div>
                </div>

                <!-- <p class="text-gray-400 text-sm text-center">
                    &copy; {{ date('Y') }}
                    Toko Grosir IJAD.
                    All rights reserved.
                </p> -->

                <a href="{{ route('login') }}"
                   class="bg-yellow-400
                          hover:bg-yellow-300
                          text-slate-950
                          font-semibold
                          px-5 py-2.5
                          rounded-xl
                          text-sm transition">

                    Login Admin →
                </a>
            </div>
        </div>
    </footer>

</body>
</html>