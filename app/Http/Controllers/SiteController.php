<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Feedback;
use App\Models\Milestone;
use App\Models\Office;
use App\Models\Person;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Procurement;
use App\Models\Service;
use App\Models\Statistic;
use App\Models\WorkArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('site.home', [
            'statistics' => Statistic::orderBy('sort_order')->get(),
            'services' => Service::orderBy('sort_order')->get(),
            'portfolios' => Portfolio::orderByDesc('is_featured')->latest('year')->take(6)->get(),
            'posts' => Post::published()->take(6)->get(),
            'direksi' => Person::where('type', 'direksi')->orderBy('sort_order')->get(),
            'categories' => Feedback::CATEGORIES,
            'workAreas' => WorkArea::orderBy('sort_order')->get(['name', 'provinces', 'units']),
            'timeline' => $this->timeline(),
        ]);
    }

    /**
     * Year-by-year rows for the home timeline: projects on one side, news on
     * the other, milestones as annotations on the axis. Only years that have
     * something to show are returned, oldest first.
     */
    private function timeline(): array
    {
        $rows = [];

        foreach (Portfolio::orderBy('year')->get() as $p) {
            $rows[(int) $p->year]['projects'][] = [
                'title' => $p->title,
                'category' => $p->category ?: 'Lainnya',
                'meta' => trim($p->location.' · '.$p->year, ' ·'),
                'url' => route('portfolio.show', $p),
            ];
        }

        foreach (Post::published()->reorder('published_at')->get() as $post) {
            $rows[(int) $post->published_at->year]['news'][] = [
                'title' => $post->title,
                'category' => $post->category,
                'meta' => $post->published_at->translatedFormat('d M Y'),
                'url' => route('news.show', $post),
            ];
        }

        foreach (Milestone::orderBy('sort_order')->get() as $m) {
            $rows[(int) $m->year]['milestones'][] = $m->title;
        }

        ksort($rows);

        return collect($rows)
            ->filter(fn ($row, $year) => $year > 0)
            ->map(fn ($row, $year) => ['year' => $year] + $row + ['projects' => [], 'news' => [], 'milestones' => []])
            ->values()
            ->all();
    }

    public function about(): View
    {
        return view('site.about', [
            'milestones' => Milestone::orderBy('sort_order')->get(),
            'statistics' => Statistic::orderBy('sort_order')->get(),
        ]);
    }

    public function management(): View
    {
        return view('site.management', [
            'direksi' => Person::where('type', 'direksi')->orderBy('sort_order')->get(),
            'komisaris' => Person::where('type', 'komisaris')->orderBy('sort_order')->get(),
        ]);
    }

    public function governance(): View
    {
        return view('site.governance', [
            'groups' => [
                'rups' => ['label' => 'RUPS', 'icon' => 'landmark', 'items' => Document::where('type', 'rups')->latest('year')->get()],
                'anggaran_dasar' => ['label' => 'Anggaran Dasar', 'icon' => 'scale', 'items' => Document::where('type', 'anggaran_dasar')->latest('year')->get()],
                'esg' => ['label' => 'Kebijakan ESG', 'icon' => 'leaf', 'items' => Document::where('type', 'esg')->latest('year')->get()],
            ],
        ]);
    }

    public function services(): View
    {
        return view('site.services', ['services' => Service::orderBy('sort_order')->get()]);
    }

    public function service(Service $service): View
    {
        return view('site.service', [
            'service' => $service,
            'others' => Service::whereKeyNot($service->id)->orderBy('sort_order')->take(3)->get(),
        ]);
    }

    public function portfolio(): View
    {
        $portfolios = Portfolio::latest('year')->get();

        return view('site.portfolio', [
            'portfolios' => $portfolios,
            'categories' => $portfolios->pluck('category')->filter()->unique()->values(),
        ]);
    }

    public function portfolioShow(Portfolio $portfolio): View
    {
        return view('site.portfolio-show', [
            'portfolio' => $portfolio,
            'others' => Portfolio::whereKeyNot($portfolio->id)->latest('year')->take(3)->get(),
        ]);
    }

    public function news(Request $request): View
    {
        $query = Post::published();

        if ($category = $request->query('kategori')) {
            $query->where('category', $category);
        }

        if ($search = $request->query('q')) {
            $query->whereLike('title', "%{$search}%"); // case-insensitive on MySQL, PostgreSQL and SQLite
        }

        return view('site.news', [
            'posts' => $query->paginate(9)->withQueryString(),
            'categories' => Post::published()->reorder()->distinct()->pluck('category'),
            'activeCategory' => $category,
        ]);
    }

    public function newsShow(Post $post): View
    {
        abort_unless($post->status === 'published', 404);

        $post->increment('views');

        return view('site.news-show', [
            'post' => $post,
            'related' => Post::published()->whereKeyNot($post->id)->take(3)->get(),
        ]);
    }

    public function procurement(Request $request): View
    {
        $query = Procurement::latest('start_date');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return view('site.procurement', [
            'procurements' => $query->paginate(10)->withQueryString(),
            'statuses' => Procurement::distinct()->pluck('status'),
            'activeStatus' => $status,
        ]);
    }

    public function network(): View
    {
        return view('site.network', [
            'workAreas' => WorkArea::orderBy('sort_order')->get(),
            'offices' => Office::orderBy('sort_order')->get(),
        ]);
    }

    public function contact(Request $request): View
    {
        $topic = $request->query('topik');

        return view('site.contact', [
            'offices' => Office::orderBy('sort_order')->take(4)->get(),
            'categories' => Feedback::CATEGORIES,
            'topic' => array_key_exists((string) $topic, Feedback::CATEGORIES) ? $topic : null,
        ]);
    }

    public function sendFeedback(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|max:50',
            'company' => 'nullable|max:255',
            'category' => ['required', Rule::in(array_keys(Feedback::CATEGORIES))],
            'subject' => 'required|max:255',
            'message' => 'required|min:10|max:5000',
            'consent' => 'accepted',
        ], [
            'consent.accepted' => 'Mohon setujui penggunaan data untuk keperluan tindak lanjut.',
        ]);

        $feedback = Feedback::create(Arr::except($data, 'consent'));

        return redirect()->route('contact')->with('sent', [
            'reference' => $feedback->reference(),
            'name' => $feedback->name,
            'email' => $feedback->email,
            'unit' => Feedback::CATEGORIES[$feedback->category],
        ]);
    }
}
