<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Person;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Procurement;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));

        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Total Berita', 'value' => Post::count(), 'note' => Post::where('status', 'published')->count().' Dipublikasi', 'icon' => 'newspaper', 'tone' => 'blue', 'module' => 'posts'],
                ['label' => 'Total Portfolio', 'value' => Portfolio::count(), 'note' => 'Proyek', 'icon' => 'briefcase', 'tone' => 'green', 'module' => 'portfolios'],
                ['label' => 'Total Pengadaan', 'value' => Procurement::count(), 'note' => Procurement::where('status', 'Dibuka')->count().' Tender Dibuka', 'icon' => 'gavel', 'tone' => 'amber', 'module' => 'procurements'],
                ['label' => 'Total Feedback', 'value' => Feedback::count(), 'note' => Feedback::where('is_read', false)->count().' Belum Dibaca', 'icon' => 'message-square', 'tone' => 'cyan', 'module' => 'feedbacks'],
                ['label' => 'Total Users', 'value' => User::count(), 'note' => 'Admin', 'icon' => 'users', 'tone' => 'slate', 'module' => 'users'],
                ['label' => 'Direksi', 'value' => Person::where('type', 'direksi')->count(), 'note' => 'Profil Direksi', 'icon' => 'user-round-check', 'tone' => 'blue', 'module' => 'direksi'],
                ['label' => 'Komisaris', 'value' => Person::where('type', 'komisaris')->count(), 'note' => 'Profil Komisaris', 'icon' => 'id-card', 'tone' => 'green', 'module' => 'komisaris'],
            ],
            'chart' => [
                'labels' => $months->map(fn ($m) => $m->translatedFormat('M Y'))->all(),
                'posts' => $months->map(fn ($m) => Post::whereBetween('published_at', [$m, $m->copy()->endOfMonth()])->count())->all(),
                'feedbacks' => $months->map(fn ($m) => Feedback::whereBetween('created_at', [$m, $m->copy()->endOfMonth()])->count())->all(),
            ],
            'latestPosts' => Post::latest('published_at')->take(5)->get(),
            'latestFeedbacks' => Feedback::latest()->take(5)->get(),
        ]);
    }
}
