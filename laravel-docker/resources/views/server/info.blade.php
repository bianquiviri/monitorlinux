<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Servidor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            50: '#f7f7f8',
                            100: '#ececf1',
                            200: '#d9d9e3',
                            300: '#c5c5d2',
                            400: '#acacbe',
                            500: '#8e8ea0',
                            600: '#6e6e80',
                            700: '#4a4a5a',
                            800: '#343541',
                            900: '#202123',
                            950: '#0d0d0f',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-950 text-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-2 bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                Dashboard del Servidor
            </h1>
            <p class="text-dark-400">Información en tiempo real del sistema</p>
            <p class="text-sm text-dark-500 mt-2">{{ $current_time }}</p>
        </div>

        <!-- Grid de métricas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- PHP Version -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Versión de PHP</p>
                        <p class="text-2xl font-bold text-white">{{ $php_version }}</p>
                    </div>
                </div>
            </div>

            <!-- Laravel Version -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Laravel</p>
                        <p class="text-2xl font-bold text-white">v{{ $laravel_version }}</p>
                    </div>
                </div>
            </div>

            <!-- Sistema Operativo -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Sistema Operativo</p>
                        <p class="text-2xl font-bold text-white">{{ $os_name }}</p>
                    </div>
                </div>
            </div>

            <!-- Memoria -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Uso de Memoria</p>
                        <p class="text-2xl font-bold text-white">{{ $memory_usage }}</p>
                    </div>
                </div>
                <div class="w-full bg-dark-800 rounded-full h-2">
                    <div class="bg-purple-500 h-2 rounded-full" style="width: {{ round(memory_get_usage(true) / (128 * 1024 * 1024) * 100) }}%"></div>
                </div>
                <p class="text-xs text-dark-500 mt-2">Límite: {{ $memory_limit }}</p>
            </div>

            <!-- Espacio en Disco -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg md:col-span-2">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Espacio en Disco</p>
                        <p class="text-2xl font-bold text-white">{{ $disk_used_percent }}% usado</p>
                    </div>
                </div>
                <div class="w-full bg-dark-800 rounded-full h-3">
                    <div class="bg-gradient-to-r from-green-400 via-yellow-400 to-red-500 h-3 rounded-full" style="width: {{ $disk_used_percent }}%"></div>
                </div>
                <div class="flex justify-between mt-2 text-sm">
                    <span class="text-dark-400">Usado: {{ $disk_total }}</span>
                    <span class="text-dark-400">Libre: {{ $disk_free }}</span>
                </div>
            </div>

            <!-- Load Average -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg" x-data="serverMonitor()">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Load Average</p>
                        <p class="text-2xl font-bold text-white">
                            <span class="text-green-400" x-text="loadAvg['1min']"></span>
                            <span class="text-dark-500">/</span>
                            <span class="text-yellow-400" x-text="loadAvg['5min']"></span>
                            <span class="text-dark-500">/</span>
                            <span class="text-red-400" x-text="loadAvg['15min']"></span>
                        </p>
                    </div>
                </div>
                <p class="text-xs text-dark-500">Uptime: <span x-text="uptime"></span></p>
            </div>

        </div>

        <!-- Docker Containers -->
        <div class="mt-8 bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg" x-data="serverMonitor()" x-init="startMonitoring()">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-teal-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Contenedores Docker</h2>
                    <p class="text-dark-400 text-sm">Contenedores en ejecución</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-dark-400 border-b border-dark-700">
                            <th class="text-left py-3 px-2">ID</th>
                            <th class="text-left py-3 px-2">Imagen</th>
                            <th class="text-left py-3 px-2">Estado</th>
                            <th class="text-left py-3 px-2">Puertos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="container in dockerContainers" :key="container.id">
                            <tr class="border-b border-dark-800 hover:bg-dark-800 transition">
                                <td class="py-2 px-2 text-blue-400 font-mono" x-text="container.id"></td>
                                <td class="py-2 px-2 text-dark-300" x-text="container.image"></td>
                                <td class="py-2 px-2">
                                    <span class="px-2 py-1 rounded text-xs"
                                          :class="container.status.includes('Up') ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                          x-text="container.status"></span>
                                </td>
                                <td class="py-2 px-2 text-dark-400 text-xs" x-text="container.ports || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Procesos en Tiempo Real -->
        <div class="mt-8 bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg" x-data="serverMonitor()" x-init="startMonitoring()">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-pink-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Procesos en Tiempo Real</h2>
                        <p class="text-dark-400 text-sm">Actualizando cada 3 segundos</p>
                    </div>
                </div>
                <button @click="refreshProcesses()" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualizar
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-dark-400 border-b border-dark-700">
                            <th class="text-left py-3 px-2">USER</th>
                            <th class="text-left py-3 px-2">PID</th>
                            <th class="text-left py-3 px-2">%CPU</th>
                            <th class="text-left py-3 px-2">%MEM</th>
                            <th class="text-left py-3 px-2">VSZ</th>
                            <th class="text-left py-3 px-2">STAT</th>
                            <th class="text-left py-3 px-2">TIME</th>
                            <th class="text-left py-3 px-2">COMMAND</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="proc in processes" :key="proc.pid">
                            <tr class="border-b border-dark-800 hover:bg-dark-800 transition">
                                <td class="py-2 px-2 text-blue-400" x-text="proc.user"></td>
                                <td class="py-2 px-2 text-white" x-text="proc.pid"></td>
                                <td class="py-2 px-2" :class="proc.cpu > 50 ? 'text-red-400' : (proc.cpu > 20 ? 'text-yellow-400' : 'text-green-400')" x-text="proc.cpu + '%'"></td>
                                <td class="py-2 px-2" :class="proc.mem > 50 ? 'text-red-400' : (proc.mem > 20 ? 'text-yellow-400' : 'text-green-400')" x-text="proc.mem + '%'"></td>
                                <td class="py-2 px-2 text-dark-400" x-text="proc.vsz"></td>
                                <td class="py-2 px-2">
                                    <span class="px-2 py-1 rounded text-xs" 
                                          :class="proc.stat === 'R' ? 'bg-green-500/20 text-green-400' : (proc.stat === 'S' ? 'bg-blue-500/20 text-blue-400' : 'bg-yellow-500/20 text-yellow-400')"
                                          x-text="proc.stat"></span>
                                </td>
                                <td class="py-2 px-2 text-dark-400" x-text="proc.time"></td>
                                <td class="py-2 px-2 text-dark-300 font-mono text-xs truncate max-w-xs" x-text="proc.command"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center text-dark-500 text-sm">
            <p>Generado con Laravel, Tailwind CSS & Alpine.js</p>
        </div>
    </div>

    <script>
    function serverMonitor() {
        return {
            processes: @json($processes),
            loadAvg: @json($load_avg),
            uptime: '{{ $uptime }}',
            dockerContainers: @json($docker_containers),
            interval: null,
            
            startMonitoring() {
                this.interval = setInterval(() => {
                    this.refreshProcesses();
                }, 3000);
            },
            
            async refreshProcesses() {
                try {
                    const response = await fetch('/server/processes');
                    const data = await response.json();
                    this.processes = data.processes;
                    this.loadAvg = data.load_avg;
                    this.uptime = data.uptime;
                    this.dockerContainers = data.docker_containers;
                } catch (error) {
                    console.error('Error fetching processes:', error);
                }
            }
        }
    }
    </script>
</body>
</html>