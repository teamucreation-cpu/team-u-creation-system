<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'package_id',
        'event_date',
        'event_type',
        'venue',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function package()
    {
        return $this->belongsTo(EventPackage::class, 'package_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function inventoryAssignments()
    {
        return $this->hasMany(InventoryAssignment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Only a Completed event with no review yet can have one submitted.
     */
    public function isReviewable(): bool
    {
        return $this->status === 'Completed' && ! $this->review()->exists();
    }

    /**
     * A client can only edit or cancel their own booking while it's still
     * awaiting admin review — once Approved (or any other resolved state)
     * it's locked to view-only.
     */
    public function isEditable(): bool
    {
        return $this->status === 'Pending';
    }

    public function isCancellable(): bool
    {
        return $this->status === 'Pending';
    }

    /**
     * Single source of truth for date availability, shared by the Book Event
     * page's smart date picker and every Event Calendar view (client,
     * admin). Keyed by 'Y-m-d'; a date absent from the map is available.
     *
     * Approved bookings always win over Pending ones on the same date —
     * mirrors the definition of "booked" already used when an admin approves
     * a booking (see AdminBookingController::approve()'s conflict check).
     *
     * $excludeBookingId lets the Edit Booking page treat the booking's own
     * current date as available rather than "conflicting with itself".
     *
     * @return array<string, array{status: string, event_type: string}>
     */
    public static function monthAvailability(int $year, int $month, ?int $excludeBookingId = null): array
    {
        $start = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = static::whereIn('status', ['Pending', 'Approved'])
            ->whereBetween('event_date', [$start, $end]);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        $rows = $query
            ->orderByRaw("CASE WHEN status = 'Approved' THEN 0 ELSE 1 END")
            ->get(['event_date', 'status', 'event_type']);

        $map = [];
        foreach ($rows as $row) {
            $key = $row->event_date->format('Y-m-d');
            if (isset($map[$key])) {
                continue; // an Approved row for this date was already recorded first
            }
            $map[$key] = [
                'status' => $row->status === 'Approved' ? 'booked' : 'pending',
                'event_type' => $row->event_type,
            ];
        }

        return $map;
    }

    /**
     * Minimum-advance-notice booking rules, applied per event type. Keyed by
     * a lowercase keyword matched case-insensitively as a substring against
     * the free-text "event_type" field, so "Wedding Reception" or "corporate
     * seminar" still match "wedding"/"corporate". The first matching keyword
     * wins. This is the single source of truth for the rule — both the
     * server-side validation in BookingController and the client-side
     * "Booking Rule" preview on the Book Event page (via
     * bookingRulesForJs()) read from it, so they can never disagree.
     *
     * To support another event type, add a row here — nothing else needs to
     * change.
     *
     * @var array<string, array{amount: int, unit: 'day'|'month', label: string}>
     */
    public const BOOKING_RULES = [
        'wedding' => ['amount' => 3, 'unit' => 'month', 'label' => 'Wedding'],
        'birthday' => ['amount' => 3, 'unit' => 'day', 'label' => 'Birthday'],
        'corporate' => ['amount' => 4, 'unit' => 'day', 'label' => 'Corporate'],
    ];

    /**
     * Applied to any event type that doesn't match a keyword above.
     */
    public const BOOKING_RULE_DEFAULT = ['amount' => 7, 'unit' => 'day', 'label' => 'General Event'];

    /**
     * Resolve the applicable advance-notice rule for a (free-text) event type.
     */
    public static function bookingRuleFor(?string $eventType): array
    {
        $needle = strtolower(trim((string) $eventType));

        if ($needle !== '') {
            foreach (self::BOOKING_RULES as $keyword => $rule) {
                if (str_contains($needle, $keyword)) {
                    return $rule;
                }
            }
        }

        return self::BOOKING_RULE_DEFAULT;
    }

    /**
     * The earliest event_date that satisfies the given event type's
     * advance-notice rule, measured from $from (defaults to today).
     */
    public static function earliestBookableDate(?string $eventType, ?\Carbon\Carbon $from = null): \Carbon\Carbon
    {
        $from = ($from ?? now())->copy()->startOfDay();
        $rule = self::bookingRuleFor($eventType);

        return $rule['unit'] === 'month'
            ? $from->addMonths($rule['amount'])
            : $from->addDays($rule['amount']);
    }

    /**
     * "3 months" / "4 days" / "1 day" — pluralized notice phrase for a rule.
     */
    public static function bookingRuleNoticeLabel(array $rule): string
    {
        return $rule['amount'] . ' ' . \Illuminate\Support\Str::plural($rule['unit'], $rule['amount']);
    }

    /**
     * BOOKING_RULES reshaped into a flat list (keyword included per row),
     * ready to hand to the client-side date picker via @json().
     */
    public static function bookingRulesForJs(): array
    {
        $rows = [];
        foreach (self::BOOKING_RULES as $keyword => $rule) {
            $rows[] = array_merge(['keyword' => $keyword], $rule);
        }

        return $rows;
    }
}
