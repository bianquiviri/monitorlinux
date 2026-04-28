<?php

namespace App\Services;

use phpseclib3\Net\SSH2;
use App\Models\Server;

class SSHService
{
    public function execute(Server $server, $command)
    {
        if ($server->is_local) {
            return shell_exec($command);
        }

        try {
            $ssh = $this->connect($server);
            if (!$ssh) return null;
            return $ssh->exec($command);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SSH Error for {$server->name}: " . $e->getMessage());
            return null;
        }
    }

    public function testConnection(Server $server): array
    {
        if ($server->is_local) return ['success' => true, 'message' => 'Conexión local exitosa'];
        try {
            $ssh = $this->connect($server);
            if ($ssh !== null) {
                return ['success' => true, 'message' => 'Conexión SSH exitosa'];
            }
            return ['success' => false, 'message' => 'No se pudo autenticar. Verifique: usuario, contraseña/clave SSH, y que el servidor acepte conexiones en el puerto ' . $server->ssh_port];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    public function getConnectionError(Server $server): string
    {
        if ($server->is_local) return '';
        try {
            $ssh = new \phpseclib3\Net\SSH2($server->ip, (int)$server->ssh_port);
            $log = $ssh->getLog();
            return is_array($log) ? implode('; ', array_slice($log, -5)) : 'Sin log disponible';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    protected function connect(Server $server)
    {
        $ssh = new SSH2($server->ip, (int)$server->ssh_port);
        
        if (!empty($server->ssh_key)) {
            $key = \phpseclib3\Crypt\PublicKeyLoader::load($server->ssh_key);
            if ($ssh->login($server->ssh_user, $key)) {
                return $ssh;
            }
        }

        if (!empty($server->ssh_password)) {
            $password = \Illuminate\Support\Facades\Crypt::decryptString($server->ssh_password);
            if ($ssh->login($server->ssh_user, $password)) {
                return $ssh;
            }
        }

        return null;
    }
}
