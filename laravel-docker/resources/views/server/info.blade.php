<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#3b82f6">
    <link rel="apple-touch-icon" href="/img/icon-192.png">
    <title>MonitorLinux - Server Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
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
    <div class="container mx-auto px-4 py-8 max-w-7xl" x-data="serverMonitor()" x-init="interval = setInterval(() => refreshProcesses(), 5000)">
        
        <!-- Connection Status Banner -->
        <div x-show="connectionError" x-cloak class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-2xl flex items-center gap-3 text-red-400 font-bold animate-pulse">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Error de conexión: No se pudo establecer contacto con <span x-text="selectedServerName"></span></span>
        </div>

        <div x-show="!connectionError" x-cloak x-transition class="mb-6 p-4 bg-green-500/20 border border-green-500/30 rounded-2xl flex items-center gap-3 text-green-400 font-bold text-sm">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-ping"></div>
            <span>Conectado a <span x-text="selectedServerName"></span> vía SSH</span>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-start mb-12">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('server.list') }}" class="p-2 hover:bg-white/5 rounded-xl text-dark-500 transition-all" title="Volver al Listado">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 17l-5-5m0 0l5-5m-5 5h12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <h1 class="text-5xl font-extrabold text-white tracking-tight">
                        Monitor<span class="text-blue-500">Linux</span>
                    </h1>
                </div>
                <p class="text-dark-400">Información en tiempo real del sistema</p>
                <p class="text-sm text-dark-500 mt-2">{{ $current_time }}</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative" x-data="{ openSelector: false }">
                    <button @click="openSelector = !openSelector" class="bg-dark-800 text-dark-300 px-4 py-2 rounded-xl border border-dark-700 flex items-center gap-2 hover:bg-dark-700 transition-all">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/></svg>
                        <span x-text="selectedServerName">Local Server</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSelector" @click.away="openSelector = false" class="absolute right-0 mt-2 w-48 glass rounded-xl shadow-2xl z-50 overflow-hidden" x-cloak>
                        <template x-for="server in servers" :key="server.id">
                            <button @click="selectServer(server); openSelector = false" 
                                    class="w-full text-left px-4 py-3 text-sm hover:bg-blue-600/20 transition-colors flex items-center justify-between"
                                    :class="selectedServerId === server.id ? 'bg-blue-600/30 text-white font-bold' : ''">
                                <span x-text="server.name"></span>
                                <span class="w-2 h-2 rounded-full" :class="server.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></span>
                            </button>
                        </template>
                        <div class="border-t border-white/5 bg-dark-950">
                            <button @click="isEditing = false; newServer = { id: null, name: '', ip: '', ssh_user: '', ssh_password: '', ssh_port: 22 }; openModal = true; openSelector = false" 
                                    class="w-full text-left px-4 py-3 text-xs text-blue-400 hover:bg-blue-600/10 font-bold">
                                + Añadir Servidor
                            </button>
                            <button @click="openManageModal = true; openSelector = false" 
                                    class="w-full text-left px-4 py-3 text-xs text-dark-400 hover:bg-white/5 font-bold">
                                ⚙️ Gestionar Servidores
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Gestionar Servidores -->
                <div x-show="openManageModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-cloak>
                    <div class="glass w-full max-w-2xl p-8 rounded-3xl shadow-2xl border border-white/10" @click.away="openManageModal = false">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold">Gestionar Servidores</h3>
                            <button @click="openManageModal = false" class="text-dark-500 hover:text-white">✕</button>
                        </div>
                        <div class="space-y-3 max-h-[50vh] overflow-y-auto pr-2">
                            <template x-for="server in servers" :key="server.id">
                                <div class="flex items-center justify-between bg-dark-950 p-4 rounded-2xl border border-dark-800">
                                    <div>
                                        <p class="font-bold text-white" x-text="server.name"></p>
                                        <p class="text-xs text-dark-500" x-text="server.ip || 'Local'"></p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="editServer(server)" class="p-2 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="deleteServer(server.id)" 
                                                class="p-2 hover:bg-red-500/20 text-red-400 rounded-lg transition-all"
                                                :disabled="server.is_local"
                                                :class="server.is_local ? 'opacity-20 cursor-not-allowed' : ''">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Añadir/Editar Servidor -->
                <div x-show="openModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm" x-cloak>
                    <div class="glass w-full max-w-lg p-8 rounded-3xl shadow-2xl border border-white/10" @click.away="openModal = false">
                        <h3 class="text-2xl font-bold mb-6" x-text="isEditing ? 'Editar Servidor' : 'Añadir Nuevo Servidor'"></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-dark-500 uppercase mb-2">Nombre del Servidor</label>
                                <input type="text" x-model="newServer.name" class="w-full bg-dark-950 border border-dark-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-all" placeholder="Ej: Producción AWS">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-dark-500 uppercase mb-2">IP o Hostname</label>
                                <input type="text" x-model="newServer.ip" class="w-full bg-dark-950 border border-dark-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-all" placeholder="Ej: 192.168.1.10">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-dark-500 uppercase mb-2">Puerto SSH</label>
                                <input type="number" x-model="newServer.ssh_port" class="w-full bg-dark-950 border border-dark-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-all" placeholder="22">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-dark-500 uppercase mb-2">Usuario SSH</label>
                                <input type="text" x-model="newServer.ssh_user" class="w-full bg-dark-950 border border-dark-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-all" placeholder="root">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-dark-500 uppercase mb-2">Contraseña SSH</label>
                                <input type="password" x-model="newServer.ssh_password" class="w-full bg-dark-950 border border-dark-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-all" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="flex gap-4 mt-8">
                            <button @click="openModal = false" class="flex-1 px-6 py-3 rounded-xl border border-dark-800 text-dark-400 hover:bg-white/5 transition-all">Cancelar</button>
                            <button @click="addServer" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all">Guardar</button>
                        </div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-dark-800 hover:bg-red-500/20 text-dark-400 hover:text-red-400 px-4 py-2 rounded-xl transition-all border border-dark-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span class="hidden md:inline">Cerrar Sesión</span>
                    </button>
                </form>
            </div>
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
                        <p class="text-2xl font-bold text-white" x-text="phpVersion">{{ $php_version }}</p>
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
                        <p class="text-2xl font-bold text-white" x-text="'v' + laravelVersion">v{{ $laravel_version }}</p>
                    </div>
                </div>
            </div>

            <!-- Estado de Servicios -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    Estado de Servicios
                </h3>
                <div class="space-y-3">
                    <template x-for="service in servicesStatus" :key="service.name">
                        <div class="flex justify-between items-center">
                            <span class="text-dark-300 text-sm" x-text="service.name"></span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                  :class="service.online ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                  x-text="service.online ? 'Online' : 'Offline'"></span>
                        </div>
                    </template>
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
                        <p class="text-lg font-bold text-white" x-text="osName">{{ $os_name }}</p>
                        <p class="text-dark-400 text-sm">Tiempo de Actividad</p>
                        <p class="text-sm font-bold text-white truncate" x-text="uptime">{{ $uptime }}</p>
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
                        <p class="text-2xl font-bold text-white" x-text="memoryUsage">{{ $memory_usage }}</p>
                    </div>
                </div>
                <div class="w-full bg-dark-800 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-500" 
                         :class="swapUsage.percent > 50 ? 'bg-red-500' : 'bg-purple-500'"
                         :style="'width: ' + swapUsage.percent + '%'"></div>
                </div>
                <div class="flex justify-between mt-2">
                    <p class="text-[10px] text-dark-500">Swap: <span class="text-purple-400" x-text="swapUsage.used + ' / ' + swapUsage.total"></span></p>
                    <p class="text-[10px] text-dark-500">Límite: <span x-text="memoryLimit">{{ $memory_limit }}</span></p>
                </div>
            </div>

            <!-- Espacio en Disco -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg md:col-span-1 lg:col-span-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Espacio en Disco</p>
                        <p class="text-2xl font-bold text-white" x-text="diskFree + ' Libres'">{{ $disk_free }} Libres</p>
                    </div>
                </div>
                <div class="w-full bg-dark-800 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-500" 
                         :class="diskUsedPercent > 80 ? 'bg-orange-500' : (diskUsedPercent > 90 ? 'bg-red-500' : 'bg-emerald-500')"
                         :style="'width: ' + diskUsedPercent + '%'"></div>
                </div>
                <div class="flex justify-between mt-2">
                    <p class="text-[10px] text-dark-500">Total: <span class="text-emerald-400" x-text="diskTotal">{{ $disk_total }}</span></p>
                    <p class="text-[10px] text-dark-500">Uso: <span class="text-emerald-400" x-text="diskUsedPercent + '%'">{{ $disk_used_percent }}%</span></p>
                </div>
            </div>

            <!-- Base de Datos -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Base de Datos</p>
                        <p class="text-2xl font-bold text-white" x-text="dbStats.size"></p>
                    </div>
                </div>
                <p class="text-xs text-dark-500">Conexiones: <span class="text-emerald-400" x-text="dbStats.connections"></span></p>
            </div>

            <!-- Network Usage -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg lg:col-span-2">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Tráfico de Red</p>
                        <div class="flex gap-6 mt-1">
                            <template x-for="iface in networkStats" :key="iface.interface">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-bold text-dark-500 uppercase" x-text="iface.interface"></span>
                                    <span class="text-sm text-green-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        <span x-text="iface.rx"></span>
                                    </span>
                                    <span class="text-sm text-blue-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        <span x-text="iface.tx"></span>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load Average -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">Load Average</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div class="bg-dark-950 p-2 rounded border border-dark-800 text-center">
                        <p class="text-[10px] text-dark-500 uppercase">1 min</p>
                        <p class="text-sm font-bold" :class="loadAvg['1min'] > 1 ? 'text-orange-400' : 'text-emerald-400'" x-text="loadAvg['1min']"></p>
                    </div>
                    <div class="bg-dark-950 p-2 rounded border border-dark-800 text-center">
                        <p class="text-[10px] text-dark-500 uppercase">5 min</p>
                        <p class="text-sm font-bold" :class="loadAvg['5min'] > 1 ? 'text-orange-400' : 'text-emerald-400'" x-text="loadAvg['5min']"></p>
                    </div>
                    <div class="bg-dark-950 p-2 rounded border border-dark-800 text-center">
                        <p class="text-[10px] text-dark-500 uppercase">15 min</p>
                        <p class="text-sm font-bold" :class="loadAvg['15min'] > 1 ? 'text-orange-400' : 'text-emerald-400'" x-text="loadAvg['15min']"></p>
                    </div>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            
            <!-- Docker Containers -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-teal-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Contenedores Docker</h2>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-dark-400 border-b border-dark-700">
                                <th class="text-left py-3 px-2">Nombre</th>
                                <th class="text-left py-3 px-2">Imagen</th>
                                <th class="text-left py-3 px-2">Estado</th>
                                <th class="text-right py-3 px-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="container in dockerContainers" :key="container.id">
                                <tr class="border-b border-dark-800 hover:bg-dark-800 transition">
                                    <td class="py-2 px-2">
                                        <div class="text-blue-400 font-bold" x-text="container.name"></div>
                                        <div class="text-[10px] text-dark-500" x-text="container.id"></div>
                                    </td>
                                    <td class="py-2 px-2 text-dark-300 truncate max-w-[150px]" x-text="container.image"></td>
                                    <td class="py-2 px-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                              :class="container.status.includes('Up') ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                              x-text="container.status"></span>
                                    </td>
                                    <td class="py-2 px-2 text-right">
                                        <div class="flex justify-end gap-1">
                                            <button @click="manageContainer(container.id, 'start')" 
                                                    class="p-1 hover:bg-green-500/20 text-green-400 rounded transition" title="Start"
                                                    x-show="!container.status.includes('Up')">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.5 3.5a.5.5 0 01.5-.5h10a.5.5 0 01.5.5v13a.5.5 0 01-.5.5h-10a.5.5 0 01-.5-.5v-13z"/></svg>
                                            </button>
                                            <button @click="manageContainer(container.id, 'stop')" 
                                                    class="p-1 hover:bg-red-500/20 text-red-400 rounded transition" title="Stop"
                                                    x-show="container.status.includes('Up')">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.27 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h11.46a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75H4.27z"/></svg>
                                            </button>
                                            <button @click="manageContainer(container.id, 'restart')" 
                                                    class="p-1 hover:bg-blue-500/20 text-blue-400 rounded transition" title="Restart">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Últimos Logs de Laravel -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Logs de la Aplicación</h2>
                    </div>
                </div>
                <div class="overflow-x-auto h-64">
                    <table class="w-full text-[10px] text-left">
                        <thead>
                            <tr class="text-dark-400 border-b border-dark-700">
                                <th class="py-2 px-2">Fecha</th>
                                <th class="py-2 px-2">Nivel</th>
                                <th class="py-2 px-2">Mensaje</th>
                            </tr>
                        </thead>
                        <tbody class="font-mono">
                            <template x-for="log in recentLogs" :key="log.date + log.message">
                                <tr class="border-b border-dark-800 hover:bg-dark-800 transition">
                                    <td class="py-2 px-2 text-dark-500 whitespace-nowrap" x-text="log.date"></td>
                                    <td class="py-2 px-2">
                                        <span class="px-2 py-0.5 rounded font-bold uppercase"
                                              :class="{
                                                  'bg-red-500/20 text-red-400': log.level === 'ERROR' || log.level === 'CRITICAL' || log.level === 'ALERT' || log.level === 'EMERGENCY',
                                                  'bg-yellow-500/20 text-yellow-400': log.level === 'WARNING',
                                                  'bg-blue-500/20 text-blue-400': log.level === 'INFO',
                                                  'bg-dark-700 text-dark-400': log.level === 'DEBUG'
                                              }"
                                              x-text="log.level"></span>
                                    </td>
                                    <td class="py-2 px-2 text-dark-300 truncate max-w-[200px]" x-text="log.message" :title="log.message"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Últimos Logs del Servidor (Supervisor) -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-gray-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Logs del Servidor</h2>
                    </div>
                </div>
                <div class="bg-dark-950 rounded-lg p-4 font-mono text-[10px] text-dark-500 h-64 overflow-y-auto space-y-1">
                    <template x-for="log in serverLogs" :key="log">
                        <div class="border-l-2 border-dark-800 pl-2 py-0.5 hover:border-blue-500 transition-colors" 
                             :class="log.includes('error') || log.includes('WARN') ? 'text-red-400 border-red-500' : ''"
                             x-text="log"></div>
                    </template>
                </div>
            </div>

        </div>

        <!-- Procesos y Seguridad -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
            
            <!-- Procesos en Tiempo Real -->
            <div class="lg:col-span-2 bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-pink-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-white">Procesos Top CPU</h2>
                    </div>
                    <button @click="refreshProcesses()" class="p-2 hover:bg-dark-800 rounded-full transition-colors text-pink-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-dark-500 border-b border-dark-800">
                                <th class="text-left py-2 px-2">PID</th>
                                <th class="text-left py-2 px-2">%CPU</th>
                                <th class="text-left py-2 px-2">%MEM</th>
                                <th class="text-left py-2 px-2">COMANDO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="proc in processes.slice(0,10)" :key="proc.pid">
                                <tr class="border-b border-dark-800/50 hover:bg-dark-800/30 transition">
                                    <td class="py-2 px-2 text-white font-mono" x-text="proc.pid"></td>
                                    <td class="py-2 px-2 font-bold" :class="proc.cpu > 10 ? 'text-orange-400' : 'text-green-400'" x-text="proc.cpu + '%'"></td>
                                    <td class="py-2 px-2 text-dark-300" x-text="proc.mem + '%'"></td>
                                    <td class="py-2 px-2 text-dark-400 font-mono text-[10px] truncate max-w-[200px]" x-text="proc.command"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Seguridad / Usuarios -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-indigo-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 21a9.963 9.963 0 006.454-2.312 9.99 9.99 0 002.106-2.106M12 7V3m0 4h.01M12 7a4 4 0 100 8 4 4 0 000-8z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Seguridad</h2>
                </div>
                <div class="space-y-6">
                    <div>
                        <p class="text-dark-500 text-xs font-bold uppercase tracking-wider mb-3">Usuarios Logueados</p>
                        <div class="space-y-2">
                            <template x-for="user in loggedUsers" :key="user">
                                <div class="flex items-center gap-2 bg-dark-950 p-2 rounded border border-dark-800">
                                    <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
                                    <span class="text-xs text-indigo-300 font-mono" x-text="user"></span>
                                </div>
                            </template>
                            <template x-if="loggedUsers.length === 0">
                                <p class="text-dark-600 text-xs italic">No hay usuarios remotos detectados.</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Puertos Escuchando -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-rose-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Puertos Abiertos</h2>
                    </div>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto text-dark-400">
                    <template x-for="port in listeningPorts" :key="port.proto + port.address">
                        <div class="flex justify-between items-center bg-dark-950 p-2 rounded border border-dark-800">
                            <span class="text-[10px] font-bold text-rose-400 uppercase" x-text="port.proto"></span>
                            <span class="text-xs text-dark-300 font-mono" x-text="port.address"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Kernel Logs (dmesg) -->
            <div class="bg-dark-900 border border-dark-800 rounded-xl p-6 shadow-lg lg:col-span-2">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-pink-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Kernel Logs (dmesg)</h2>
                    </div>
                </div>
                <div class="bg-dark-950 rounded-lg p-4 font-mono text-[10px] text-dark-500 h-48 overflow-y-auto space-y-1">
                    <template x-for="log in dmesgLogs" :key="log">
                        <div class="border-l-2 border-dark-800 pl-2 py-0.5 hover:border-pink-500 transition-colors" x-text="log"></div>
                    </template>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="mt-12 text-center text-dark-500 text-sm">
            <p>MonitorLinux v2.0 - Powered by Laravel, Alpine.js & Tailwind CSS</p>
        </div>
    </div>

    <script>
    function serverMonitor() {
        return {
            processes: @json($processes),
            loadAvg: @json($load_avg),
            uptime: '{{ $uptime }}',
            dockerContainers: @json($docker_containers),
            networkStats: @json($network_stats),
            servicesStatus: @json($services_status),
            recentLogs: @json($recent_logs),
            dbStats: @json($db_stats),
            loggedUsers: @json($logged_users),
            swapUsage: @json($swap_usage),
            ioWait: '{{ $io_wait }}',
            listeningPorts: @json($listening_ports),
            dmesgLogs: @json($dmesg_logs),
            phpVersion: '{{ $php_version }}',
            laravelVersion: '{{ $laravel_version }}',
            memoryUsage: '{{ $memory_usage }}',
            memoryLimit: '{{ $memory_limit }}',
            diskTotal: '{{ $disk_total }}',
            diskFree: '{{ $disk_free }}',
            diskUsedPercent: {{ $disk_used_percent }},
            osName: '{{ $os_name }}',
            servers: @json($servers),
            selectedServerId: '{{ $selected_server_id_encrypted }}',
            selectedServerName: '{{ $selected_server_name }}',
            connectionError: false,
            openModal: false,
            openManageModal: false,
            isEditing: false,
            newServer: { id: null, name: '', ip: '', ssh_user: '', ssh_password: '', ssh_port: 22 },
            interval: null,
            
            selectServer(server) {
                this.selectedServerId = server.encrypted_id;
                this.selectedServerName = server.name;
                this.refreshProcesses();
            },

            async refreshProcesses() {
                try {
                    const response = await fetch(`{{ route('server.processes') }}?server_id=${this.selectedServerId}`);
                    const data = await response.json();
                    
                    if (!data || data.error || !data.os_name) {
                        this.connectionError = true;
                        return;
                    }
                    this.connectionError = false;
                    
                    this.processes = data.processes;
                    this.loadAvg = data.load_avg;
                    this.uptime = data.uptime;
                    this.dockerContainers = data.docker_containers;
                    this.networkStats = data.network_stats;
                    this.servicesStatus = data.services_status;
                    this.serverLogs = data.server_logs;
                    this.recentLogs = data.recent_logs;
                    this.dbStats = data.db_stats;
                    this.loggedUsers = data.logged_users;
                    this.swapUsage = data.swap_usage;
                    this.ioWait = data.io_wait;
                    this.listeningPorts = data.listening_ports;
                    this.dmesgLogs = data.dmesg_logs;
                    this.servers = data.servers;
                    this.phpVersion = data.php_version;
                    this.laravelVersion = data.laravel_version;
                    this.memoryUsage = data.memory_usage;
                    this.memoryLimit = data.memory_limit;
                    this.diskTotal = data.disk_total;
                    this.diskFree = data.disk_free;
                    this.diskUsedPercent = data.disk_used_percent;
                    this.osName = data.os_name;
                } catch (error) {
                    console.error('Error fetching processes:', error);
                }
            },

            async addServer() {
                if (!this.newServer.name) return;
                const url = this.isEditing ? `/server/${this.newServer.id}` : '{{ route('server.add') }}';
                const method = this.isEditing ? 'PUT' : 'POST';
                
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.newServer)
                    });
                    if (response.ok) {
                        this.openModal = false;
                        this.isEditing = false;
                        this.newServer = { id: null, name: '', ip: '', ssh_user: '', ssh_password: '', ssh_port: 22 };
                        this.refreshProcesses();
                    }
                } catch (error) {
                    console.error('Error saving server:', error);
                }
            },

            editServer(server) {
                this.isEditing = true;
                this.newServer = { ...server, ssh_password: '' };
                this.openModal = true;
                this.openManageModal = false;
            },

            async deleteServer(id) {
                if (!confirm('¿Estás seguro de eliminar este servidor?')) return;
                try {
                    const response = await fetch(`/server/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    if (response.ok) {
                        this.refreshProcesses();
                    }
                } catch (error) {
                    console.error('Error deleting server:', error);
                }
            },
            
            startMonitoring() {
                this.interval = setInterval(() => {
                    this.refreshProcesses();
                }, 3000);
            },
            

            async manageContainer(id, action) {
                if (!confirm(`¿Estás seguro de que quieres ${action} el contenedor?`)) return;
                
                try {
                    const response = await fetch('/server/container/manage', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ server_id: this.selectedServerId, id, action })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.refreshProcesses();
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    console.error('Error managing container:', error);
                }
            }
        }
    }
    </script>
</body>
</html>