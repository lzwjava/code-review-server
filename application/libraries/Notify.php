<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/25
 * Time: 上午4:19
 */
class Notify extends CI_Model
{
    public $notificationDao;
    public $sms;

    function __construct()
    {
        parent::__construct();
        $this->load->model(NotificationDao::class);
        $this->notificationDao = new NotificationDao();
        $this->load->library('sms');
        $this->sms = new Sms();
    }

    function notifyNewOrder($order)
    {
        $this->notificationDao->notifyNewOrder($order);
        $this->sms->notifyNewOrderBySms($order);
    }

    function notifyApplySucceed($userId)
    {
        $this->sms->notifyApplySucceed($userId);
        $this->notificationDao->notifyAgree($userId);
    }

    function notifyReviewFinish($order, $review)
    {
        $this->sms->notifyReviewFinish($order->learner, $order->reviewer, $review->reviewId);
        $this->notificationDao->notifyOrderFinish($order);
    }

}