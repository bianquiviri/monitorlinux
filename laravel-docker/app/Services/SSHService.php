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
            $ssh = new SSH2($server->ip, (int)$server->ssh_port);
            
            $password = $server->ssh_password ? \Illuminate\Support\Facades\Crypt::decryptString($server->ssh_password) : '';
            
            if (!$ssh->login($server->ssh_user, $password)) {
                return null;
            }

            return $ssh->exec($command);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SSH Error for {$server->name}: " . $e->getMessage());
            return null;
        }
    }
}
