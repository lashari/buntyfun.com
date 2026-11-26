<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\Ads\AdControls;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Admin → Ads: on/off per section and device, unit limit, excluded paths. Super admins save. */
class AdsAdminController extends Controller
{
    public function index()
    {
        return view('admin.ads.index', [
            'settings' => AdControls::settings(),
            'sections' => AdControls::SECTIONS,
            'clientId' => (string) config('services.adsense.client_id'),
            'slotId' => (string) config('services.adsense.slot_id'),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        $data = $request->validate([
            'ads_max_units' => ['required', 'integer', 'min:0', 'max:'.AdControls::MAX_UNITS],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['in:1'],
            'excluded_paths' => ['nullable', 'string', 'max:5000'],
        ]);

        $paths = collect(preg_split('/\R/', (string) ($data['excluded_paths'] ?? '')))
            ->map(fn ($p) => trim($p))->filter()->unique()->values();
        $bad = $paths->first(fn ($p) => ! preg_match('~^/[A-Za-z0-9/_\-.]*$~', $p) || strlen($p) > 200);
        if ($bad !== null || $paths->count() > 200) {
            throw ValidationException::withMessages(['excluded_paths' => $bad !== null
                ? "\"{$bad}\" is not a path. Each line must start with / and use only letters, numbers, / - _ ."
                : 'At most 200 paths.']);
        }

        $on = array_keys((array) ($data['sections'] ?? []));
        Setting::put('ads_enabled', $request->boolean('ads_enabled'));
        Setting::put('ads_desktop', $request->boolean('ads_desktop'));
        Setting::put('ads_mobile', $request->boolean('ads_mobile'));
        Setting::put('ads_max_units', (int) $data['ads_max_units']);
        Setting::put('ads_sections', collect(AdControls::SECTIONS)->map(fn ($v, $k) => in_array($k, $on, true))->all());
        Setting::put('ads_excluded_paths', $paths->map(fn ($p) => '/'.trim($p, '/'))->unique()->values()->all());
        AuditLog::record($request->user(), 'ads_settings_updated', 'settings', 0);

        return back()->with('status', 'Ad settings saved.');
    }
}
