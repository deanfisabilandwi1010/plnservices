<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Feedback;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function admin(): User
    {
        return User::first();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.resource.index', 'posts'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_log_in(): void
    {
        $this->post(route('admin.login'), ['email' => 'admin@plnservices.test', 'password' => 'PlnServices@2026'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->post(route('admin.login'), ['email' => 'admin@plnservices.test', 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_every_module_screen_renders(): void
    {
        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk();

        foreach (config('cms.modules') as $key => $config) {
            $this->get(route('admin.resource.index', $key))->assertOk();
            $this->get(route('admin.resource.index', [$key, 'q' => 'a']))->assertOk();

            $item = $config['model']::query()->where($config['scope'] ?? [])->first();

            if ($config['readonly'] ?? false) {
                $this->get(route('admin.resource.create', $key))->assertNotFound();
                $this->get(route('admin.resource.show', [$key, $item->id]))->assertOk();
            } else {
                $this->get(route('admin.resource.create', $key))->assertOk();
                $this->get(route('admin.resource.edit', [$key, $item->id]))->assertOk();
            }
        }

        foreach (array_keys(config('cms.settings')) as $section) {
            $this->get(route('admin.settings.edit', $section))->assertOk();
        }
    }

    public function test_unknown_module_is_404(): void
    {
        $this->actingAs($this->admin())->get('/admin/tidak-ada')->assertNotFound();
    }

    public function test_create_update_and_delete_post_with_image(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->post(route('admin.resource.store', 'posts'), [
            'title' => 'Berita Baru',
            'category' => 'Berita',
            'status' => 'published',
            'image' => UploadedFile::fake()->image('cover.jpg'),
            'content' => '<p>Isi</p>',
        ])->assertRedirect(route('admin.resource.index', 'posts'));

        $post = Post::where('title', 'Berita Baru')->firstOrFail();
        $this->assertSame('berita-baru', $post->slug);
        $this->assertNotNull($post->published_at);
        Storage::disk('public')->assertExists($post->image);

        // Same title again gets a unique slug.
        $this->post(route('admin.resource.store', 'posts'), ['title' => 'Berita Baru', 'category' => 'Berita', 'status' => 'draft']);
        $this->assertDatabaseHas(Post::class, ['slug' => 'berita-baru-2']);

        $oldImage = $post->image;
        $this->put(route('admin.resource.update', ['posts', $post->id]), [
            'title' => 'Berita Diubah',
            'category' => 'CSR',
            'status' => 'published',
            'image_remove' => '1',
        ])->assertRedirect();
        $this->assertSame('berita-diubah', $post->fresh()->slug);
        $this->assertNull($post->fresh()->image);
        Storage::disk('public')->assertMissing($oldImage);

        $this->delete(route('admin.resource.destroy', ['posts', $post->id]))->assertRedirect();
        $this->assertModelMissing($post);
    }

    public function test_uploads_go_to_the_configured_media_disk(): void
    {
        config(['cms.media_disk' => 's3']);
        Storage::fake('s3');
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.resource.store', 'posts'), [
            'title' => 'Berita Cloud',
            'category' => 'Berita',
            'status' => 'published',
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertRedirect();

        $path = Post::where('title', 'Berita Cloud')->value('image');
        Storage::disk('s3')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
        $this->assertNotNull(media_url($path));
    }

    public function test_scoped_modules_keep_their_type(): void
    {
        $this->actingAs($this->admin())->post(route('admin.resource.store', 'esg'), ['title' => 'Kebijakan Anti Korupsi', 'year' => '2026']);

        $this->assertDatabaseHas(Document::class, ['title' => 'Kebijakan Anti Korupsi', 'type' => 'esg']);

        $rups = Document::where('type', 'rups')->first();
        $this->get(route('admin.resource.edit', ['esg', $rups->id]))->assertNotFound();
    }

    public function test_validation_errors_are_reported(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.resource.store', 'posts'), ['title' => '', 'status' => 'bogus'])
            ->assertSessionHasErrors(['title', 'status', 'category']);
    }

    public function test_user_password_rules(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.resource.store', 'users'), ['name' => 'Editor', 'email' => 'editor@example.com', 'role' => 'editor'])
            ->assertSessionHasErrors('password');

        $this->post(route('admin.resource.store', 'users'), ['name' => 'Editor', 'email' => 'editor@example.com', 'role' => 'editor', 'password' => 'rahasia123']);
        $editor = User::where('email', 'editor@example.com')->firstOrFail();
        $hash = $editor->password;

        // A blank password on edit keeps the existing one.
        $this->put(route('admin.resource.update', ['users', $editor->id]), ['name' => 'Editor 2', 'email' => 'editor@example.com', 'role' => 'editor', 'password' => '']);
        $this->assertSame($hash, $editor->fresh()->password);
        $this->assertSame('Editor 2', $editor->fresh()->name);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->delete(route('admin.resource.destroy', ['users', $admin->id]))->assertSessionHas('error');
        $this->assertModelExists($admin);
    }

    public function test_feedback_is_marked_read_when_opened(): void
    {
        $feedback = Feedback::first();
        $this->actingAs($this->admin())->get(route('admin.resource.show', ['feedbacks', $feedback->id]))->assertOk()->assertSee($feedback->message);
        $this->assertTrue($feedback->fresh()->is_read);
    }

    public function test_settings_update_reaches_public_site(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update', 'company-overview'), ['hero_title' => 'Judul Hero Baru'])
            ->assertSessionHas('success');

        $this->assertSame('Judul Hero Baru', Setting::get('hero_title'));
        $this->get(route('home'))->assertSee('Judul Hero Baru');
    }

    public function test_editor_image_upload(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->postJson(route('admin.editor-upload'), ['image' => UploadedFile::fake()->image('a.png')])
            ->assertOk()
            ->assertJsonStructure(['url']);
    }
}
