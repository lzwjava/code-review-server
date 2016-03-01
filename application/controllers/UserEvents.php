<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:09
 */
class UserEvents extends BaseController
{
    public $userEventDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('UserEventDao');
        $this->userEventDao = new UserEventDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_EVENT_ID))) {
            return;
        }
        $eventId = $this->post(KEY_EVENT_ID);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $ok = $this->userEventDao->addUserEvent($user->id, $eventId);
        $this->responseBySQLRes($ok);
    }
}
