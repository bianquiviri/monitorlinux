<template>
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Connection Status Banner -->
        <div v-if="connectionError" class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-2xl flex items-center gap-3 text-red-400 font-bold animate-pulse">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Error de conexión: No se pudo establecer contacto con <span>{{ selectedServerName }}</span></span>
        </div>

        <div v-else class="mb-6 p-4 bg-green-500/20 border border-green-500/30 rounded-2xl flex items-center gap-3 text-green-400 font-bold text-sm">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-ping"></div>
            <span>Conectado a <span>{{ selectedServerName }}</span> vía SSH</span>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-start mb-12">
            <div>
                <div class="flex items-center gap-3">
                    <Link href="/server" class="p-2 hover:bg-white/5 rounded-xl text-dark-500 transition-all" title="Volver al Listado">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 17l-5-5m0 0l5-5m-5 5h12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </Link>
                    <h1 class="text-5xl font-extrabold text-white tracking-tight">
                        Monitor<span class="text-blue-500">Linux</span>
                    </h1>
                </div>
                <p class="text-slate-400 mt-2">Información en tiempo real del sistema</p>
                <p class="text-sm text-slate-500 mt-2">{{ currentTime }}</p>
            </div>
            <div class="flex items-center gap-4 relative">
                <button @click="openSelector = !openSelector" class="bg-dark-800 text-slate-300 px-4 py-2 rounded-xl border border-slate-700 flex items-center gap-2 hover:bg-slate-700 transition-all">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/></svg>
                    <span>{{ selectedServerName }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div v-if="openSelector" class="absolute top-12 right-0 mt-2 w-48 glass rounded-xl shadow-2xl z-50 overflow-hidden">
                    <button v-for="server in servers" :key="server.id" @click="selectServer(server)" 
                            class="w-full text-left px-4 py-3 text-sm hover:bg-blue-600/20 transition-colors flex items-center justify-between"
                            :class="selectedServerId === server.encrypted_id ? 'bg-blue-600/30 text-white font-bold' : ''">
                        <span>{{ server.name }}</span>
                        <span class="w-2 h-2 rounded-full" :class="server.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></span>
                    </button>
                    <div class="border-t border-white/5 bg-slate-950">
                        <Link href="/server" class="w-full text-left px-4 py-3 text-sm text-blue-400 font-bold hover:bg-blue-600/20 transition-colors flex items-center gap-2 block">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Gestionar Servidores
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col gap-6">
                <!-- System Info Card -->
                <div class="glass rounded-3xl p-8 border border-white/5 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-2 12H6V8h12v8z"/></svg>
                    </div>
                    <div class="flex items-center gap-4 mb-6 relative z-10">
                        <div class="p-3 bg-blue-500/20 rounded-2xl border border-blue-500/30">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold font-outfit tracking-wide">CPU & Sistema</h2>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 relative z-10">
                        <div class="bg-dark-900/50 rounded-2xl p-4 border border-white/5">
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Carga (Load)</p>
                            <p class="text-3xl font-black text-white font-outfit" v-if="typeof loadAvg === 'object' && loadAvg !== null">
                                {{ loadAvg['1min'] }} <span class="text-sm text-slate-500 font-bold">/ {{ loadAvg['5min'] }} / {{ loadAvg['15min'] }}</span>
                            </p>
                            <p class="text-3xl font-black text-white font-outfit" v-else>
                                {{ loadAvg }}
                            </p>
                        </div>
                        <div class="bg-dark-900/50 rounded-2xl p-4 border border-white/5">
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Uptime</p>
                            <p class="text-xl font-bold text-slate-300">{{ uptime }}</p>
                        </div>
                        <div class="bg-dark-900/50 rounded-2xl p-4 border border-white/5 col-span-2 md:col-span-1">
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Sistema Operativo</p>
                            <p class="text-sm font-semibold text-slate-400 break-words">{{ osName }}</p>
                        </div>
                    </div>
                </div>

                <!-- Memory & Disk Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Memory Card -->
                    <div class="glass rounded-3xl p-8 border border-white/5 shadow-2xl">
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-purple-500/20 rounded-2xl border border-purple-500/30">
                                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <h2 class="text-2xl font-bold font-outfit tracking-wide">Memoria RAM</h2>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2 mb-2">
                            <span class="text-4xl font-black text-white font-outfit">{{ memoryUsed }}</span>
                            <span class="text-slate-500 font-bold">/ {{ memoryTotal }}</span>
                        </div>
                        <div class="w-full bg-dark-900 rounded-full h-3 mb-2 border border-white/5 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-600 to-blue-500 h-3 rounded-full transition-all duration-1000 ease-out" :style="{ width: memoryUsedPercent + '%' }"></div>
                        </div>
                        <p class="text-right text-sm text-slate-400 font-bold">{{ memoryUsedPercent }}% Usado</p>
                    </div>

                    <!-- Disk Card -->
                    <div class="glass rounded-3xl p-8 border border-white/5 shadow-2xl">
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-emerald-500/20 rounded-2xl border border-emerald-500/30">
                                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                </div>
                                <h2 class="text-2xl font-bold font-outfit tracking-wide">Almacenamiento</h2>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2 mb-2">
                            <span class="text-4xl font-black text-white font-outfit">{{ diskFree }}</span>
                            <span class="text-slate-500 font-bold">Libres de {{ diskTotal }}</span>
                        </div>
                        <div class="w-full bg-dark-900 rounded-full h-3 mb-2 border border-white/5 overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-3 rounded-full transition-all duration-1000 ease-out" :style="{ width: diskUsedPercent + '%' }"></div>
                        </div>
                        <p class="text-right text-sm text-slate-400 font-bold">{{ diskUsedPercent }}% Usado</p>
                    </div>
                </div>

                <!-- Process List -->
                <div class="glass rounded-3xl p-8 border border-white/5 shadow-2xl">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="p-3 bg-orange-500/20 rounded-2xl border border-orange-500/30">
                            <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold font-outfit tracking-wide">Top Procesos Activos</h2>
                    </div>
                    
                    <div class="overflow-x-auto rounded-2xl border border-white/5">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-dark-900/80 text-slate-400 text-xs uppercase tracking-wider font-bold">
                                    <th class="p-4 rounded-tl-2xl">PID</th>
                                    <th class="p-4">Usuario</th>
                                    <th class="p-4">% CPU</th>
                                    <th class="p-4">% RAM</th>
                                    <th class="p-4 rounded-tr-2xl">Comando</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm">
                                <tr v-for="proc in processes" :key="proc.pid" class="hover:bg-white/5 transition-colors group">
                                    <td class="p-4 font-mono text-slate-400 group-hover:text-blue-400">{{ proc.pid }}</td>
                                    <td class="p-4 text-slate-300 font-medium">{{ proc.user }}</td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold" :class="parseFloat(proc.cpu) > 10 ? 'bg-red-500/20 text-red-400' : 'bg-slate-800 text-slate-300'">
                                            {{ proc.cpu }}%
                                        </span>
                                    </td>
                                    <td class="p-4 text-slate-300">{{ proc.mem }}%</td>
                                    <td class="p-4 font-mono text-emerald-400 text-xs truncate max-w-[200px]" :title="proc.command">{{ proc.command }}</td>
                                </tr>
                                <tr v-if="processes.length === 0">
                                    <td colspan="5" class="p-8 text-center text-slate-500">
                                        <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Cargando procesos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Docker Card -->
                <div class="glass rounded-3xl p-8 border border-white/5 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 opacity-5 pointer-events-none">
                        <svg class="w-40 h-40" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 13.62c-.17-.18-.38-.3-.6-.35-.22-.05-.45-.03-.66.05l-3.32 1.25c-.21.08-.39.22-.53.4-.13.18-.2.4-.2.62v2.48c0 .24.08.47.23.65.15.19.36.31.59.35l3.32.6c.22.04.45 0 .66-.1.21-.09.38-.24.5-.43.12-.19.17-.41.15-.63l-.36-3.37c-.02-.22-.12-.42-.28-.57zM2.83 14.52l3.32 1.25c.21.08.4.22.53.4.13.18.2.4.2.62v2.48c0 .24-.07.47-.22.65-.15.19-.36.31-.59.35l-3.32.6c-.22.04-.45 0-.66-.1-.21-.09-.38-.24-.5-.43-.12-.19-.17-.41-.15-.63l.36-3.37c.02-.22.12-.42.28-.57.17-.18.38-.3.6-.35.22-.05.45-.03.66.05zm8.3-4.52l3.32 1.25c.21.08.4.22.53.4.13.18.2.4.2.62v2.48c0 .24-.07.47-.22.65-.15.19-.36.31-.59.35l-3.32.6c-.22.04-.45 0-.66-.1-.21-.09-.38-.24-.5-.43-.12-.19-.17-.41-.15-.63l.36-3.37c.02-.22.12-.42.28-.57.17-.18.38-.3.6-.35.22-.05.45-.03.66.05zm0 8.04l3.32 1.25c.21.08.4.22.53.4.13.18.2.4.2.62v2.48c0 .24-.07.47-.22.65-.15.19-.36.31-.59.35l-3.32.6c-.22.04-.45 0-.66-.1-.21-.09-.38-.24-.5-.43-.12-.19-.17-.41-.15-.63l.36-3.37c.02-.22.12-.42.28-.57.17-.18.38-.3.6-.35.22-.05.45-.03.66.05zm-8.3-8.04l3.32 1.25c.21.08.4.22.53.4.13.18.2.4.2.62v2.48c0 .24-.07.47-.22.65-.15.19-.36.31-.59.35l-3.32.6c-.22.04-.45 0-.66-.1-.21-.09-.38-.24-.5-.43-.12-.19-.17-.41-.15-.63l.36-3.37c.02-.22.12-.42.28-.57.17-.18.38-.3.6-.35.22-.05.45-.03.66.05zm8.3-8.04l3.32 1.25c.21.08.4.22.53.4.13.18.2.4.2.62v2.48c0 .24-.07.47-.22.65-.15.19-.36.31-.59.35l-3.32.6c-.22.04-.45 0-.66-.1-.21-.09-.38-.24-.5-.43-.12-.19-.17-.41-.15-.63l.36-3.37c.02-.22.12-.42.28-.57.17-.18.38-.3.6-.35.22-.05.45-.03.66.05zm-8.3 0l3.32 1.25c.21.08.4.22.53.4.13.18.2.4.2.62v2.48c0 .24-.07.47-.22.65-.15.19-.36.31-.59.35l-3.32.6c-.22.04-.45 0-.66-.1-.21-.09-.38-.24-.5-.43-.12-.19-.17-.41-.15-.63l.36-3.37c.02-.22.12-.42.28-.57.17-.18.38-.3.6-.35.22-.05.45-.03.66.05z"/></svg>
                    </div>
                    <div class="flex items-center gap-4 mb-6 relative z-10">
                        <div class="p-3 bg-cyan-500/20 rounded-2xl border border-cyan-500/30">
                            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold font-outfit tracking-wide">Contenedores Docker</h2>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-white/5 relative z-10">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-dark-900/80 text-slate-400 text-xs uppercase tracking-wider font-bold">
                                    <th class="p-4 rounded-tl-2xl">Contenedor</th>
                                    <th class="p-4">Estado</th>
                                    <th class="p-4 rounded-tr-2xl">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm">
                                <tr v-for="container in dockerContainers" :key="container.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-2.5 w-2.5 relative">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="container.status.includes('Up') ? 'bg-green-400' : 'bg-red-400'"></span>
                                                <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="container.status.includes('Up') ? 'bg-green-500' : 'bg-red-500'"></span>
                                            </span>
                                            <div>
                                                <h4 class="font-bold text-white group-hover:text-cyan-400 transition-colors">{{ container.name }}</h4>
                                                <p class="text-xs text-slate-500 font-mono">{{ container.image }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-xs text-slate-400 font-medium bg-dark-950/50 px-2 py-1 rounded">{{ container.status }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex gap-2">
                                            <button v-if="container.status.includes('Up')" @click="manageContainer(container.id, 'stop')" class="text-xs font-bold px-3 py-1.5 bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition-colors">
                                                Detener
                                            </button>
                                            <button v-else @click="manageContainer(container.id, 'start')" class="text-xs font-bold px-3 py-1.5 bg-green-500/10 text-green-400 rounded-lg hover:bg-green-500/20 transition-colors">
                                                Iniciar
                                            </button>
                                            <button @click="manageContainer(container.id, 'restart')" class="text-xs font-bold px-3 py-1.5 bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700 transition-colors">
                                                Reiniciar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="dockerContainers.length === 0">
                                    <td colspan="3" class="p-6 text-center text-slate-500 bg-dark-900/30 border-dashed border border-white/5">
                                        No se detectaron contenedores activos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    current_time: String,
    processes: Array,
    load_avg: String,
    uptime: String,
    memory_used: String,
    memory_total: String,
    memory_used_percent: Number,
    disk_free: String,
    disk_total: String,
    disk_used_percent: Number,
    os_name: String,
    docker_containers: Array,
    servers: Array,
    selected_server_id_encrypted: String,
    selected_server_name: String,
});

