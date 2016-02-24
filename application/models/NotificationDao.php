<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/25
 * Time: 上午1:07
 */
class NotificationDao extends BaseDao
{
    public $commentDao;
    public $orderDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('commentDao');
        $this->load->model('OrderDao');
        $this->commentDao = new CommentDao();
        $this->orderDao = new OrderDao();
    }

    private function addNotification($userId, $type, $commentId = null,
                                     $orderId = null, $text = null)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_TYPE => $type);
        if ($commentId) {
            $data[KEY_COMMENT_ID] = $commentId;
        }
        if ($orderId) {
            $data[KEY_ORDER_ID] = $orderId;
        }
        if ($text) {
            $data[KEY_TEXT] = $text;
        }
        $this->db->insert(TABLE_NOTIFICATIONS, $data);
        return $this->db->insert_id();
    }

    private function publicFields($prefixTableName = TABLE_NOTIFICATIONS)
    {
        return $this->mergeFields(array(
            KEY_NOTIFICATION_ID, KEY_USER_ID, KEY_UNREAD,
            KEY_TYPE, KEY_COMMENT_ID, KEY_ORDER_ID, KEY_TYPE), $prefixTableName);
    }

    function getMyNotifications($userId, $unread = null, $skip = 0, $limit = 100)
    {
        $fields = $this->publicFields('n');
        if ($unread) {
            $unreadSql = 'and unread=' . $unread;
        } else {
            $unreadSql = '';
        }
        $sql = "select $fields,
                c.commentId, c.content,c.reviewId,c.authorId,c.created,c.parentId,
                u.id,u.username,u.avatarUrl
                from notifications as n
                left join comments as c USING(commentId)
                left join orders as o USING(orderId)
                left join users as u on c.authorId = u.id
                where userId=? $unreadSql
                LIMIT $limit offset $skip";
        $binds = array($userId);
        $result = $this->db->query($sql, $binds)->result();
        $this->handleList($result);
        return $result;
    }

    private function handleList($notifications)
    {
        foreach ($notifications as $notification) {
            $notification->comment = extractFields($notification,
                $this->commentDao->fields());
            $notification->comment->author = extractFields($notification,
                array(KEY_ID, KEY_USERNAME, KEY_AVATAR_URL));
        }
    }

    function markAsAllRead($userId)
    {
        return $this->markAsRead($userId, null);
    }

    function markOneAsRead($id)
    {
        return $this->markAsRead(null, $id);
    }

    private function markAsRead($userId, $notificationId)
    {
        $data = array(KEY_UNREAD => 0);
        if ($notificationId) {
            $this->db->where(KEY_NOTIFICATION_ID, $notificationId);
            return $this->db->update(TABLE_NOTIFICATIONS, $data);
        } else {
            $this->db->where(KEY_USER_ID, $userId);
            $this->db->where(KEY_UNREAD, 1);
            return $this->db->update(TABLE_NOTIFICATIONS, $data);
        }
    }

    function notifyNewComment($commentId, $reviewId, $author)
    {
        $order = $this->orderDao->getOrderByReviewId($reviewId);
        if ($author->id != $order->learnerId) {
            $this->addNotification($order->learnerId,
                TYPE_COMMENT, $commentId);
        }
        if ($author->id != $order->reviewerId) {
            $this->addNotification($order->reviewerId,
                TYPE_COMMENT, $commentId);
        }
    }

    function notifyNewOrder($order)
    {
        $this->addNotification($order->reviewerId,
            TYPE_NEW_ORDER, null, $order->orderId);
    }

    function notifyOrderFinish($order)
    {
        $this->addNotification($order->learnerId, TYPE_FINISH_ORDER, null, $order->orderId);
    }

    function notifyAgree($userId)
    {
        $this->addNotification($userId, TYPE_AGREE, null, null, '您已通过审核, 欢迎成为审阅者的一员.');
    }
}
