<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GrowthPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/growth-plan')->assertRedirect();
    }

    public function test_admin_sees_plan_with_status_and_overdue(): void
    {
        Config::set('growth_plan', [
            'updated' => '11-24-2026',
            'phases' => [[
                'title' => 'Test phase',
                'items' => [
                    ['task' => 'Finished thing', 'status' => 'done', 'who' => 'Claude', 'when' => '01-01-2020', 'note' => ''],
                    ['task' => 'Late thing', 'status' => 'todo', 'who' => 'Claude', 'when' => '01-01-2020', 'note' => ''],
                    ['task' => 'Future thing', 'status' => 'needs_you', 'who' => 'You', 'when' => '01-01-2099', 'note' => 'n'],
                ],
            ]],
        ]);

        $user = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($user)->get('/admin/growth-plan')
            ->assertOk()
            ->assertSee('Growth Plan')
            ->assertSee('1 of 3 tasks done (33%)')
            ->assertSee('Late thing')
            ->assertSee('(overdue)', false)
            ->assertSee('Needs you')
            ->assertSee('Content on the site now');
    }

    public function test_real_plan_config_is_valid(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        foreach (config('growth_plan.phases') as $phase) {
            foreach ($phase['items'] as $item) {
                $this->assertContains($item['status'], ['done', 'doing', 'todo', 'needs_you'], $item['task']);
                $this->assertContains($item['who'], ['Claude', 'You'], $item['task']);
            }
        }
        $this->actingAs($user)->get('/admin/growth-plan')->assertOk();
    }
}
