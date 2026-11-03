<?php

namespace Tests\Feature;

use Tests\TestCase;

class LaunchReadinessTest extends TestCase
{
    public function test_setup_blocks_account_access_and_writes(): void
    {
        config(['kwdc.launch_ready' => false]);

        $this->get('/login')->assertStatus(503)->assertSee('preparing to open');
        $this->post('/register', [])->assertStatus(503);
        $this->get('/up')->assertOk();
    }

    public function test_ready_portal_keeps_login_accessible(): void
    {
        config(['kwdc.launch_ready' => true]);

        $this->get('/login')->assertOk();
    }
}
