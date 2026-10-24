<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Appointment;
use App\Models\Booking;
use App\Models\User;
use App\Mail\WorkflowNotificationMail;
use App\Support\PhilippineTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function notify(User $user, string $message, string $type = 'general', bool $sendSms = true, ?Appointment $appointment = null, ?Booking $booking = null): Notification
    {
        $unique = [
            'user_id' => $user->id,
            'notification_type' => $type,
        ];

        if ($booking || $appointment) {
            $unique['booking_id'] = $booking?->id;
            $unique['appointment_id'] = $appointment?->id;
            $notification = Notification::firstOrCreate($unique, [
                'message' => $message,
                'status' => 'unread',
            ]);
        } else {
            $notification = Notification::create(array_merge($unique, [
                'message' => $message,
                'status' => 'unread',
            ]));
        }

        if ($notification->wasRecentlyCreated && $sendSms && $user->phone) {
            try {
                $this->smsService->send($user->phone, $message);
            } catch (\Throwable $exception) {
                Log::warning('Workflow notification SMS delivery failed.', [
                    'user_id' => $user->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return $notification;
    }

    /**
     * Booking notifications name the event itself (e.g. "Year End Party",
     * "Birthday Celebration") rather than a raw database id like "#39" —
     * clients don't know or care what a booking's numeric id is; the event
     * type is what they actually recognize.
     */
    public function notifyBookingSubmitted(User $clientUser, Booking $booking): void
    {
        $this->notify(
            $clientUser,
            'Your ' . $booking->event_type . ' booking request has been submitted successfully. We will review it shortly.',
            'booking_submitted', true, null, $booking
        );

        $adminUrl = secure_url(route('admin.bookings.show', $booking, false));
        $details = [
            'Client' => $clientUser->name,
            'Booking reference' => '#'.$booking->id,
            'Event type' => $booking->event_type,
            'Package' => $booking->package?->package_name ?? 'No package selected',
            'Event date' => $booking->event_date->format('F j, Y'),
            'Submitted' => PhilippineTime::format($booking->created_at),
            'Status' => 'Pending',
        ];

        $this->notifyAdmins(
            $booking,
            null,
            'New booking request from '.$clientUser->name.'.',
            'booking_submitted',
            'New Event Booking Request – Team U Creations',
            'New Event Booking Request',
            'A client has submitted an event booking request.',
            $details,
            $adminUrl,
            'View Booking',
        );
    }

    public function notifyBookingApproved(Booking $booking): void
    {
        $clientUser = $booking->client->user;
        $notification = $this->notify(
            $clientUser,
            'Great news! Your '.$booking->event_type.' booking has been approved.',
            'booking_approved', true, null, $booking
        );
        if (! $notification->wasRecentlyCreated) {
            return;
        }
        $this->sendEmail(
            $clientUser,
            'Event Booking Approved – Team U Creations',
            'Event Booking Approved',
            'Your event booking has been approved.',
            ['Booking reference' => '#'.$booking->id, 'Event type' => $booking->event_type, 'Event date' => $booking->event_date->format('F j, Y'), 'Status' => 'Approved'],
            secure_url(route('client.bookings.show', $booking, false)),
            'View Booking',
        );
    }

    public function notifyBookingDeclined(Booking $booking): void
    {
        $clientUser = $booking->client->user;
        $notification = $this->notify(
            $clientUser,
            'Your '.$booking->event_type.' booking request has been declined.',
            'booking_declined', true, null, $booking
        );
        if (! $notification->wasRecentlyCreated) {
            return;
        }
        $this->sendEmail(
            $clientUser,
            'Event Booking Declined – Team U Creations',
            'Event Booking Declined',
            'We are unable to approve your event booking request.',
            ['Booking reference' => '#'.$booking->id, 'Event type' => $booking->event_type, 'Event date' => $booking->event_date->format('F j, Y'), 'Status' => 'Declined'],
            secure_url(route('client.bookings.show', $booking, false)),
            'View Booking',
        );
    }

    public function notifyBookingRescheduled(Booking $booking, string $previousDate): void
    {
        $clientUser = $booking->client->user;
        $notification = $this->notify(
            $clientUser,
            'Your '.$booking->event_type.' booking has been rescheduled to '.$booking->event_date->format('F j, Y').'.',
            'booking_rescheduled', true, null, $booking
        );
        if (! $notification->wasRecentlyCreated) {
            return;
        }
        $this->sendEmail(
            $clientUser,
            'Event Booking Rescheduled – Team U Creations',
            'Event Booking Rescheduled',
            'Your event booking schedule has changed.',
            ['Booking reference' => '#'.$booking->id, 'Event type' => $booking->event_type, 'Previous event date' => $previousDate, 'Updated event date' => $booking->event_date->format('F j, Y'), 'Status' => 'Rescheduled'],
            secure_url(route('client.bookings.show', $booking, false)),
            'View Booking',
        );
    }

    public function notifyAppointmentSubmitted(User $clientUser, Appointment $appointment): void
    {
        $this->notify(
            $clientUser,
            'Your appointment request has been received and is awaiting confirmation.',
            'appointment_submitted',
            true,
            $appointment
        );

        $this->notifyAdmins(
            null,
            $appointment,
            'New appointment request from '.$clientUser->name.'.',
            'appointment_submitted',
            'New Appointment Request – Team U Creations',
            'New Appointment Request',
            'A client has submitted an appointment request.',
            ['Client' => $clientUser->name, 'Appointment reference' => '#'.$appointment->id, 'Appointment date' => $appointment->appointment_date->format('F j, Y'), 'Appointment time' => $this->formatTime($appointment->appointment_time), 'Purpose' => $appointment->purpose, 'Status' => 'Pending', 'Submitted' => PhilippineTime::format($appointment->created_at)],
            secure_url(route('admin.appointments.show', $appointment, false)),
            'View Appointment',
        );
    }

    public function notifyAppointmentConfirmed(User $clientUser, Appointment $appointment): void
    {
        $notification = $this->notify(
            $clientUser,
            'Your appointment has been confirmed. Please check the appointment details.',
            'appointment_confirmed',
            true,
            $appointment
        );
        if (! $notification->wasRecentlyCreated) {
            return;
        }
        $this->sendEmail(
            $clientUser,
            'Appointment Approved – Team U Creations',
            'Appointment Approved',
            'Your appointment request has been approved.',
            $this->appointmentDetails($appointment, 'Confirmed'),
            secure_url(route('client.appointments.show', $appointment, false)),
            'View Appointment',
        );
    }

    public function notifyAppointmentDeclined(User $clientUser, Appointment $appointment): void
    {
        $notification = $this->notify(
            $clientUser,
            'Your appointment request has been declined. Please contact us for more information.',
            'appointment_declined',
            true,
            $appointment
        );
        if (! $notification->wasRecentlyCreated) {
            return;
        }
        $this->sendEmail(
            $clientUser,
            'Appointment Declined – Team U Creations',
            'Appointment Declined',
            'We are unable to approve your appointment request.',
            $this->appointmentDetails($appointment, 'Declined'),
            secure_url(route('client.appointments.show', $appointment, false)),
            'View Appointment',
        );
    }

    public function notifyAppointmentRescheduled(User $clientUser, Appointment $appointment, string $previousSchedule): void
    {
        $notification = $this->notify(
            $clientUser,
            'Your appointment has been rescheduled. Please check the updated appointment details.',
            'appointment_rescheduled',
            true,
            $appointment
        );
        if (! $notification->wasRecentlyCreated) {
            return;
        }
        $this->sendEmail(
            $clientUser,
            'Appointment Rescheduled – Team U Creations',
            'Appointment Rescheduled',
            'Your appointment has been moved to a new schedule.',
            array_merge(['Previous schedule' => $previousSchedule], $this->appointmentDetails($appointment, 'Rescheduled')),
            secure_url(route('client.appointments.show', $appointment, false)),
            'View Appointment',
        );
    }

    private function notifyAdmins(?Booking $booking, ?Appointment $appointment, string $message, string $type, string $subject, string $heading, string $intro, array $details, string $url, string $actionLabel): void
    {
        foreach (User::where('role', 'admin')->get() as $admin) {
            $notification = $this->notify($admin, $message, $type, false, $appointment, $booking);
            if ($notification->wasRecentlyCreated) {
                $this->sendEmail($admin, $subject, $heading, $intro, $details, $url, $actionLabel);
            }
        }
    }

    private function sendEmail(User $user, string $subject, string $heading, string $intro, array $details, string $url, string $actionLabel): void
    {
        if (! $user->email) {
            Log::warning('Workflow notification email was not sent because the recipient has no email address.', [
                'user_id' => $user->id,
                'subject' => $subject,
            ]);

            return;
        }

        try {
            Mail::mailer('smtp')
                ->to($user->email)
                ->send(new WorkflowNotificationMail($subject, $heading, $intro, $details, $url, $actionLabel));
        } catch (\Throwable $exception) {
            Log::error('Workflow notification email delivery failed.', [
                'user_id' => $user->id,
                'subject' => $subject,
                'mailer' => 'smtp',
                'exception' => $exception::class,
            ]);
        }
    }

    private function appointmentDetails(Appointment $appointment, string $status): array
    {
        return [
            'Appointment reference' => '#'.$appointment->id,
            'Purpose' => $appointment->purpose,
            'Appointment date' => $appointment->appointment_date->format('F j, Y'),
            'Appointment time' => $this->formatTime($appointment->appointment_time),
            'Status' => $status,
        ];
    }

    private function formatTime(string $time): string
    {
        return \Carbon\Carbon::parse($time, config('app.display_timezone', 'Asia/Manila'))->format('g:i A');
    }

    public function notifyAppointmentCompleted(User $clientUser, Appointment $appointment): void
    {
        $this->notify(
            $clientUser,
            'Your appointment has been marked as completed.',
            'appointment_completed',
            true,
            $appointment
        );
    }

    public function notifyAppointmentCancelled(User $clientUser, Appointment $appointment): void
    {
        $this->notify(
            $clientUser,
            'Your appointment has been cancelled.',
            'appointment_cancelled',
            true,
            $appointment
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