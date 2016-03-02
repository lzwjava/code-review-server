<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 下午7:33
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

    function one_get($eventId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $userEvent = $this->userEventDao->getUserEvent($user->id, $eventId);
        if ($this->checkIfObjectNotExists($userEvent)) {
            return;
        }
        $this->succeed($userEvent);
    }

    function list_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        $userEvents = $this->userEventDao->getUserEvents($user->id, $skip, $limit);
        $this->succeed($userEvents);
    }
}
