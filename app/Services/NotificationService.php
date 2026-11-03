<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserContact;
use App\Models\Notification;
use App\Models\DispatchOrder;
use App\Models\PickupRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to all stakeholders
     */
    public static function notifyDispatchCreated($dispatch, $additionalRecipients = [])
    {
        $orderNumber = $dispatch->dispatch_number ?? 'DSP-' . $dispatch->id;
        $trackingUrl = url('/dispatch/' . $dispatch->id);
        
        // Get client
        $client = User::find($dispatch->client_id);
        
        // Collect all recipients
        $recipients = self::getAllRecipients($client, $additionalRecipients);
        
        // Email data
        $emailData = [
            'recipientName' => '',
            'dispatchNumber' => $orderNumber,
            'pickupAddress' => $dispatch->pickup_address,
            'deliveryAddress' => $dispatch->delivery_address ?? 'Multiple stops',
            'distance' => $dispatch->distance_km ?? 0,
            'amount' => $dispatch->base_price ?? 0,
            'driverName' => $dispatch->driver->name ?? null,
            'driverPhone' => $dispatch->driver->phone ?? null,
            'trackingUrl' => $trackingUrl,
        ];
        
        foreach ($recipients as $recipient) {
            $emailData['recipientName'] = $recipient['name'];
            
            self::sendEmail(
                $recipient['email'],
                $recipient['name'],
                'New Dispatch Order Created - ' . $orderNumber,
                'emails.dispatch-created',
                $emailData,
                'dispatch_created',
                $dispatch->id,
                null,
                $recipient['user_id']
            );
        }
        
        Log::info('Dispatch notification sent for order: ' . $orderNumber);
    }
    
    /**
     * Send notification for pickup request
     */
    public static function notifyPickupCreated($pickup, $additionalRecipients = [])
    {
        $pickupNumber = $pickup->pickup_number ?? 'PUP-' . $pickup->id;
        $trackingUrl = url('/pickup/' . $pickup->id);
        
        $client = User::find($pickup->client_id);
        
        $recipients = self::getAllRecipients($client, $additionalRecipients);
        
        $emailData = [
            'recipientName' => '',
            'pickupNumber' => $pickupNumber,
            'destinationAddress' => $pickup->destination_address,
            'stopCount' => $pickup->pickupStops->count(),
            'totalBoxes' => $pickup->total_boxes ?? 0,
            'distance' => $pickup->distance_km ?? 0,
            'amount' => $pickup->base_price ?? 0,
            'trackingUrl' => $trackingUrl,
        ];
        
        foreach ($recipients as $recipient) {
            $emailData['recipientName'] = $recipient['name'];
            
            self::sendEmail(
                $recipient['email'],
                $recipient['name'],
                'New Pickup Request Created - ' . $pickupNumber,
                'emails.pickup-created',
                $emailData,
                'pickup_created',
                null,
                $pickup->id,
                $recipient['user_id']
            );
        }
        
        Log::info('Pickup notification sent for request: ' . $pickupNumber);
    }
    
    /**
     * Send status update notification
     */
    public static function notifyStatusUpdate($order, $type, $oldStatus, $newStatus)
    {
        $statusConfig = self::getStatusConfig($newStatus);
        
        if ($type == 'dispatch') {
            $orderNumber = $order->dispatch_number ?? 'DSP-' . $order->id;
            $trackingUrl = url('/dispatch/' . $order->id);
            $client = User::find($order->client_id);
        } else {
            $orderNumber = $order->pickup_number ?? 'PUP-' . $order->id;
            $trackingUrl = url('/pickup/' . $order->id);
            $client = User::find($order->client_id);
        }
        
        $recipients = self::getAllRecipients($client);
        
        $emailData = [
            'recipientName' => '',
            'type' => $type == 'dispatch' ? 'Dispatch' : 'Pickup',
            'orderNumber' => $orderNumber,
            'statusText' => $statusConfig['text'],
            'statusMessage' => $statusConfig['message'],
            'statusIcon' => $statusConfig['icon'],
            'statusColor' => $statusConfig['color'],
            'driverName' => $order->driver->name ?? null,
            'driverPhone' => $order->driver->phone ?? null,
            'estimatedArrival' => $statusConfig['estimated_arrival'] ?? null,
            'trackingUrl' => $trackingUrl,
        ];
        
        foreach ($recipients as $recipient) {
            $emailData['recipientName'] = $recipient['name'];
            
            self::sendEmail(
                $recipient['email'],
                $recipient['name'],
                'Status Update - ' . $orderNumber . ' is ' . $statusConfig['text'],
                'emails.status-updated',
                $emailData,
                'status_updated',
                $type == 'dispatch' ? $order->id : null,
                $type == 'pickup' ? $order->id : null,
                $recipient['user_id']
            );
        }
    }
    
    /**
     * Get all recipients (primary user + additional contacts)
     */
    private static function getAllRecipients($user, $additional = [])
    {
        $recipients = [];
        
        // Add primary user
        if ($user && $user->email) {
            $recipients[] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => 'primary'
            ];
        }
        
        // Add additional contacts
        if ($user) {
            $contacts = UserContact::where('user_id', $user->id)
                ->where('receive_emails', true)
                ->get();
            
            foreach ($contacts as $contact) {
                if ($contact->email) {
                    $recipients[] = [
                        'user_id' => $user->id,
                        'name' => $contact->name,
                        'email' => $contact->email,
                        'type' => 'contact'
                    ];
                }
            }
        }
        
        // Add additional recipients from request
        foreach ($additional as $extra) {
            if (isset($extra['email']) && $extra['email']) {
                $recipients[] = [
                    'user_id' => null,
                    'name' => $extra['name'] ?? 'User',
                    'email' => $extra['email'],
                    'type' => 'additional'
                ];
            }
        }
        
        return $recipients;
    }
    
    /**
     * Send email via Mailtrap/SMTP
     */
    private static function sendEmail($to, $name, $subject, $view, $data, $notificationType, $dispatchId, $pickupId, $userId)
    {
        try {
            Mail::send($view, $data, function ($message) use ($to, $name, $subject) {
                $message->to($to, $name)
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            if ($userId) {
                Notification::create([
                    'notification_number' => 'NOT-' . date('Ymd') . '-' . uniqid(),
                    'type' => $notificationType,
                    'title' => $subject,
                    'dispatch_order_id' => $dispatchId,
                    'pickup_request_id' => $pickupId,
                    'user_id' => $userId,
                    'recipient_email' => $to,
                    'recipient_name' => $name,
                    'subject' => $subject,
                    'message' => json_encode($data),
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
            
            if ($userId) {
                Notification::create([
                    'notification_number' => 'NOT-' . date('Ymd') . '-' . uniqid(),
                    'type' => $notificationType,
                    'title' => $subject,
                    'dispatch_order_id' => $dispatchId,
                    'pickup_request_id' => $pickupId,
                    'user_id' => $userId,
                    'recipient_email' => $to,
                    'recipient_name' => $name,
                    'subject' => $subject,
                    'message' => json_encode($data),
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }
    
    /**
     * Get status configuration for animated emails
     */
    private static function getStatusConfig($status)
    {
        $configs = [
            'pending' => [
                'text' => 'Pending',
                'message' => 'Your order has been created and is waiting for driver assignment.',
                'icon' => '⏳',
                'color' => '#d97706',
            ],
            'assigned' => [
                'text' => 'Driver Assigned',
                'message' => 'A driver has been assigned to your order.',
                'icon' => '👨‍✈️',
                'color' => '#2563eb',
            ],
            'picked_up' => [
                'text' => 'Picked Up',
                'message' => 'Your items have been picked up and are on the way.',
                'icon' => '📦',
                'color' => '#4338ca',
            ],
            'on_the_way' => [
                'text' => 'On The Way',
                'message' => 'Your delivery is in transit.',
                'icon' => '🚚',
                'color' => '#ea580c',
            ],
            'delivered' => [
                'text' => 'Delivered',
                'message' => 'Your order has been successfully delivered!',
                'icon' => '✅',
                'color' => '#059669',
            ],
            'completed' => [
                'text' => 'Completed',
                'message' => 'Your pickup request has been completed.',
                'icon' => '🎉',
                'color' => '#059669',
            ],
        ];
        
        return $configs[$status] ?? $configs['pending'];
    }

/**
 * Send notification for warehouse approval
 */
public static function notifyWarehouseApproved($warehouse)
{
    $owner = User::find($warehouse->owner_id);
    
    if (!$owner || !$owner->email) return;
    
    $emailData = [
        'recipientName' => $owner->name,
        'warehouseName' => $warehouse->name,
        'warehouseLocation' => $warehouse->location,
        'status' => 'approved',
        'dashboardUrl' => url('/warehouses'),
    ];
    
    self::sendEmail(
        $owner->email,
        $owner->name,
        'Your Warehouse Has Been Approved - ' . $warehouse->name,
        'emails.warehouse-approved',
        $emailData,
        'warehouse_approved',
        null,
        null,
        $owner->id
    );
}
}
