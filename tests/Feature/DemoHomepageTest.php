<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoHomepageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_homepage_renders_with_seeded_data(): void
    {
        $response = $this->get(route('public.demo'));

        $response->assertOk();

        // Real, dynamic content — not static placeholder copy.
        $response->assertSee('LevelXGamingArena');
        $response->assertSee('PC Games');
        $response->assertSee('PS5');
        $response->assertSee('Premium Snooker Room');
        $response->assertSee('Private Table Tennis Room');
        $response->assertSee('Account packages');

        // Section landmarks of the concept layout.
        $response->assertSee('Pick your arena', false);
        $response->assertSee('Every activity, live availability', false);
        $response->assertSee('From screen to table in three steps', false);
        $response->assertSee('Ready when you are', false);

        // Key CTAs resolve to the real public pages. (About-us and contact are
        // both linked on-page; the compact footer carries the legal pages.)
        $response->assertSee(route('public.activity', \App\Models\Resource::where('code', 'PC-GAMES')->firstOrFail()), false);
        $response->assertSee(route('public.contact'), false);
        $response->assertSee(route('public.about'), false);
    }

    public function test_demo_homepage_works_for_guests_and_customers(): void
    {
        $this->get(route('public.demo'))->assertOk();

        $customer = User::where('email', 'customer@example.com')->firstOrFail();
        $this->actingAs($customer)->get(route('public.demo'))->assertOk();
    }

    public function test_demo_homepage_only_shows_online_bookable_activities(): void
    {
        // Legacy demo stations stay in the back office but are not sold online,
        // and SNK-3 is seeded in maintenance — neither may appear on the floor.
        $response = $this->get(route('public.demo'));

        $response->assertOk();
        $response->assertSee('PC Games');
        $response->assertDontSee('Gaming Zone TV 1');
        $response->assertDontSee('Snooker Table 3 (Maintenance)');
    }

    public function test_demo_homepage_respects_display_settings(): void
    {
        Setting::set('show_activity_image', '0');
        Setting::set('show_hourly_rate', '0');

        $response = $this->get(route('public.demo'));

        $response->assertOk();
        // Rates hidden → no "/hour" label should be rendered anywhere.
        $response->assertDontSee('/hour', false);
    }

    public function test_demo_homepage_reflects_category_changes(): void
    {
        Category::where('slug', 'pc-gaming')->update(['is_active' => false]);

        $response = $this->get(route('public.demo'));

        $response->assertOk();
        $response->assertDontSee('PC Games');
    }

    public function test_demo_homepage_handles_empty_floor_gracefully(): void
    {
        // Nothing bookable online → page must still render 200.
        \App\Models\Resource::query()->update(['is_online_bookable' => false]);

        $response = $this->get(route('public.demo'));

        $response->assertOk();
        $response->assertSee('No activities are available for online booking right now.');
    }

    public function test_demo_homepage_images_point_at_existing_public_assets(): void
    {
        Business::query()->update(['opens_at' => '12:00']);

        $html = $this->get(route('public.demo'))->getContent();

        // Every activity image URL emitted by the page must map to a file that
        // actually exists on the public disk (no broken <img> srcs).
        preg_match_all('/src="([^"]+)"/', $html, $matches);

        $checked = 0;
        foreach (array_unique($matches[1]) as $src) {
            if (str_contains($src, '/storage/')) {
                $path = public_path(str_replace('/storage/', 'storage/', parse_url($src, PHP_URL_PATH)));
                $this->assertFileExists($path, "Missing storage asset: {$src}");
                $checked++;
            } elseif (str_starts_with($src, asset('img/'))) {
                $path = public_path(ltrim(parse_url($src, PHP_URL_PATH), '/'));
                $this->assertFileExists($path, "Missing bundled asset: {$src}");
                $checked++;
            }
        }

        $this->assertGreaterThan(0, $checked, 'No images were checked — the demo page emitted no local image URLs.');
    }

    public function test_homepage_route_serves_the_demo_concept(): void
    {
        // / is now the demo homepage; /demo stays as an alias for old links.
        $this->get(route('public.index'))
            ->assertOk()
            ->assertSee('Pick your arena', false)
            ->assertSee('Inside the arena', false);

        $this->get(route('public.demo'))->assertOk();
    }

    public function test_demo_homepage_renders_a_minimal_footer(): void
    {
        $html = $this->get(route('public.demo'))->getContent();

        // The full multi-column public footer is intentionally not rendered on
        // the concept homepage — a compact, centred link bar takes its place.
        $this->assertStringNotContainsString('Snooker, PS5, PC gaming and VIP rooms', $html);

        // Every public informational page is reachable from the homepage.
        foreach (['About Us', 'Contact Us', 'Privacy Policy', 'Disclaimer', 'Terms &amp; Conditions', 'Cookie Policy'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        foreach (['public.about', 'public.contact', 'public.privacy', 'public.disclaimer', 'public.terms', 'public.cookies'] as $route) {
            $this->assertStringContainsString(route($route), $html);
        }
    }

    public function test_homepage_uses_the_whatsapp_cta_and_24_7_branding(): void
    {
        $html = $this->get(route('public.index'))->getContent();
        $business = Business::primary();

        $this->assertTrue($business->is24Hours());
        $this->assertSame('Open 24/7', $business->hoursLabel());

        // Hero call to action opens WhatsApp with the venue's own number and a
        // prefilled enquiry message.
        $this->assertStringContainsString('https://wa.me/'.$business->whatsAppNumber().'?text=', $html);
        $this->assertStringContainsString(
            rawurlencode('Hi, I would like to inquire about your gaming zone and activities.'),
            $html
        );
        $this->assertStringContainsString('WhatsApp Now', $html);
        $this->assertStringNotContainsString('Closed right now', $html);
        $this->assertStringContainsString('Open 24/7', $html);
    }

    public function test_ready_section_no_longer_offers_a_book_now_button(): void
    {
        $html = $this->get(route('public.index'))->getContent();

        $start = strpos($html, 'Ready when you are');
        $this->assertNotFalse($start);

        $this->assertStringNotContainsString('Book Now', substr($html, $start, 2500));
    }

    public function test_demo_homepage_gallery_items_open_the_lightbox(): void
    {
        $html = $this->get(route('public.demo'))->getContent();

        // Gallery buttons dispatch the lightbox event from inside an Alpine
        // component (x-data on the slider), and the modal listens on window.
        $this->assertStringContainsString('class="mobile-slider mt-8" x-data', $html);
        $this->assertStringContainsString('sm:grid-cols-3 lg:grid-cols-4', $html);
        $this->assertStringContainsString("\$dispatch('dz-open'", $html);
        $this->assertStringContainsString('@dz-open.window="i = $event.detail; open = true"', $html);
    }
}
