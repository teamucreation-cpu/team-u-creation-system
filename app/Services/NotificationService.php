<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function notify(User $user, string $message, string $type = 'general', bool $sendSms = true): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'message' => $message,
            'notification_type' => $type,
            'status' => 'unread',
        ]);

        if ($sendSms && $user->phone) {
            $this->smsService->send($user->phone, $message);
        }

        return $notification;
    }

    /**
     * Booking notifications name the event itself (e.g. "Year End Party",
     * "Birthday Celebration") rather than a raw database id like "#39" —
     * clients don't know or care what a booking's numeric id is; the event
     * type is what they actually recognize.
     */
    public function notifyBookingSubmitted(User $clientUser, string $eventType): void
    {
        $this->notify(
            $clientUser,
            'Your ' . $eventType . ' booking request has been submitted successfully. We will review it shortly.',
            'booking_submitted'
        );
    }

    public function notifyBookingApproved(User $clientUser, string $eventType): void
    {
        $this->notify(
            $clientUser,
            'Great news! Your ' . $eventType . ' booking has been approved. You may now view the event details in your calendar.',
            'booking_approved'
        );
    }

    public function notifyBookingDeclined(User $clientUser, string $eventType): void
    {
        $this->notify(
            $clientUser,
            'Your ' . $eventType . ' booking request has been declined. Please contact us for more information.',
            'booking_declined'
        );
    }

    public function notifyBookingRescheduled(User $clientUser, string $eventType, string $newDate): void
    {
        $this->notify(
            $clientUser,
            'Your ' . $eventType . ' booking has been rescheduled to ' . $newDate . '.',
            'booking_rescheduled'
        );
    }

    public function notifyAppointmentSubmitted(User $clientUser): void
    {
        $this->notify(
            $clientUser,
            'Your appointment request has been received and is awaiting confirmation.',
            'appointment_submitted'
        );
    }

    public function notifyAppointmentConfirmed(User $clientUser): void
    {
        $this->notify(
            $clientUser,
            'Your appointment has been confirmed. Please check the appointment details.',
            'appointment_confirmed'
        );
    }

    public function notifyAppointmentDeclined(User $clientUser): void
    {
        $this->notify(
            $clientUser,
            'Your appointment request has been declined. Please contact us for more information.',
            'appointment_declined'
        );
    }

    public function notifyAppointmentRescheduled(User $clientUser): void
    {
        $this->notify(
            $clientUser,
            'Your appointment has been rescheduled. Please check the updated appointment details.',
            'appointment_rescheduled'
        );
    }

    public function notifyLowStock(User $adminUser, string $itemName, int $quantityAvailable): void
    {
        $this->notify(
            $adminUser,
            'Low stock alert: "' . $itemName . '" has only ' . $quantityAvailable . ' unit(s) available.',
            'inventory_low_stock'
        );
    }

    public function notifyOutOfStock(User $adminUser, string $itemName): void
    {
        $this->notify(
            $adminUser,
            'Out of stock alert: "' . $itemName . '" has no units available.',
            'inventory_out_of_stock'
        );
    }

    public function notifyReviewApproved(User $clientUser): void
    {
        $this->notify(
            $clientUser,
            'Thank you! Your review has been approved and is now featured on our website.',
            'review_approved'
        );
    }
}