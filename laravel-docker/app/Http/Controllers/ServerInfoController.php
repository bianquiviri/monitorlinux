<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ServerInfoController extends Controller
{
    public function index()
    {
        $data = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'memory_limit' => ini_get('memory_limit'),
            'disk_total' => $this->formatBytes(disk_total_space('/')),
            'disk_free' => $this->formatBytes(disk_free_space('/')),
            'disk_used_percent' => round(100 - (disk_free_space('/') / disk_total_space('/') * 100), 1),
            'os_name' => PHP_OS,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'current_time' => now()->format('Y-m-d H:i:s'),
            'processes' => $this->getProcesses(),
            'load_avg' => $this->getLoadAverage(),
            'uptime' => $this->getUptime(),
            'docker_containers' => $this->getDockerContainers(),
        ];

        return view('server.info', $data);
    }

    public function processes()
    {
        return response()->json([
            'processes' => $this->getProcesses(),
            'load_avg' => $this->getLoadAverage(),
            'uptime' => $this->getUptime(),
            'docker_containers' => $this->getDockerContainers(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function getProcesses()
    {
        $output = [];
        exec('ps aux --no-headers', $output);
        
        $processes = [];
        foreach (array_slice($output, 0, 20) as $line) {
            $parts = preg_split('/\s+/', $line);
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

    private function getLoadAverage()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => round($load[0], 2),
                '5min' => round($load[1], 2),
                '15min' => round($load[2], 2),
            ];
        }
        return ['1min' => 0, '5min' => 0, '15min' => 0];
    }

    private function getUptime()
    {
        $output = [];
        exec('uptime -s', $output);
        return $output[0] ?? 'N/A';
    }

    private function getDockerContainers()
    {
        $command = 'docker ps --no-trunc --format "{{json .}}" 2>/dev/null';
        $output = shell_exec($command);
        
        $containers = [];
        if (empty($output)) return $containers;
        
        $lines = explode("\n", trim($output));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $data = json_decode($line, true);
            if ($data) {
                $containers[] = [
                    'id' => substr($data['ID'] ?? '', 0, 12),
                    'image' => $data['Image'] ?? '',
                    'status' => $data['Status'] ?? '',
                    'ports' => $data['Ports'] ?? '',
                ];
            }
        }
        
        return $containers;
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}