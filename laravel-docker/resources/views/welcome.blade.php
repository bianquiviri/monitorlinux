<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#3b82f6">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <title>MonitorLinux - Inteligencia Operativa 2026</title>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #050505; color: #fff; overflow-x: hidden; }
        .hero-gradient { background: radial-gradient(circle at 50% -20%, #1e40af 0%, #050505 60%); }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .glow-blue { box-shadow: 0 0 30px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body>
    <div class="hero-gradient min-h-screen">
        <!-- Nav -->
        <nav class="container mx-auto px-6 py-8 flex justify-between items-center">
            <div class="text-2xl font-extrabold tracking-tighter">
                Monitor<span class="text-blue-500">Linux</span>
            </div>
            <a href="{{ route('login') }}" class="glass px-6 py-2 rounded-full hover:bg-white/10 transition-all font-semibold">
                Iniciar Sesión
            </a>
        </nav>

        <!-- Hero Section -->
        <header class="container mx-auto px-6 pt-20 pb-32 text-center">
            <div class="inline-flex items-center gap-2 bg-blue-500/10 border border-blue-500/20 px-4 py-1.5 rounded-full text-blue-400 text-sm font-bold mb-8 animate-bounce">
                🚀 Bienvenido a MonitorLinux v2.0
            </div>
            <h1 class="text-6xl md:text-8xl font-black mb-6 tracking-tight">
                Inteligencia Operativa <br>
                <span class="bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">Para tu Servidor</span>
            </h1>
            <p class="text-gray-400 text-xl max-w-2xl mx-auto mb-12">
                Monitorización avanzada para el 2026. Gestiona recursos, seguridad y procesos en tiempo real con una interfaz diseñada para la excelencia.
            </p>
            <div class="flex flex-col md:flex-row gap-4 justify-center">
                <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-2xl font-bold text-lg shadow-xl shadow-blue-600/30 transition-all transform hover:scale-105">
                    Empezar Ahora
                </a>
                <a href="#features" class="glass px-10 py-4 rounded-2xl font-bold text-lg hover:bg-white/5 transition-all">
                    Ver Capacidades
                </a>
            </div>
        </header>

        <!-- Features Grid -->
        <section id="features" class="container mx-auto px-6 py-24">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-extrabold mb-4">Los 4 Pilares del Rendimiento</h2>
                <p class="text-gray-500">Todo lo que necesitas para mantener tu infraestructura saludable</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- CPU -->
                <div class="glass p-8 rounded-3xl glow-blue group hover:border-blue-500/50 transition-all">
                    <div class="w-14 h-14 bg-blue-600/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">CPU & Load</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Control de Load Average, I/O Wait y carga por usuario. Detecta cuellos de botella antes de que ocurran.</p>
                </div>

                <!-- Memoria -->
                <div class="glass p-8 rounded-3xl group hover:border-purple-500/50 transition-all">
                    <div class="w-14 h-14 bg-purple-600/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">RAM & Swap</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Vigilancia de memoria física y Swap. Evita el "OOM Killer" con alertas preventivas inteligentes.</p>
                </div>

                <!-- Almacenamiento -->
                <div class="glass p-8 rounded-3xl group hover:border-yellow-500/50 transition-all">
                    <div class="w-14 h-14 bg-yellow-600/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Disco e I/O</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Estado de almacenamiento en tiempo real. Análisis de Input/Output para garantizar la fluidez de datos.</p>
                </div>

                <!-- Red -->
                <div class="glass p-8 rounded-3xl group hover:border-emerald-500/50 transition-all">
                    <div class="w-14 h-14 bg-emerald-600/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Red Avanzada</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Tráfico RX/TX y monitorización de puertos abiertos. Vigilancia constante de la conectividad.</p>
                </div>
            </div>

            <!-- More Capabilities -->
            <div class="mt-32 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold mb-6">Mucho más que simples gráficas</h2>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-4">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-[10px] font-bold">✓</div>
                            <span class="text-gray-300">Gestión de Contenedores Docker (Start/Stop/Restart)</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-[10px] font-bold">✓</div>
                            <span class="text-gray-300">Análisis de Logs de Laravel, Servidor y Kernel (dmesg)</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-[10px] font-bold">✓</div>
                            <span class="text-gray-300">Seguridad: Detección de usuarios logueados y puertos</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-[10px] font-bold">✓</div>
                            <span class="text-gray-300">Salud de Servicios Críticos (MySQL, Nginx, PHP-FPM)</span>
                        </li>
                    </ul>
                </div>
                <div class="glass p-1 rounded-3xl overflow-hidden shadow-2xl">
                    <img src="{{ asset('img/dashboard-preview.png') }}" alt="Dashboard Preview" class="rounded-[1.4rem] w-full">
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="container mx-auto px-6 py-20 border-t border-white/5 text-center">
            <p class="text-gray-600">&copy; 2026 MonitorLinux. La nueva era de la monitorización de servidores.</p>
        </footer>
    </div>
</body>
</html>
