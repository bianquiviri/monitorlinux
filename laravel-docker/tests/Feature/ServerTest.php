<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_authenticated_user_can_view_info_page()
    {
        Server::create(['name' => 'Local', 'is_local' => true]);
        
        $response = $this->actingAs($this->user)->get('/server');
        $response->assertStatus(200);
    }

    public function test_user_can_add_new_server()
    {
        $response = $this->actingAs($this->user)->postJson('/server/add', [
            'name' => 'Remote Node',
            'ip' => '1.2.3.4',
            'ssh_user' => 'root',
            'ssh_password' => 'secret123',
            'ssh_port' => 22
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('servers', ['name' => 'Remote Node']);
        
        $server = Server::where('name', 'Remote Node')->first();
        $this->assertNotEquals('secret123', $server->ssh_password); // Should be encrypted
    }

    public function test_cannot_delete_local_server()
    {
        $localServer = Server::create(['name' => 'Local', 'is_local' => true]);
        
        $response = $this->actingAs($this->user)->delete("/server/{$localServer->id}");
        $response->assertStatus(403);
    }
}