const processes = ref(props.processes || []);
const loadAvg = ref(props.load_avg || '');
const uptime = ref(props.uptime || '');
const memoryUsed = ref(props.memory_used || '');
const memoryTotal = ref(props.memory_total || '');
const memoryUsedPercent = ref(props.memory_used_percent || 0);
const diskFree = ref(props.disk_free || '');
const diskTotal = ref(props.disk_total || '');
const diskUsedPercent = ref(props.disk_used_percent || 0);
const osName = ref(props.os_name || '');
const dockerContainers = ref(props.docker_containers || []);
const currentTime = ref(props.current_time || '');

const selectedServerId = ref(props.selected_server_id_encrypted);
const selectedServerName = ref(props.selected_server_name);
const connectionError = ref(false);
const openSelector = ref(false);

let intervalId = null;

const selectServer = (server) => {
    selectedServerId.value = server.encrypted_id;
    selectedServerName.value = server.name;
    openSelector.value = false;
    refreshProcesses();
};

const refreshProcesses = async () => {
    try {
        const response = await axios.get(`/server/processes?server_id=${selectedServerId.value}`);
        const data = response.data;
        
        if (!data || data.error || !data.os_name) {
            connectionError.value = true;
            return;
        }
        connectionError.value = false;
        
        processes.value = data.processes || [];
        loadAvg.value = data.load_avg || '';
        uptime.value = data.uptime || '';
        memoryUsed.value = data.memory_used || '';
        memoryTotal.value = data.memory_total || '';
        memoryUsedPercent.value = data.memory_used_percent || 0;
        diskFree.value = data.disk_free || '';
        diskTotal.value = data.disk_total || '';
        diskUsedPercent.value = data.disk_used_percent || 0;
        osName.value = data.os_name || '';
        dockerContainers.value = data.docker_containers || [];
    } catch (e) {
        connectionError.value = true;
        console.error("Error refreshing data:", e);
    }
};

const manageContainer = async (id, action) => {
    try {
        const response = await axios.post('/server/manage-container', {
            server_id: selectedServerId.value,
            id: id,
            action: action
        });
        
        if (response.data.success) {
            refreshProcesses();
        } else {
            alert('Error: ' + response.data.message);
        }
    } catch (e) {
        alert('Error al ejecutar la acción.');
        console.error(e);
    }
};

onMounted(() => {
    intervalId = setInterval(refreshProcesses, 5000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});
</script>

<style>
.glass {
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(12px);
}
</style>
