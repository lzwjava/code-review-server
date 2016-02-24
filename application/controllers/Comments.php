<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/10
 * Time: 下午3:50
 */
class Comments extends BaseController
{
    public $commentDao;
    public $reviewDao;
    public $orderDao;
    public $notificationDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('CommentDao');
        $this->load->model('OrderDao');
        $this->load->model('ReviewDao');
        $this->load->model('NotificationDao');
        $this->commentDao = new CommentDao();
        $this->reviewDao = new ReviewDao();
        $this->orderDao = new OrderDao();
        $this->notificationDao = new NotificationDao();
    }

    function create_post($reviewId)
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_CONTENT))) {
            return;
        }
        $content = $this->post(KEY_CONTENT);
        $parentId = $this->post(KEY_PARENT_ID);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $commentId = $this->commentDao->addComment($reviewId, $parentId, $content, $user->id);
        $this->addNotifications($commentId, $reviewId, $user);
        $this->succeed(array(KEY_COMMENT_ID => $commentId));
    }

    private function addNotifications($commentId, $reviewId, $author)
    {
        $order = $this->orderDao->getOrderByReviewId($reviewId);
        if ($author->id != $order->learnerId) {
            $this->notificationDao->addNotification($order->learnerId,
                TYPE_COMMENT, $commentId);
        }
        if ($author->id != $order->reviewerId) {
            $this->notificationDao->addNotification($order->reviewerId,
                TYPE_COMMENT, $commentId);
        }
    }

    function list_get($reviewId)
    {
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        $comments = $this->commentDao->getComments($reviewId, $skip, $limit);
        $this->succeed($comments);
    }

}