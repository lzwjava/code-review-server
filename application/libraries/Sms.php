<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/25
 * Time: 上午3:49
 */
class Sms extends BaseDao
{
    public $leanCloud;
    public $userDao;
    public $eventDao;

    function __construct()
    {
        parent::__construct();
        $this->load->library(LeanCloud::class);
        $this->load->model(UserDao::class);
        $this->load->model(EventDao::class);
        $this->leanCloud = new LeanCloud();
        $this->userDao = new UserDao();
        $this->eventDao = new EventDao();
        $this->load->helper('date');
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

    function notifyAttendEvent($userId, $eventId)
    {
        $user = $this->userDao->findUserById($userId);
        $data = array(
            SMS_USER => $user->username,
            SMS_LOCAION => '中关村 e 世界联合创业办公社',
            SMS_DATE => '3月13日'
        );
        $this->leanCloud->sendTemplateSms($user->mobilePhoneNumber, 'AttendEvent', $data);
    }

    private function formatDate($date)
    {
        $time = strtotime($date);
        logInfo("type " . gettype($time));
        logInfo("time " . $time);
        return mdate('%m-%d %D %h:%i%a', $time);
    }

    function notifyEventComing($userId, $eventId)
    {
        $user = $this->userDao->findUserById($userId);
        $event = $this->eventDao->getEvent($eventId, null);

        $data = array(
            SMS_USER => $user->username,
            SMS_EVENT => $event->name,
            SMS_LOCATION => $event->location,
            SMS_DATE => $this->formatDate($event->startDate),
            SMS_OTHER_TIPS => ''
        );
        $this->leanCloud->sendTemplateSms($user->mobilePhoneNumber, 'EventComing', $data);
    }
}
