<?php

namespace Tests\Unit;

use App\Models\Server;
use App\Services\SSHService;
use Tests\TestCase;

class SSHServiceTest extends TestCase
{
    public function test_local_execution_uses_shell_exec()
    {
        $server = new Server(['is_local' => true]);
        $service = new SSHService();
        
        $output = $service->execute($server, 'echo "hello"');
        $this->assertEquals("hello\n", $output);
    }
}
