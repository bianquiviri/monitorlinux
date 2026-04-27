<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#3b82f6">
    <link rel="apple-touch-icon" href="/img/icon-192.png">
    <title>Seleccionar Servidor - MonitorLinux</title>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen selection:bg-blue-500/30">
    
    <div class="container mx-auto px-4 py-16 max-w-6xl" x-data="serverManager()">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-6 text-center md:text-left">
            <div>
                <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white">
                    Panel de <span class="text-blue-500">Control</span>
                </h1>
                <p class="text-slate-400 mt-2 text-lg">Selecciona un servidor para comenzar el monitoreo</p>
            </div>
            <div class="flex gap-4">
                <button @click="openModal = true" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-blue-500/20 transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round"/></svg>
                    Añadir Servidor
                </button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-slate-800 hover:bg-red-500/20 text-slate-400 hover:text-red-400 px-6 py-3 rounded-2xl border border-slate-700 transition-all font-bold">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>

        <!-- Server Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="server in servers" :key="server.id">
                <div class="glass border border-slate-800 rounded-3xl p-6 hover:border-blue-500/50 transition-all group relative">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-500 group-hover:bg-blue-500 group-hover:text-white transition-all duration-300 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="flex gap-2">
                            <button @click="editServer(server)" class="p-2 hover:bg-blue-500/20 text-blue-400 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button @click="deleteServer(server.encrypted_id)" 
                                    class="p-2 hover:bg-red-500/20 text-red-400 rounded-xl transition-all opacity-0 group-hover:opacity-100"
                                    x-show="!server.is_local">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-1 group-hover:text-blue-400 transition-colors" x-text="server.name"></h3>
                    <p class="text-slate-500 text-sm mb-6" x-text="server.ip || 'Servidor Local (Host)'"></p>
                    
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full" :class="server.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400" x-text="server.status"></span>
                        </span>
                        <a :href="'/server/monitor/' + server.encrypted_id" class="text-blue-500 font-bold text-sm hover:underline flex items-center gap-1">
                            Monitorear
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5-5 5M6 7l5 5-5 5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                </div>
            </template>

            <!-- Add Card (Shortcut) -->
            <button @click="openModal = true" class="border-2 border-dashed border-slate-800 rounded-3xl p-6 hover:border-blue-500/50 hover:bg-blue-500/5 transition-all group flex flex-col items-center justify-center min-h-[220px]">
                <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-slate-500 group-hover:bg-blue-600 group-hover:text-white transition-all mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <span class="text-slate-400 group-hover:text-white font-bold transition-all">Añadir Nuevo Servidor</span>
            </button>
        </div>

        <!-- Modal Añadir/Editar -->
        <div x-show="openModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-sm" x-cloak x-transition>
            <div class="glass w-full max-w-lg p-10 rounded-[2.5rem] shadow-2xl border border-white/10" @click.away="openModal = false">
                <h3 class="text-3xl font-black mb-8 text-white" x-text="isEditing ? 'Editar Servidor' : 'Nuevo Servidor'"></h3>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nombre Identificador</label>
                        <input type="text" x-model="newServer.name" class="w-full bg-slate-900 border border-slate-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Ej: AWS Production">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">IP / Hostname</label>
                            <input type="text" x-model="newServer.ip" class="w-full bg-slate-900 border border-slate-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="192.168.1.100">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Puerto SSH</label>
                            <input type="number" x-model="newServer.ssh_port" class="w-full bg-slate-900 border border-slate-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="22">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Usuario SSH</label>
                            <input type="text" x-model="newServer.ssh_user" class="w-full bg-slate-900 border border-slate-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="root">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Contraseña SSH</label>
                            <input type="password" x-model="newServer.ssh_password" class="w-full bg-slate-900 border border-slate-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="••••••••">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Llave Privada SSH (Opcional - Más Seguro)</label>
                        <textarea x-model="newServer.ssh_key" class="w-full bg-slate-900 border border-slate-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-500 transition-all placeholder:text-slate-700 font-mono text-xs" rows="4" placeholder="-----BEGIN OPENSSH PRIVATE KEY-----"></textarea>
                    </div>

                    <div x-show="testStatus" class="p-4 rounded-xl text-sm font-bold" :class="testStatus === 'success' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'">
                        <span x-text="testStatus === 'success' ? '✓ Conexión exitosa' : '✗ Error de conexión'"></span>
                    </div>
                </div>

                <div class="flex flex-col gap-4 mt-10">
                    <button @click="testConnection" :disabled="testing" class="w-full px-6 py-4 rounded-2xl border border-blue-500/30 text-blue-400 hover:bg-blue-500/10 transition-all font-bold flex items-center justify-center gap-2">
                        <svg x-show="testing" class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="testing ? 'Probando...' : 'Probar Conexión'"></span>
                    </button>
                    <div class="flex gap-4">
                        <button @click="openModal = false" class="flex-1 px-6 py-4 rounded-2xl border border-slate-800 text-slate-400 hover:bg-slate-800 transition-all font-bold">
                            Cancelar
                        </button>
                        <button @click="saveServer" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white px-6 py-4 rounded-2xl font-black shadow-lg shadow-blue-500/20 transition-all active:scale-95">
                            <span x-text="isEditing ? 'Actualizar' : 'Crear Servidor'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function serverManager() {
        return {
            servers: @json($servers),
            openModal: false,
            isEditing: false,
            testing: false,
            testStatus: null,
            newServer: { id: null, name: '', ip: '', ssh_user: '', ssh_password: '', ssh_port: 22, ssh_key: '' },

            async testConnection() {
                this.testing = true;
                this.testStatus = null;
                try {
                    const response = await fetch('{{ route('server.test-connection') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.newServer)
                    });
                    const data = await response.json();
                    this.testStatus = data.success ? 'success' : 'error';
                } catch (error) {
                    this.testStatus = 'error';
                } finally {
                    this.testing = false;
                }
            },

            async saveServer() {
                if (!this.newServer.name) return;
                const isUpdate = this.isEditing;
                const url = isUpdate ? `/server/${this.newServer.encrypted_id}` : '{{ route('server.add') }}';
                const method = isUpdate ? 'PUT' : 'POST';
                
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
                        window.location.reload();
                    } else {
                        const err = await response.json();
                        alert(err.message || 'Error al guardar');
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            },

            editServer(server) {
                this.isEditing = true;
                this.newServer = { ...server, ssh_password: '' };
                this.openModal = true;
            },

            async deleteServer(id) {
                if (!confirm('¿Seguro que quieres eliminar este servidor?')) return;
                try {
                    const response = await fetch(`/server/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    if (response.ok) window.location.reload();
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }
    }
    </script>
</body>
</html>
