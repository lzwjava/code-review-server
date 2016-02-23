<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午2:22
 */
class Reviews extends BaseController
{

    public $mailgunService;

    public $tagDao;
    public $reviewDao;
    public $orderDao;
    public $leanCloud;

    function __construct()
    {
        parent::__construct();

        $this->load->library('MailgunService');
        $this->mailgunService = new MailgunService();

        $this->load->library('LeanCloud');
        $this->leanCloud = new LeanCloud();

        $this->load->model('tagDao');
        $this->tagDao = new TagDao();
        $this->load->model('reviewDao');
        $this->reviewDao = new ReviewDao();
        $this->load->model('orderDao');
        $this->orderDao = new OrderDao();
    }

    public function add_post()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_ORDER_ID,
            KEY_CONTENT, KEY_TITLE))
        ) {
            return;
        }
        $orderId = $_POST[KEY_ORDER_ID];
        $title = $_POST[KEY_TITLE];
        $content = $_POST[KEY_CONTENT];
        $order = $this->orderDao->getOrder($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "找不到相应的 review 订单");
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($user->id != $order->reviewerId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, "当前登录的用户不是该 review 指定的大神");
            return;
        }
        if ($order->status == ORDER_STATUS_FINISHED) {
            $this->failure(ERROR_ALREADY_DO_IT, "已经填写过 Review 了, 请编辑相应的 Review");
            return;
        }
        if (strlen($title) <= 0) {
            $this->failure(ERROR_PARAMETER_ILLEGAL, "标题长度应该大于 0");
            return;
        }
        $this->db->trans_start();
        $insertId = $this->reviewDao->add($orderId, $title, $content);
        $this->orderDao->updateStatus($orderId, ORDER_STATUS_FINISHED);
        $this->db->trans_complete();
        $review = $this->reviewDao->getOne($insertId);
        $this->notifyReviewFinish($order->learner, $order->reviewer, $review->reviewId);
        $this->succeed($review);
    }

    private function notifyReviewFinish($learner, $reviewer, $reviewId)
    {
        $reviewUrl = 'http://reviewcode.cn/article.html?reviewId=' . $reviewId;
        $data = array(
            SMS_LEARNER => $learner->username,
            SMS_REVIEWER => $reviewer->username,
            SMS_REVIEW_URL => $reviewUrl
        );
        $user = $this->userDao->findUserById($learner->id);
        $this->leanCloud->sendTemplateSms($user->mobilePhoneNumber, 'ReviewFinish', $data);
    }

    function email_get()
    {
        $this->mailgunService->sendMessage('Subject', 'text');
        $this->succeed();
    }

    public function update_patch($reviewId)
    {
        $keys = array(KEY_CONTENT, KEY_TITLE);
        if ($this->checkIfNotAtLeastOneParam($this->patch(), $keys)) {
            return;
        }
        $data = $this->patchParams($keys);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $review = $this->reviewDao->getOne($reviewId);
        if ($review == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "找不到相应的 Review");
            return;
        }
        $orderId = $review->orderId;
        $order = $this->orderDao->getOrder($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "找不到相应的 review 订单");
            return;
        }
        if ($user->id != $order->reviewerId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, "当前编辑的用户不是该 Review 指定的大神");
            return;
        }
        $this->reviewDao->update($reviewId, $data);
        $this->succeed($this->reviewDao->getOne($reviewId));
    }

    public function allReviews_get()
    {
        $displaying = 1;
        if (isset($_GET[KEY_DISPLAYING])) {
            $displaying = $_GET[KEY_DISPLAYING];
        }
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        $reviews = $this->reviewDao->getDisplayingReviews($displaying, $skip, $limit);
        $count = $this->reviewDao->countReviews($displaying);
        $this->succeed($reviews, $count);
    }

    public function view_get($reviewId)
    {
        $review = $this->reviewDao->getOne($reviewId);
        $this->succeed($review);
    }

    public function viewByOrder_get($orderId)
    {
        $review = $this->reviewDao->getOneByOrderId($orderId);
        $this->succeed($review);
    }

    public function userReviews_get($reviewerId)
    {
        $reviews = $this->reviewDao->getListForReviewer($reviewerId,
            $this->getSkip(), $this->getLimit());
        $this->succeed($reviews);
    }

    public function addTag_post($reviewId)
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_TAG_ID))) {
            return;
        }
        $tagId = $this->post(KEY_TAG_ID);
        $this->tagDao->addReviewTag($reviewId, $tagId);
        $this->succeed($this->tagDao->getReviewTags($reviewId));
    }

    public function removeTag_delete($reviewId, $tagId)
    {
        $this->tagDao->removeReviewTag($reviewId, $tagId);
        $this->succeed($this->tagDao->getReviewTags($reviewId));
    }

    public function adminUpdate_patch($reviewId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $keys = array(KEY_DISPLAYING, KEY_COVER_URL);
        if ($this->checkIfParamsNotExist($this->patch(),
            $keys)
        ) {
            return;
        }
        $data = $this->patchParams($keys);
        $displaying = intval($data[KEY_DISPLAYING]);
        if ($displaying !== 0 && $displaying !== 1) {
            $this->failure(ERROR_PARAMETER_ILLEGAL, 'displaying 值非法');
            return;
        }
        $ok = $this->reviewDao->update($reviewId, $data);
        if ($ok) {
            $this->succeed();
        } else {
            $this->failure(ERROR_RUN_SQL_FAILED, 'run sql failed');
        }
    }

}
