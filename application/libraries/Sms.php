<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/25
 * Time: 上午3:49
 */
class Sms extends CI_Model
{
    public $leanCloud;
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->library('LeanCloud');
        $this->load->model('UserDao');
        $this->leanCloud = new LeanCloud();
        $this->userDao = new UserDao();
    }

    function notifyNewOrderBySms($order)
    {
        $data = array(
            SMS_REVIEWER => $order->reviewer->username,
            SMS_LEARNER => $order->learner->username,
            KEY_AMOUNT => amountToYuan($order->amount),
            SMS_CODE_URL => $order->gitHubUrl,
        );
        $user = $this->userDao->findUserById($order->reviewer->id);
        $phone = $user->mobilePhoneNumber;
        $this->leanCloud->sendTemplateSms($phone, 'order', $data);
    }

    function notifyApplySucceed($userId)
    {
        $user = $this->userDao->findUserById($userId);
        $phone = $user->mobilePhoneNumber;
        $data = array(
            SMS_REVIEWER => $user->username
        );
        $this->leanCloud->sendTemplateSms($phone, 'ApplySucceed', $data);
    }

    function notifyReviewFinish($learner, $reviewer, $reviewId)
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
}
