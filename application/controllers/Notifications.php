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
        $this->notificationDao->
    }
}
