<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:02
 */
class Events extends BaseController
{
    public $eventDao;
    public $userEventDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('EventDao');
        $this->eventDao = new EventDao();
        $this->load->model('UserEventDao');
        $this->userEventDao = new UserEventDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_NAME, KEY_AMOUNT))) {
            return;
        }
        $name = $this->post(KEY_NAME);
        $amount = $this->post(KEY_AMOUNT);
        $id = $this->eventDao->addEvent($name, $amount);
        if ($this->checkIfSQLResWrong($id)) {
            return;
        }
        $this->succeed(array(KEY_EVENT_ID => $id));
    }

    function attend_post($eventId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $userEvent = $this->userEventDao->getUserEvent($user->id, $eventId);
        if ($userEvent) {
            $this->failure(ERROR_ALREADY_DO_IT, '您已报名过该活动了');
            return;
        }
        $userEventId = $this->userEventDao->addUserEvent($user->id, $eventId);
        if ($this->checkIfSQLResWrong($userEventId)) {
            return;
        }
        $this->succeed(array(KEY_USER_EVENT_ID => $userEventId));
    }

    function pay_post($eventId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $event = $this->eventDao->getEvent($eventId);
        if ($this->checkIfObjectNotExists($event)) {
            return;
        }
        $userEvent = $this->userEventDao->getUserEvent($user->id, $eventId);
        if (!$userEvent) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, '您还没有报名该活动');
            return;
        }
        $subject = truncate($user->username) . '参加' . truncate($event->name, 15);
        $body = $user->username . ' 参加 ' . $event->name;
        $metaData = array(KEY_USER_EVENT_ID => $userEvent->userEventId);
        $this->createChargeThenResponse($event->amount, $subject, $body, $metaData, $user);
    }

    function one_get($eventId)
    {
        $event = $this->eventDao->getEvent($eventId);
        if ($this->checkIfObjectNotExists($event)) {
            return;
        }
        $this->succeed($event);
    }
}
