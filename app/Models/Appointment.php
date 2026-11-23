<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    public const RESCHEDULE_AWAITING_CLIENT = 'Rescheduled – Awaiting Client Confirmation';

    protected $fillable = [
        'client_id',
        'booking_id',
        'appointment_date',
        'appointment_time',
        'original_appointment_date',
        'original_appointment_time',
        'proposed_appointment_date',
        'proposed_appointment_time',
        'reschedule_reason',
        'reschedule_response',
        'reschedule_responded_at',
        'purpose',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'original_appointment_date' => 'date',
            'proposed_appointment_date' => 'date',
            'reschedule_responded_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public static function hasScheduleConflict(string $date, string $time, ?int $excludeAppointmentId = null): bool
    {
        $query = static::query()
            ->whereIn('status', ['Pending', 'Approved', 'Confirmed', 'Rescheduled', self::RESCHEDULE_AWAITING_CLIENT])
            ->where(function ($schedule) use ($date, $time) {
                $schedule->where(function ($current) use ($date, $time) {
                    $current->whereDate('appointment_date', $date)
                        ->whereTime('appointment_time', $time);
                })->orWhere(function ($proposed) use ($date, $time) {
                    $proposed->whereDate('proposed_appointment_date', $date)
                        ->whereTime('proposed_appointment_time', $time);
                });
            });

        if ($excludeAppointmentId !== null) {
            $query->whereKeyNot($excludeAppointmentId);
        }

        return $query->exists();
    }
}
