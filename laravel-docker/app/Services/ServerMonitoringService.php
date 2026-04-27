<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ServerMonitoringService
{
    protected $ssh;

    public function __construct(SSHService $ssh)
    {
        $this->ssh = $ssh;
    }

    public function getServerStats(?\App\Models\Server $server): array
    {
        if (!$server) {
            $server = \App\Models\Server::where('is_local', true)->first();
        }

        $isLocal = $server->is_local;

        return [
            'php_version' => $isLocal ? PHP_VERSION : 'N/A (Remote)',
            'laravel_version' => $isLocal ? app()->version() : 'N/A (Remote)',
            'memory_usage' => $this->getRemoteMemoryUsage($server),
            'memory_limit' => $isLocal ? ini_get('memory_limit') : 'N/A',
            'disk_total' => $this->getRemoteDiskTotal($server),
            'disk_free' => $this->getRemoteDiskFree($server),
            'disk_used_percent' => $this->getRemoteDiskPercent($server),
            'os_name' => $this->getDetailedOS($server),
            'server_software' => $isLocal ? ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') : 'N/A',
            'current_time' => now()->format('Y-m-d H:i:s'),
            'processes' => $this->getProcesses($server),
            'load_avg' => $this->getLoadAverage($server),
            'uptime' => $this->getUptime($server),
            'docker_containers' => $this->getDockerContainers($server),
            'network_stats' => $this->getNetworkStats($server),
            'services_status' => $this->getServicesStatus($server),
            'recent_logs' => $isLocal ? $this->getRecentLogs() : [],
            'server_logs' => $this->getServerLogs($server),
            'db_stats' => $isLocal ? $this->getDatabaseStats() : ['connections' => 'N/A', 'size' => 'N/A'],
            'logged_users' => $this->getLoggedUsers($server),
            'swap_usage' => $this->getSwapUsage($server),
            'io_wait' => $this->getIOWait($server),
            'listening_ports' => $this->getListeningPorts($server),
            'dmesg_logs' => $this->getDmesgLogs($server),
        ];
    }

    public function getProcesses(\App\Models\Server $server): array
    {
        $output_str = $this->ssh->execute($server, 'ps aux --no-headers | sort -rk 3 | head -n 20');
        $output = explode("\n", trim($output_str));
        
        $processes = [];
        foreach ($output as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 11) {
                $processes[] = [
                    'user' => $parts[0],
                    'pid' => $parts[1],
                    'cpu' => $parts[2],
                    'mem' => $parts[3],
                    'vsz' => $this->formatBytes((int)$parts[4] * 1024),
                    'stat' => $parts[7] ?? 'R',
                    'time' => $parts[9] ?? '00:00',
                    'command' => implode(' ', array_slice($parts, 10)),
                ];
            }
        }
        return $processes;
    }

    public function getLoadAverage(\App\Models\Server $server): array
    {
        $output = $this->ssh->execute($server, 'cat /proc/loadavg');
        $load = explode(' ', $output);
        return [
            '1min' => round((float)($load[0] ?? 0), 2),
            '5min' => round((float)($load[1] ?? 0), 2),
            '15min' => round((float)($load[2] ?? 0), 2),
        ];
    }

    public function getUptime(\App\Models\Server $server): string
    {
        return trim($this->ssh->execute($server, 'uptime -p') ?: 'N/A');
    }

    public function getDockerContainers(\App\Models\Server $server): array
    {
        $command = 'docker ps -a --no-trunc --format "{{json .}}" 2>/dev/null';
        $output = $this->ssh->execute($server, $command);
        
        $containers = [];
        if (empty($output)) return $containers;
        
        $lines = explode("\n", trim($output));
        foreach ($lines as $line) {
            $data = json_decode(trim($line), true);
            if ($data) {
                $containers[] = [
                    'id' => substr($data['ID'] ?? '', 0, 12),
                    'image' => $data['Image'] ?? '',
                    'status' => $data['Status'] ?? '',
                    'ports' => $data['Ports'] ?? '',
                    'name' => $data['Names'] ?? '',
                ];
            }
        }
        return $containers;
    }

    public function manageContainer(\App\Models\Server $server, string $id, string $action): bool
    {
        $allowedActions = ['start', 'stop', 'restart'];
        if (!in_array($action, $allowedActions)) return false;

        $safeAction = escapeshellarg($action);
        $safeId = escapeshellarg($id);
        $command = "docker {$safeAction} {$safeId} 2>&1";
        $this->ssh->execute($server, $command);
        return true;
    }

    public function getServerLogs(\App\Models\Server $server): array
    {
        if ($server->is_local) {
            $logPath = base_path('supervisord.log');
            if (!File::exists($logPath)) return [];
            $output_str = shell_exec("tail -n 20 $logPath");
        } else {
            $output_str = $this->ssh->execute($server, "tail -n 20 /var/log/syslog 2>/dev/null || tail -n 20 /var/log/messages");
        }
        
        return array_reverse(explode("\n", trim($output_str)));
    }

    private function getNetworkStats(\App\Models\Server $server): array
    {
        $stats = [];
        $data = $this->ssh->execute($server, 'cat /proc/net/dev');
        if (empty($data)) return $stats;

        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                $parts = preg_split('/\s+/', trim($line));
                $interface = str_replace(':', '', $parts[0]);
                if ($interface !== 'lo') {
                    $stats[] = [
                        'interface' => $interface,
                        'rx' => $this->formatBytes((int)$parts[1]),
                        'tx' => $this->formatBytes((int)$parts[9]),
                    ];
                }
            }
        }
        return $stats;
    }

    private function getServicesStatus(\App\Models\Server $server): array
    {
        $services = [
            ['name' => 'MySQL', 'host' => config('database.connections.mysql.host'), 'port' => 3306],
            ['name' => 'Nginx', 'host' => '127.0.0.1', 'port' => 80],
            ['name' => 'PHP-FPM', 'host' => '127.0.0.1', 'port' => 9000],
        ];

        $results = [];
        foreach ($services as $service) {
            $connection = @fsockopen($service['host'], $service['port'], $errno, $errstr, 0.5);
            $results[] = [
                'name' => $service['name'],
                'online' => is_resource($connection),
            ];
            if (is_resource($connection)) fclose($connection);
        }
        return $results;
    }

    private function getSwapUsage(\App\Models\Server $server): array
    {
        $free = $this->ssh->execute($server, 'free -m');
        $lines = explode("\n", $free);
        foreach ($lines as $line) {
            if (str_starts_with($line, 'Swap:')) {
                $parts = preg_split('/\s+/', $line);
                return [
                    'total' => $parts[1] . ' MB',
                    'used' => $parts[2] . ' MB',
                    'free' => $parts[3] . ' MB',
                    'percent' => $parts[1] > 0 ? round(($parts[2] / $parts[1]) * 100, 1) : 0,
                ];
            }
        }
        return ['total' => '0 MB', 'used' => '0 MB', 'free' => '0 MB', 'percent' => 0];
    }

    private function getIOWait(\App\Models\Server $server): string
    {
        $output = $this->ssh->execute($server, "top -bn1 | grep 'Cpu(s)' | awk '{print $10}'");
        return trim($output) ?: '0.0';
    }

    private function getListeningPorts(\App\Models\Server $server): array
    {
        $output_str = $this->ssh->execute($server, 'ss -tuln');
        $output = explode("\n", trim($output_str));
        $ports = [];
        foreach (array_slice($output, 1) as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 5) {
                $ports[] = [
                    'proto' => $parts[0],
                    'address' => $parts[4],
                ];
            }
        }
        return array_slice($ports, 0, 10);
    }

    private function getDmesgLogs(\App\Models\Server $server): array
    {
        $output_str = $this->ssh->execute($server, 'dmesg | tail -n 10');
        $output = explode("\n", trim($output_str));
        return array_reverse($output);
    }

    private function getRecentLogs(): array
    {
        $logPath = storage_path('logs/laravel.log');
        if (!File::exists($logPath)) return [];
        
        $output = [];
        exec("tail -n 20 $logPath", $output);
        $output = array_reverse($output);

        $parsedLogs = [];
        foreach ($output as $line) {
            if (preg_match('/^\[(?P<date>.*?)\]\s+(?P<env>\w+)\.(?P<level>\w+):\s+(?P<message>.*)/', $line, $matches)) {
                $parsedLogs[] = [
                    'date' => $matches['date'],
                    'level' => $matches['level'],
                    'message' => $matches['message'],
                ];
            } elseif (!empty($line)) {
                $parsedLogs[] = ['date' => '-', 'level' => 'DEBUG', 'message' => $line];
            }
        }
        return array_slice($parsedLogs, 0, 15);
    }

    private function getDatabaseStats(): array
    {
        try {
            $connections = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            $dbSize = DB::select("SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES");
            return [
                'connections' => $connections[0]->Value ?? 0,
                'size' => $this->formatBytes($dbSize[0]->size ?? 0),
            ];
        } catch (\Exception $e) {
            return ['connections' => 'N/A', 'size' => 'N/A'];
        }
    }

    private function getLoggedUsers(\App\Models\Server $server): array
    {
        $output = $this->ssh->execute($server, 'who');
        return explode("\n", trim($output));
    }

    private function getDetailedOS(\App\Models\Server $server): string
    {
        $output = $this->ssh->execute($server, 'cat /etc/os-release | grep PRETTY_NAME');
        if (preg_match('/PRETTY_NAME="([^"]+)"/', $output, $matches)) {
            return $matches[1];
        }
        return $this->ssh->execute($server, 'uname -sr');
    }

    private function getRemoteMemoryUsage(\App\Models\Server $server): string
    {
        if ($server->is_local) return $this->formatBytes(memory_get_usage(true));
        
        $output = $this->ssh->execute($server, "free -b | grep Mem | awk '{print $3}'");
        return $this->formatBytes((int)trim($output));
    }

    private function getRemoteDiskTotal(\App\Models\Server $server): string
    {
        $output = $this->ssh->execute($server, "df -B1 / | tail -n 1 | awk '{print $2}'");
        return $this->formatBytes((int)trim($output));
    }

    private function getRemoteDiskFree(\App\Models\Server $server): string
    {
        $output = $this->ssh->execute($server, "df -B1 / | tail -n 1 | awk '{print $4}'");
        return $this->formatBytes((int)trim($output));
    }

    private function getRemoteDiskPercent(\App\Models\Server $server): float
    {
        $output = $this->ssh->execute($server, "df / | tail -n 1 | awk '{print $5}' | sed 's/%//'");
        return (float)trim($output);
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = (float)max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min((int)$pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
