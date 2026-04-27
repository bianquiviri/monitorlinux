# Migración a Vue 3 SPA con Inertia.js

Este documento detalla el plan técnico para transformar el proyecto actual (basado en Laravel Blade + Alpine.js) en una Single Page Application (SPA) robusta utilizando **Vue 3** e **Inertia.js**.

## Contexto y Objetivo
El objetivo es preparar la aplicación para escalar, permitiendo monitorizar una gran cantidad de servidores de manera eficiente y sin recargas de página. Inertia.js actúa como el "pegamento" perfecto entre Laravel y Vue, permitiéndonos crear una SPA sin tener que construir y mantener una API REST separada para todo el enrutamiento.

## > [!IMPORTANT] User Review Required
Por favor, revisa este plan. Una vez que lo apruebes, procederé con la ejecución. Ten en cuenta que esto requiere instalar dependencias de NPM e involucra compilar assets con Vite.
**¿Estás de acuerdo en utilizar Inertia.js para mantener la integración nativa con Laravel, o prefieres una arquitectura completamente separada (Laravel solo como API REST + Proyecto Vue aparte)?**
*Recomiendo fuertemente Inertia.js por velocidad de desarrollo y mantenimiento.*

## Cambios Propuestos

### 1. Configuración del Servidor (Laravel)
Instalaremos el adaptador de servidor de Inertia.
#### [NEW] `composer require inertiajs/inertia-laravel`
#### [NEW] `php artisan inertia:middleware`
Se añadirá y registrará el middleware de Inertia en la configuración de la aplicación (`bootstrap/app.php` en Laravel 11).

#### [NEW] `resources/views/app.blade.php`
Crearemos el archivo base principal donde se montará Vue. Reemplazará la necesidad de múltiples vistas Blade.

### 2. Configuración del Cliente (Vue + Vite)
Instalaremos el ecosistema frontend de Vue.
#### [NEW] `npm install vue @inertiajs/vue3 @vitejs/plugin-vue`
#### [MODIFY] `vite.config.js`
Añadiremos el plugin de Vue para que Vite compile los archivos `.vue`.
#### [MODIFY] `resources/js/app.js`
Punto de entrada de JavaScript que inicializa la aplicación Vue y monta Inertia.

### 3. Refactorización de Vistas a Componentes Vue
Convertiremos las vistas actuales en componentes de una sola página (SFC - Single File Components).

#### [NEW] `resources/js/Pages/Auth/Login.vue`
Migración de `auth/login.blade.php`. Mantendrá el mismo diseño con Tailwind CSS.
#### [NEW] `resources/js/Pages/Server/Index.vue`
Migración de `server/index.blade.php`. Convertiremos la lógica de Alpine.js (`serverManager`) a Composition API de Vue 3.
#### [NEW] `resources/js/Pages/Server/Info.vue`
Migración de `server/info.blade.php`. Convertiremos la lógica de monitoreo e intervalos a Composition API (`onMounted`, `onUnmounted`, `ref`).
#### [DELETE] Vistas Blade Antiguas
Eliminaremos `login.blade.php`, `index.blade.php` e `info.blade.php` una vez migradas.

### 4. Actualización de Controladores
Adaptaremos los controladores para que devuelvan respuestas de Inertia en lugar de vistas de Blade.

#### [MODIFY] `app/Http/Controllers/LoginController.php`
Cambiar `return view('auth.login')` a `return Inertia::render('Auth/Login')`.
#### [MODIFY] `app/Http/Controllers/ServerInfoController.php`
Cambiar `return view('server.index')` a `return Inertia::render('Server/Index')` y lo mismo para la vista `info`.

## Plan de Verificación

### Verificación Automática (Build)
- Ejecutaremos `npm run build` dentro del contenedor `app` para asegurar que los componentes Vue se compilan sin errores.

### Verificación Manual
- Validar el flujo de autenticación (Login).
- Verificar que la navegación entre la lista de servidores y la vista de monitor individual ocurre sin recargar la página del navegador.
- Comprobar que los gráficos/intervalos de carga de datos en `Info.vue` siguen funcionando correctamente vía polling AJAX a las rutas JSON de la aplicación.
