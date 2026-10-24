<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_public_pages_render(): void
    {
        $urls = [
            route('home'), route('about'), route('management'), route('governance'),
            route('services'), route('services.show', Service::first()),
            route('portfolio'), route('portfolio.show', Portfolio::first()),
            route('news'), route('news', ['kategori' => 'CSR', 'q' => 'listrik']), route('news.show', Post::published()->first()),
            route('procurement'), route('procurement', ['status' => 'Dibuka']),
            route('network'), route('contact'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk()->assertSee('PLN Services');
        }
    }

    public function test_draft_posts_are_hidden(): void
    {
        $post = Post::first();
        $post->update(['status' => 'draft']);

        $this->get(route('news.show', $post))->assertNotFound();
    }

    public function test_contact_form_stores_feedback_with_reference(): void
    {
        $this->post(route('contact.send'), [
            'name' => 'Dewi',
            'email' => 'dewi@example.com',
            'company' => 'PT Contoh',
            'category' => 'Kemitraan & Bisnis',
            'subject' => 'Kemitraan',
            'message' => 'Halo, kami ingin bermitra.',
            'consent' => '1',
        ])->assertRedirect(route('contact'))->assertSessionHas('sent');

        $feedback = Feedback::where('email', 'dewi@example.com')->firstOrFail();
        $this->assertFalse($feedback->is_read);
        $this->assertSame('PT Contoh', $feedback->company);

        $this->followingRedirects()->get(route('contact'))->assertOk();
        $this->withSession(['sent' => ['reference' => $feedback->reference(), 'name' => 'Dewi', 'email' => 'dewi@example.com', 'unit' => 'Divisi Pengembangan Bisnis']])
            ->get(route('contact'))
            ->assertSee($feedback->reference());
    }

    public function test_contact_form_validates(): void
    {
        $this->post(route('contact.send'), ['email' => 'bukan-email', 'category' => 'Tidak Ada'])
            ->assertSessionHasErrors(['name', 'email', 'category', 'subject', 'message', 'consent']);
    }

    public function test_contact_topic_is_preselected(): void
    {
        $html = $this->get(route('contact', ['topik' => 'Pengadaan & Vendor']))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/value="Pengadaan &amp; Vendor"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="Informasi Umum"[^>]*checked/', $html);
    }
}
