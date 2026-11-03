<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_layout_loads_smooth_experience_assets(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($user)->get('/pickup');

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('css/kwdc-ui.css', $html);
        $this->assertStringContainsString('js/kwdc-flow.js', $html);
        $this->assertStringContainsString('id="kwdc-page-progress"', $html);
    }
}
