<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function test_user_can_login_and_see_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->waitFor('.glass', 10)
                ->type('email', 'bianquiviri@gmail.com')
                ->type('password', '!N1k00905')
                ->press('Entrar al Dashboard')
                ->waitForLocation('/server')
                ->assertPathIs('/server')
                ->assertSee('Panel de Control')
                ->assertSee('Selecciona un servidor');
        });
    }
}
