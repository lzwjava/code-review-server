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
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('commentDao');
        $this->load->model('OrderDao');
        $this->load->model('UserDao');
        $this->commentDao = new CommentDao();
        $this->orderDao = new OrderDao();
        $this->userDao = new UserDao();
    }

    private function addNotification($userId, $type, $senderId, $commentId = null,
                                     $orderId = null, $text = null)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_TYPE => $type,
            KEY_SENDER_ID => $senderId);
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
            KEY_TYPE, KEY_SENDER_ID, KEY_COMMENT_ID,
            KEY_ORDER_ID, KEY_TYPE, KEY_CREATED), $prefixTableName);
    }

    function getMyNotifications($userId, $unread = null, $skip = 0, $limit = 100)
    {
        $fields = $this->publicFields('n');
        if ($unread !== null) {
            $unreadSql = 'and unread=' . $unread;
        } else {
            $unreadSql = '';
        }
        $sql = "select $fields,
                c.commentId, c.content,c.reviewId,c.authorId,c.parentId,
                o.orderId,o.gitHubUrl,o.amount,o.remark,
                u.id,u.username,u.avatarUrl
                from notifications as n
                left join comments as c USING(commentId)
                left join orders as o USING(orderId)
                left join users as u on u.id=n.senderId
                where userId=? $unreadSql
                order by n.created desc
                LIMIT $limit offset $skip";
        $binds = array($userId);
        $result = $this->db->query($sql, $binds)->result();
        $this->handleList($result);
        return $result;
    }

    function countUnread($userId)
    {
        $sql = "SELECT count(*) AS cnt FROM notifications WHERE userId=? AND unread=1";
        $binds = array($userId);
        $res = $this->db->query($sql, $binds)->row();
        return $res->cnt;
    }

    private function handleList($notifications)
    {
        foreach ($notifications as $notification) {
            $notification->sender = extractFields($notification,
                array(KEY_ID, KEY_USERNAME, KEY_AVATAR_URL));
            $commentFields = array(KEY_COMMENT_ID, KEY_CONTENT, KEY_REVIEW_ID, KEY_AUTHOR_ID, KEY_PARENT_ID);
            $notification->comment = extractFields($notification,
                $commentFields);
            $orderFields = array(KEY_ORDER_ID, KEY_AMOUNT, KEY_REMARK, KEY_GITHUB_URL);
            $notification->order = extractFields($notification, $orderFields);
            unset($notification->senderId);
            unset($notification->orderId);
            unset($notification->commentId);
            unset($notification->userId);
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
                TYPE_COMMENT, $author->id, $commentId);
        }
        if ($author->id != $order->reviewerId) {
            $this->addNotification($order->reviewerId,
                TYPE_COMMENT, $author->id, $commentId);
        }
    }

    function notifyNewOrder($order)
    {
        $this->addNotification($order->reviewerId,
            TYPE_NEW_ORDER, $order->learnerId, null, $order->orderId);
    }

    function notifyOrderFinish($order)
    {
        $senderId = $order->reviewerId;
        $this->addNotification($order->learnerId, TYPE_FINISH_ORDER,
            $senderId, null, $order->orderId);
    }

    function notifyAgree($userId)
    {
        $admin = $this->userDao->adminUser();
        $this->addNotification($userId, TYPE_AGREE,
            $admin->id, null, null, '您已通过审核, 欢迎成为审阅者的一员.');
    }
}
