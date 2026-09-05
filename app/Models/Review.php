<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'booking_id',
        'title',
        'rating',
        'feedback',
        'status',
    ];

    /**
     * A client may edit their own review only while it's still Pending —
     * once an Admin has acted on it (Approved/Rejected), it's locked.
     */
    public function isEditable(): bool
    {
        return $this->status === 'Pending';
    }

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
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
}
