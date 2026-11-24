<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiTool;
use App\Models\BirthdayMessage;
use App\Models\Game;
use App\Models\Message;
use App\Models\NameEntry;
use App\Models\Quiz;
use App\Models\UrduContent;
use App\Models\Wallpaper;
use Illuminate\Support\Carbon;

class GrowthPlanController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $phases = collect(config('growth_plan.phases', []))->map(function (array $phase) use ($today) {
            $phase['items'] = collect($phase['items'])->map(function (array $item) use ($today) {
                $date = $this->parseDate($item['when'] ?? '');
                $item['overdue'] = $item['status'] !== 'done' && $date !== null && $date->lt($today);

                return $item;
            })->all();

            return $phase;
        });

        $all = $phases->flatMap(fn ($p) => $p['items']);
        $totals = [
            'done' => $all->where('status', 'done')->count(),
            'doing' => $all->where('status', 'doing')->count(),
            'todo' => $all->where('status', 'todo')->count(),
            'needs_you' => $all->where('status', 'needs_you')->count(),
            'overdue' => $all->where('overdue', true)->count(),
            'all' => $all->count(),
        ];

        $content = [];
        foreach ([
            'Birthday wishes' => BirthdayMessage::class,
            'Quizzes' => Quiz::class,
            'Games' => Game::class,
            'Wallpapers' => Wallpaper::class,
            'Islamic names' => NameEntry::class,
            'Urdu content' => UrduContent::class,
            'Messages' => Message::class,
            'AI tools' => AiTool::class,
        ] as $label => $model) {
            $content[$label] = [
                'published' => $model::where('status', 'published')->count(),
                'waiting' => $model::where('status', '!=', 'published')->count(),
            ];
        }

        $next = $all->whereIn('status', ['todo', 'doing', 'needs_you'])
            ->filter(fn ($i) => $this->parseDate($i['when'] ?? '') !== null)
            ->sortBy(fn ($i) => $this->parseDate($i['when'])->timestamp)
            ->take(5)->values();

        return view('admin.growth-plan', [
            'phases' => $phases,
            'totals' => $totals,
            'content' => $content,
            'next' => $next,
            'updated' => config('growth_plan.updated'),
        ]);
    }

    private function parseDate(string $when): ?Carbon
    {
        if (! preg_match('/^\d{2}-\d{2}-\d{4}$/', $when)) {
            return null;
        }

        return Carbon::createFromFormat('m-d-Y', $when)->startOfDay();
    }
}
