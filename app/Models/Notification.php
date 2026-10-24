<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'appointment_id',
        'message',
        'notification_type',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * A short, friendly relative timestamp — "Just now", "5 minutes ago",
     * "1 hour ago", "Yesterday", "2 days ago" — rather than Carbon's default
     * diffForHumans() wording (which never says "Just now" or "Yesterday").
     * Shared by every place a notification list is rendered.
     */
    public function getRelativeTimeAttribute(): string
    {
        $created = $this->created_at;
        // This Carbon version's diffInX() methods return floats (fractional
        // minutes/hours/days), not the whole-number counts diffForHumans()
        // uses internally — truncate explicitly so "5 minutes ago" doesn't
        // render as "5.016... minutes ago".
        $seconds = (int) $created->diffInSeconds(now());
        $minutes = (int) $created->diffInMinutes(now());
        $hours = (int) $created->diffInHours(now());

        if ($seconds < 60) {
            return 'Just now';
        }
        if ($minutes < 60) {
            return $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' ago';
        }
        if ($hours < 24) {
            return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
        }
        if ($created->isYesterday()) {
            return 'Yesterday';
        }

        $days = (int) $created->diffInDays(now());

        return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
    }
}
