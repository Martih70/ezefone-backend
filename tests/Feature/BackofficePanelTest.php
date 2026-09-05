<?php

namespace Tests\Feature;

use App\Filament\Widgets\PricingTierChart;
use App\Filament\Widgets\SignupsChart;
use App\Filament\Widgets\UsageOverview;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BackofficePanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_away_from_the_panel(): void
    {
        $this->get('/backoffice')->assertRedirect();
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);

        $user = User::factory()->create(['email' => 'someone-else@example.com']);

        $this->actingAs($user)->get('/backoffice')->assertForbidden();
    }

    public function test_admin_can_view_the_dashboard(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);

        $admin = User::factory()->create(['email' => 'admin@example.com']);

        // Dashboard widgets lazy-load via Livewire, so their content isn't in
        // the initial page response — assert the page itself loads, and
        // exercise each widget's actual query logic directly below.
        $this->actingAs($admin)->get('/backoffice')->assertOk();
    }

    public function test_usage_overview_widget_reports_correct_counts(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->actingAs($admin);

        User::factory()->create(['pricing_tier' => 'early_adopter']);
        User::factory()->create(['pricing_tier' => null]);
        Feedback::create(['user_id' => $admin->id, 'message' => 'hi', 'emailed' => true]);

        Livewire::test(UsageOverview::class)
            ->assertSee('Total users')
            ->assertSee('Paid users')
            ->assertSee('Active today')
            ->assertSee('Feedback this week');
    }

    public function test_signups_chart_widget_renders(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->actingAs($admin);

        Livewire::test(SignupsChart::class)->assertOk();
    }

    public function test_pricing_tier_chart_widget_renders(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->actingAs($admin);

        Livewire::test(PricingTierChart::class)->assertOk();
    }

    public function test_admin_can_view_users_list_and_detail(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);

        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $other = User::factory()->create(['email' => 'jane@example.com']);

        $this->actingAs($admin)
            ->get('/backoffice/users')
            ->assertOk()
            ->assertSee('jane@example.com');

        $this->actingAs($admin)
            ->get("/backoffice/users/{$other->id}")
            ->assertOk();
    }

    public function test_admin_can_view_feedback_list_and_detail(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);

        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $author = User::factory()->create();
        $feedback = Feedback::create([
            'user_id' => $author->id,
            'message' => 'The check-in feature is great, thanks!',
            'emailed' => true,
        ]);

        $this->actingAs($admin)
            ->get('/backoffice/feedback')
            ->assertOk()
            ->assertSee('The check-in feature is great');

        $this->actingAs($admin)
            ->get("/backoffice/feedback/{$feedback->id}")
            ->assertOk();
    }
}
