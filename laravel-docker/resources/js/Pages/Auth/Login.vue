<template>
    <div class="flex items-center justify-center min-h-screen p-4 bg-dark-950">
        <div class="w-full max-w-md">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-blue-600/20 mb-6 border border-blue-500/30">
                    <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white tracking-tight">MonitorLinux</h1>
                <p class="text-slate-400 mt-2">Acceso restringido al sistema de monitoreo</p>
            </div>

            <div class="glass rounded-3xl p-8 shadow-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Correo Electrónico</label>
                        <input type="email" v-model="form.email" required 
                               class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Contraseña</label>
                        <input type="password" v-model="form.password" required 
                               class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>

                    <div v-if="form.errors.email" class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm">
                        {{ form.errors.email }}
                    </div>

                    <button type="submit" :disabled="form.processing"
                            class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/20 transition-all active:scale-[0.98] disabled:opacity-50 flex items-center justify-center">
                        <span v-if="form.processing">Entrando...</span>
                        <span v-else>Entrar al Dashboard</span>
                    </button>
                </form>
            </div>

            <p class="text-center text-slate-600 text-sm mt-8">
                &copy; 2026 MonitorLinux v2.0 SPA
            </p>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login');
};
</script>

<style>
.glass {
    background: rgba(17, 17, 17, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
