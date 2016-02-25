<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/25
 * Time: 上午1:43
 */
class Notifications extends BaseController
{
    public $notificationDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('NotificationDao');
        $this->notificationDao = new NotificationDao();
    }

    function list_get()
    {
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $unread = $this->get(KEY_UNREAD);
        $res = $this->notificationDao->getMyNotifications($user->id, $unread, $skip, $limit);
        $this->succeed($res);
    }

    function count_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $unread = $this->notificationDao->countUnread($user->id);
        $this->succeed(array(KEY_COUNT => $unread));
    }

    function markAsRead_patch($notificationId = null)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($notificationId) {
            $ok = $this->notificationDao->markOneAsRead($notificationId);
        } else {
            $ok = $this->notificationDao->markAsAllRead($user->id);
        }
        $this->responseBySQLRes($ok);
    }

}
