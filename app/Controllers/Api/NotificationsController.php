<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\NotificationService;

class NotificationsController extends BaseController
{
    protected $notificationService;
    
    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function getAll() {
        if (!auth()->user()) {
            return $this->response->setJSON(['status' => 'error', 'message' => "User was not logged in."]);
        }

        $notifications = $this->notificationService->getNotifications(auth()->user()->id);

        $newList = [];
        if ($notifications) {
            foreach ($notifications as $notification) {
                $notification['link'] = base_url($notification['link']);
                $notification['created_by'] = $notification['created_by'] ?? 'Guest';
                $notification['created_at'] = convert_to_user_timezone($notification['created_at'], user_timezone(auth()->user()->id));

                $newList[] = $notification;
            }
        }

        return $this->response->setJSON(['status' => 'success', 'notifications' => $newList]);
    }

    /**
     * Mark a notification as read.
     *
     * @param int $id Notification ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function markAsRead($id)
    {
        if ($this->notificationService->markAsRead($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Notification marked as read']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to mark notification as read']);
    }

    /**
     * Delete a notification.
     *
     * @param int $id Notification ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        if ($this->notificationService->deleteNotification($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Notification deleted']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete notification']);
    }

    public function clear()
    {
        $user_id = auth()->user() ? auth()->user()->id : 0;
        if ($this->notificationService->clearNotifications($user_id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'All notifications were deleted.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to clear the notifications.']);
    }
}