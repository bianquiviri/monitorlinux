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

    public function testConnection(Server $server): bool
    {
        if ($server->is_local) return true;
        try {
            $ssh = $this->connect($server);
            return $ssh !== null;
        } catch (\Exception $e) {
            return false;
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
