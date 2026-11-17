<?php

namespace Tests\Feature;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Models\Article;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_lists_upcoming_events_soonest_first(): void
    {
        Event::factory()->create(['title' => 'Later Career Fair', 'starts_at' => now()->addDays(9)]);
        Event::factory()->create(['title' => 'Sooner Meetup', 'starts_at' => now()->addDays(2)]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Sooner Meetup', $html);
        $this->assertLessThan(
            strpos($html, 'Later Career Fair'),
            strpos($html, 'Sooner Meetup'),
            'The nearest event should be listed first.',
        );
    }

    public function test_events_that_have_started_or_are_not_due_yet_stay_hidden(): void
    {
        Event::factory()->past()->create(['title' => 'Yesterday Workshop']);
        Event::factory()->create(['title' => 'Next Week Webinar', 'starts_at' => now()->addDays(7)]);
        Event::factory()->create([
            'title' => 'Scheduled Later',
            'starts_at' => now()->addDays(20),
            'published_at' => now()->addDays(10),
        ]);

        $this->get('/')->assertOk()
            ->assertSee('Next Week Webinar')
            ->assertDontSee('Yesterday Workshop')
            ->assertDontSee('Scheduled Later');
    }

    public function test_events_sit_above_the_career_edit(): void
    {
        Article::factory()->published()->create(['title' => 'A guide to interviews']);
        Event::factory()->create(['title' => 'Nairobi Town Hall', 'starts_at' => now()->addDays(3)]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'id="career-heading"'),
            strpos($html, 'id="events-heading"'),
            'Events belong above The Career Edit in the sidebar.',
        );
        $this->assertLessThan(strpos($html, 'A guide to interviews'), strpos($html, 'Nairobi Town Hall'));
    }

    public function test_the_career_edit_is_cut_to_two_stories(): void
    {
        Article::factory()->published()->count(3)->create();

        $html = $this->get('/')->assertOk()->getContent();

        $stories = substr_count($html, 'class="lead-story"') + substr_count($html, 'class="small-story"');

        $this->assertSame(2, $stories, 'Half the column is now events, so the career edit shows two stories.');
    }

    public function test_only_http_links_are_emitted_for_an_event(): void
    {
        Event::factory()->create([
            'title' => 'Sketchy Session',
            'url' => 'javascript:alert(1)',
            'starts_at' => now()->addDays(2),
        ]);

        $this->get('/')->assertOk()
            ->assertSee('Sketchy Session')
            ->assertDontSee('javascript:alert', false);
    }

    public function test_event_copy_is_escaped(): void
    {
        Event::factory()->create([
            'title' => '<script>alert(1)</script>',
            'location' => '" onmouseover="alert(1)',
            'starts_at' => now()->addDays(2),
        ]);

        $this->get('/')->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('onmouseover="alert(1)"', false);
    }

    public function test_saving_without_a_publish_date_stamps_one(): void
    {
        $event = Event::create([
            'title' => 'Unstamped Event',
            'starts_at' => now()->addDays(4),
            'url' => 'https://example.org/rsvp',
        ]);

        $this->assertNotNull($event->fresh()->published_at);
        $this->get('/')->assertOk()->assertSee('Unstamped Event');
    }

    public function test_the_events_panel_is_admin_only(): void
    {
        $this->get('/mdosi/events')->assertRedirect('/mdosi/login');

        $user = User::factory()->create();
        $this->actingAs($user)->get('/mdosi/events')->assertForbidden();

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)->get('/mdosi/events')->assertOk()
            ->assertSee('Events');
    }

    public function test_the_panel_stores_nairobi_time_as_utc(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);

        Livewire::test(CreateEvent::class)
            ->fillForm([
                'title' => 'Nairobi Meetup',
                'starts_at' => '2026-12-01 09:30:00',
                'location' => 'Nairobi',
                'url' => 'https://example.org/rsvp',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $event = Event::firstWhere('title', 'Nairobi Meetup');

        $this->assertNotNull($event);
        // 09:30 in Nairobi is 06:30 UTC; storing the typed string as UTC would
        // show the editor's event three hours late.
        $this->assertSame('2026-12-01 06:30:00', $event->starts_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('09:30', $event->startsAtNairobi()->format('H:i'));
    }

    public function test_the_panel_shows_a_stored_event_in_nairobi_time(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);

        $event = Event::factory()->create([
            'starts_at' => '2026-12-01 06:30:00',
        ]);

        $state = Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->get('data.starts_at');

        $this->assertSame(
            '2026-12-01 09:30:00',
            \Illuminate\Support\Carbon::parse($state)->format('Y-m-d H:i:s'),
        );
    }

    public function test_the_soonest_event_shows_its_banner_with_alt_text(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $lead = Event::factory()->create([
            'title' => 'Banner Event',
            'starts_at' => now()->addDays(2),
            'banner_path' => 'events/banner.png',
            'banner_alt' => 'Candidates queueing at a hiring fair',
        ]);

        $later = Event::factory()->create([
            'title' => 'Later Event',
            'starts_at' => now()->addDays(8),
            'banner_path' => 'events/later.png',
            'banner_alt' => 'A workshop room',
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('alt="Candidates queueing at a hiring fair"', $html);
        $this->assertStringNotContainsString('alt="A workshop room"', $html, 'Only the soonest event carries a banner.');
    }

    public function test_events_without_a_banner_render_no_image(): void
    {
        Event::factory()->create(['title' => 'Plain Event', 'starts_at' => now()->addDays(2)]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('event-banner', $html);
    }

}
