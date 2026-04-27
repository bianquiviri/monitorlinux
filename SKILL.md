Genera un proyecto de Laravel 11 que incluya: 
1. Configuración de Docker (Dockerfile y docker-compose.yml) usando PHP 8.3-fpm y Nginx.
2. Una ruta principal '/' que apunte a un controlador llamado ServerInfoController.
3. El controlador debe obtener: versión de PHP, sistema operativo, carga de CPU (sys_getloadavg) y memoria libre.
4. Una vista en Blade usando Tailwind CSS (vía CDN para simplificar) que muestre estos datos en cards elegantes con diseño oscuro (dark mode).
5. Dame los comandos necesarios para levantar el contenedor y ver el resultado.Aquí tienes un proyecto de Laravel 11 con la configuración de Docker, el controlador ServerInfoController, y la vista en Blade utilizando Tailwind CSS para mostrar la información del servidor.