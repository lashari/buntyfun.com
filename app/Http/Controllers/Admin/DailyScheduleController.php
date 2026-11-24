<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DailyFeature;
use App\Services\Library\DailyPicker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Next 14 days of quiz / quote / game of the day. Pick an item to pin it; "Auto" lets the date-based picker choose. */
class DailyScheduleController extends Controller
{
    public function index(DailyPicker $picker)
    {
        $days = collect(range(0, 13))->map(fn ($i) => today()->addDays($i));
        $rows = DailyFeature::whereBetween('feature_date', [today()->toDateString(), today()->addDays(13)->toDateString()])->get()
            ->keyBy(fn ($r) => $r->feature_date->toDateString().'|'.$r->type);
        $options = collect(DailyFeature::TYPES)->mapWithKeys(fn ($label, $type) => [$type => $picker->model($type)::where('status', 'published')
            ->orderBy($type === 'quote' ? 'id' : 'title')->limit(500)->get()->mapWithKeys(fn ($m) => [$m->id => $type === 'quote' ? \Illuminate\Support\Str::limit($m->body, 70).' — '.$m->author : $m->title])]);
        $shown = $days->mapWithKeys(fn ($d) => [$d->toDateString() => collect(array_keys(DailyFeature::TYPES))->mapWithKeys(fn ($t) => [$t => $picker->item($t, $d)?->id])]);

        return view('admin.daily.index', compact('days', 'rows', 'options', 'shown'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'type' => ['required', Rule::in(array_keys(DailyFeature::TYPES))],
            'item_id' => ['nullable', 'integer'],
        ]);
        if (empty($data['item_id'])) {
            DailyFeature::whereDate('feature_date', $data['date'])->where('type', $data['type'])->delete();
        } else {
            $model = app(DailyPicker::class)->model($data['type']);
            abort_unless($model::where('status', 'published')->whereKey($data['item_id'])->exists(), 422);
            DailyFeature::setFor(\Illuminate\Support\Carbon::parse($data['date'])->toDateString(), $data['type'], ['item_id' => $data['item_id'], 'pinned' => true]);
        }
        AuditLog::record($request->user(), 'scheduled_daily', $data['type'], (int) ($data['item_id'] ?? 0));

        return back()->with('status', 'Daily schedule saved.');
    }
}
