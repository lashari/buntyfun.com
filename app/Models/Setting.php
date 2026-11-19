<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Admin-editable settings (no code edits needed). Defaults live in DEFAULTS; a row
 * only exists once an admin saves a value.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public const DEFAULTS = [
        // How many drafts the daily job writes, and how they split across types (weights).
        'drafts_per_day' => 6,
        'type_weights' => ['birthday' => 3, 'message' => 3, 'urdu' => 1, 'name' => 0],
        // Monthly AI spend cap in USD; generation stops when reached.
        'ai_monthly_budget_usd' => 5,
        // Estimated price per 1M tokens (USD). Edit to your provider's current price list.
        'ai_price_input_per_m' => 0.10,
        'ai_price_output_per_m' => 0.40,
        // Name meanings may only cite these works (admin-approved list).
        'approved_name_references' => [],
        // Failure alerts by email (only if mail is configured on the server).
        'alert_email' => '',
        // Auto-publish (19 Nov 2026): the daily job publishes a draft at once when
        // EVERY quality check passed (blocking and non-blocking). Name meanings
        // never auto-publish: they need a person to verify the reference.
        'auto_publish' => false,
    ];

    public static function get(string $key): mixed
    {
        $row = static::find($key);

        return $row ? $row->value['v'] ?? null : (self::DEFAULTS[$key] ?? null);
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => ['v' => $value]]);
    }
}
