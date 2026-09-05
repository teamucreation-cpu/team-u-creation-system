<?php

namespace App\Models;

use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'Chairs',
        'Tables',
        'Artificial Flowers',
        'Backdrops',
        'Tents',
        'Lights',
        'Decorations',
        'Other Event Equipment',
    ];

    protected $fillable = [
        'item_name',
        'category',
        'quantity_total',
        'quantity_available',
        'quantity_in_use',
        'quantity_damaged',
        'quantity_returned',
        'low_stock_threshold',
        'notes',
    ];

    public function assignments()
    {
        return $this->hasMany(InventoryAssignment::class);
    }

    /**
     * Overall availability status, computed rather than stored so it can never
     * drift out of sync with the quantity columns.
     */
    public function getStatusAttribute(): string
    {
        return self::computeStatus($this->quantity_available, $this->low_stock_threshold);
    }

    public static function computeStatus(int $quantityAvailable, int $lowStockThreshold): string
    {
        if ($quantityAvailable <= 0) {
            return 'Out of Stock';
        }

        if ($quantityAvailable <= $lowStockThreshold) {
            return 'Low Stock';
        }

        return 'Available';
    }

    /**
     * Whenever quantity_available changes (from any controller action — a direct
     * admin edit, a new assignment, or a staff return), notify every Admin the
     * moment stock crosses downward into Low Stock or Out of Stock. Re-saves that
     * don't cross a new threshold (e.g. staying Low Stock) do not re-notify.
     */
    protected static function booted(): void
    {
        static::updated(function (InventoryItem $item) {
            if (! $item->wasChanged('quantity_available')) {
                return;
            }

            $oldStatus = self::computeStatus(
                (int) $item->getOriginal('quantity_available'),
                $item->low_stock_threshold
            );
            $newStatus = $item->status;

            if ($oldStatus === $newStatus) {
                return;
            }

            if ($newStatus === 'Out of Stock') {
                $item->notifyAdmins(fn (NotificationService $service, User $admin) =>
                    $service->notifyOutOfStock($admin, $item->item_name));
            } elseif ($newStatus === 'Low Stock') {
                $item->notifyAdmins(fn (NotificationService $service, User $admin) =>
                    $service->notifyLowStock($admin, $item->item_name, $item->quantity_available));
            }
        });
    }

    protected function notifyAdmins(\Closure $send): void
    {
        $service = app(NotificationService::class);

        User::where('role', 'admin')->get()->each(function (User $admin) use ($service, $send) {
            $send($service, $admin);
        });
    }
}
