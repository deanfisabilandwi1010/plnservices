<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftLaunchTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    // The "/" route is chosen at boot, so the switch has to be on before the app starts.
    public function createApplication()
    {
        $this->setSwitch('true');

        return parent::createApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->setSwitch('false');
    }

    private function setSwitch(string $value): void
    {
        $_ENV['SOFT_LAUNCH'] = $_SERVER['SOFT_LAUNCH'] = $value;
        putenv("SOFT_LAUNCH={$value}");
    }

    public function test_root_shows_coming_soon_and_links_to_the_current_home(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Website Korporat Sub Holding ke-5 PT PLN (Persero)')
            ->assertSee('PT PLN Services gabungan PT PLN Nusa Daya dan PT PLN Electricity Services.')
            ->assertSee(route('home'));

        $this->assertStringEndsWith('/beranda', route('home'));
        $this->get(route('home'))->assertOk();
    }

    public function test_only_home_and_profil_are_open(): void
    {
        foreach (['about', 'management'] as $route) {
            $this->get(route($route))->assertOk();
        }

        foreach (['/tata-kelola', '/layanan', '/layanan/'.Service::first()->slug, '/portofolio', '/berita', '/pengadaan', '/kontak', '/wilayah-kerja'] as $path) {
            $this->get($path)->assertRedirect('/');
        }
    }

    public function test_menu_marks_closed_sections_without_linking_them(): void
    {
        $html = $this->get(route('about'))->getContent();

        $this->assertStringContainsString('nav-soon', $html);
        $this->assertStringNotContainsString('href="'.route('services').'" data-nav-index', $html);
        $this->assertStringNotContainsString('href="'.route('governance').'" data-nav-index', $html);
        $this->assertStringContainsString('href="'.route('about').'" data-nav-index', $html);
    }

    public function test_home_covers_news_and_portfolio(): void
    {
        $html = $this->get(route('home'))->getContent();

        $this->assertSame(2, substr_count($html, 'data-soon-x'));
        $this->assertStringNotContainsString('id="home-portfolio"', $html);
        $this->assertStringNotContainsString(route('news').'" class="btn', $html);
    }

    public function test_the_home_form_still_shows_its_receipt(): void
    {
        $response = $this->post(route('contact.send'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'category' => array_key_first(Feedback::CATEGORIES),
            'subject' => 'Pertanyaan',
            'message' => 'Halo, saya ingin bertanya.',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('contact'));
        $this->followRedirects($response)->assertOk()->assertSee(Feedback::latest('id')->first()->reference());
    }
}
