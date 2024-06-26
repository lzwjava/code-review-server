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
    public $notificationDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('CommentDao');
        $this->load->model('NotificationDao');
        $this->commentDao = new CommentDao();
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
        $parent = null;
        if ($parentId) {
            $parent = $this->commentDao->getComment($parentId);
            if ($this->checkIfObjectNotExists($parent)) {
                return;
            }
        }
        $commentId = $this->commentDao->addComment($reviewId, $parentId, $content, $user->id);
        $this->notificationDao->notifyNewComment($commentId, $reviewId, $user, $parent);
        $this->succeed(array(KEY_COMMENT_ID => $commentId));
    }

    function list_get($reviewId)
    {
        $skip = $this->skip();
        $limit = $this->limit();
        $comments = $this->commentDao->getComments($reviewId, $skip, $limit);
        $this->succeed($comments);
    }

}