<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Resource;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Covers activity cover art: keyword search, gallery selection, manual upload,
 * replacement on edit and the stored URLs used by every listing.
 */
class ActivityImageTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->firstOrFail();
    }

    public function test_image_search_returns_relevant_gaming_covers(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.activities.image-search', ['q' => 'PS5 Gaming']));

        $response->assertOk()->assertJsonPath('ok', true);

        $results = $response->json('results');

        $this->assertNotEmpty($results);
        $this->assertStringContainsString('ps5', strtolower($results[0]['file']));
        $this->assertNotEmpty($results[0]['preview']);
    }

    public function test_image_search_without_a_query_returns_the_catalogue(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.activities.image-search'));

        $response->assertOk();
        $this->assertGreaterThanOrEqual(5, count($response->json('results')));
    }

    public function test_admin_can_create_activity_with_a_selected_gallery_image(): void
    {
        $category = Category::where('slug', 'snooker')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.activities.store'), array_merge($this->payload($category), [
                'image_library' => 'img/activities/ps5.svg',
            ]))
            ->assertRedirect(route('admin.activities.index'));

        $resource = Resource::where('code', 'IMG-1')->firstOrFail();

        $this->assertNotNull($resource->image_path);
        $this->assertTrue(Storage::disk('public')->exists($resource->image_path));

        // The public disk URL is relative so images survive any host/port change.
        $this->assertSame('/storage', config('filesystems.disks.public.url'));
        $this->assertStringStartsWith('/storage/activities/', $resource->imageUrl());
    }

    public function test_edit_form_renders_the_search_ui_and_current_cover(): void
    {
        $resource = Resource::where('code', 'SNK-1')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.activities.edit', $resource))
            ->assertOk()
            ->assertSee('id="image-search"', false)
            ->assertSee('id="image-preview"', false)
            ->assertSee('id="image-input"', false);
    }

    public function test_admin_can_upload_an_activity_image(): void
    {
        $category = Category::where('slug', 'ps5')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.activities.store'), array_merge($this->payload($category), [
                'image' => UploadedFile::fake()->image('cover.jpg', 800, 450),
            ]))
            ->assertRedirect(route('admin.activities.index'));

        $resource = Resource::where('code', 'IMG-1')->firstOrFail();

        $this->assertNotNull($resource->image_path);
        $this->assertStringEndsWith('.jpg', $resource->image_path);
        $this->assertTrue(Storage::disk('public')->exists($resource->image_path));
    }

    public function test_editing_replaces_the_stored_image(): void
    {
        $category = Category::where('slug', 'ps5')->firstOrFail();

        $resource = Resource::create([
            'business_id' => Business::primary()->id,
            'category_id' => $category->id,
            'name' => 'Editable Booth',
            'code' => 'EDIT-1',
            'capacity' => 4,
            'duration_minutes' => 60,
            'hourly_rate' => 400,
            'status' => 'active',
            'sort_order' => 1,
            'image_path' => 'activities/old.svg',
        ]);

        Storage::disk('public')->put('activities/old.svg', '<svg></svg>');

        $this->actingAs($this->admin)
            ->patch(route('admin.activities.update', $resource), array_merge($this->payload($category, 'Editable Booth'), [
                'code' => 'EDIT-1',
                'image_library' => 'img/activities/pool.svg',
            ]))
            ->assertRedirect(route('admin.activities.index'));

        $resource->refresh();

        $this->assertNotSame('activities/old.svg', $resource->image_path);
        $this->assertTrue(Storage::disk('public')->exists($resource->image_path));
        $this->assertFalse(Storage::disk('public')->exists('activities/old.svg'));
    }

    public function test_a_broken_stored_image_falls_back_to_category_artwork(): void
    {
        $category = Category::where('slug', 'snooker')->firstOrFail();

        $resource = Resource::create([
            'business_id' => Business::primary()->id,
            'category_id' => $category->id,
            'name' => 'Ghost Table',
            'capacity' => 2,
            'duration_minutes' => 60,
            'hourly_rate' => 100,
            'status' => 'active',
            'sort_order' => 2,
            // Points at a file that does not exist on disk.
            'image_path' => 'activities/does-not-exist.svg',
        ]);

        $this->assertFalse($resource->hasCustomImage());
        $this->assertStringContainsString('/img/activities/covers/snooker.jpg', $resource->imageUrl());
    }

    /** A valid create/update payload for an activity. */
    protected function payload(Category $category, string $name = 'Image Activity'): array
    {
        return [
            'business_id' => Business::primary()->id,
            'category_id' => $category->id,
            'name' => $name,
            'code' => 'IMG-1',
            'capacity' => 4,
            'duration_minutes' => 60,
            'hourly_rate' => 300,
            'status' => 'active',
            'sort_order' => 1,
        ];
    }
}
