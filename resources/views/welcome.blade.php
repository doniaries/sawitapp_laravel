<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplikasi Transaksi Penjualan Sawit</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
        /* Efek Bintang */
        #stars {
            width: 1px;
            height: 1px;
            background: transparent;
            box-shadow: 1744px 113px #FFF , 185px 1238px #FFF , 1184px 1729px #FFF , 1324px 1957px #FFF , 1744px 113px #FFF , 1184px 1729px #FFF; /* Placeholder, will generate dynamic shadow in JS or more extensive CSS */
        }
        
        .stars-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            opacity: 0.5;
            animation: twinkle var(--duration) infinite ease-in-out;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 overflow-x-hidden">
    
    @include('partials.header')

    <!-- Stars Background -->
    <div class="stars-container" id="stars-container"></div>

    <div class="relative min-h-screen pt-32 pb-24 overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute top-0 -left-4 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob dark:opacity-10 dark:bg-blue-600"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000 dark:opacity-10 dark:bg-purple-600"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000 dark:opacity-10 dark:bg-pink-600"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            <!-- Company Cards Section -->
            @if($perusahaans->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($perusahaans as $perusahaan)
                        <div class="group relative bg-white/70 dark:bg-slate-900/70 backdrop-blur-md p-8 rounded-[2.5rem] border border-white/50 dark:border-slate-800 hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-500 transform hover:-translate-y-2">
                            <div class="flex items-center mb-8">
                                <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white shadow-lg flex items-center justify-center p-3 ring-4 ring-slate-100 dark:ring-slate-800">
                                    <img src="{{ $perusahaan->logo_url }}" alt="{{ $perusahaan->name }}" class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform duration-500">
                                </div>
                                <div class="ml-5">
                                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors leading-tight">{{ $perusahaan->name }}</h3>
                                    <span class="inline-flex items-center mt-2 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                        Sistem Aktif
                                    </span>
                                </div>
                            </div>
                            
                            <div class="space-y-4 mb-8">
                                <div class="flex items-start text-slate-600 dark:text-slate-400">
                                    <svg class="w-6 h-6 text-blue-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="text-sm leading-relaxed line-clamp-2">{{ $perusahaan->alamat ?? 'Alamat tidak tersedia' }}</span>
                                </div>
                                <div class="flex items-center text-slate-600 dark:text-slate-400">
                                    <svg class="w-6 h-6 text-blue-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span class="text-sm font-medium">{{ $perusahaan->telepon ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
                                <a href="{{ route('login') }}" class="w-full flex items-center justify-between px-6 py-4 bg-slate-100 dark:bg-slate-800 hover:bg-blue-600 hover:text-white rounded-2xl font-bold transition-all duration-300 group/btn">
                                    <span>Akses Sistem</span>
                                    <svg class="w-5 h-5 group-hover/btn:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-800">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Belum Ada Perusahaan</h3>
                    <p class="text-slate-500">Saat ini belum ada perusahaan aktif yang terdaftar di sistem.</p>
                </div>
            @endif
        </div>
    </div>

    @include('partials.footer')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('stars-container');
            const starCount = 150;

            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                
                // Random position
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                
                // Random size
                const size = Math.random() * 2 + 1;
                
                // Random animation duration
                const duration = Math.random() * 3 + 2;
                
                star.style.left = `${x}%`;
                star.style.top = `${y}%`;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.setProperty('--duration', `${duration}s`);
                
                container.appendChild(star);
            }
        });
    </script>
</body>
</html>
