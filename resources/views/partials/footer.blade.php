<footer class="bg-slate-900 text-slate-300 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white font-bold text-xl">S</span>
                    </div>
                    <span class="ml-3 text-2xl font-bold tracking-tight text-white">Success <span class="text-blue-500">Mandiri</span></span>
                </div>
                <p class="text-slate-400 max-w-md leading-relaxed">
                    Aplikasi Transaksi Penjualan Sawit.
                </p>
            </div>

            <!-- Links -->
            <div>
                <h4 class="text-white font-semibold mb-6">Navigasi</h4>
                <ul class="space-y-4">
                    <li><a href="#" class="hover:text-blue-500 transition-colors">Beranda</a></li>
                    <li><a href="#perusahaan" class="hover:text-blue-500 transition-colors">Perusahaan</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-blue-500 transition-colors">Masuk Sistem</a></li>
                </ul>
            </div>

            
        </div>

        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm">
            <p>&copy; {{ date('Y') }} Success Mandiri. Hak Cipta Dilindungi.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</span>
                <span>PHP v{{ PHP_VERSION }}</span>
                <span class="flex items-center">Made with <span class="text-red-500 mx-1">❤️</span> by Don Borland</span>
            </div>
        </div>
    </div>
</footer>
