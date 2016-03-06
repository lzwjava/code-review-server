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
    public $attendanceDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(EventDao::class);
        $this->eventDao = new EventDao();
        $this->load->model(AttendanceDao::class);
        $this->attendanceDao = new AttendanceDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_NAME, KEY_AMOUNT, KEY_MAX_PEOPLE))) {
            return;
        }
        $name = $this->post(KEY_NAME);
        $amount = $this->post(KEY_AMOUNT);
        $maxPeople = $this->post(KEY_MAX_PEOPLE);
        $id = $this->eventDao->addEvent($name, $amount, $maxPeople);
        if ($this->checkIfSQLResWrong($id)) {
            return;
        }
        $this->succeed(array(KEY_EVENT_ID => $id));
    }

    function pay_post($eventId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $event = $this->eventDao->getEvent($eventId, $user);
        if ($this->checkIfObjectNotExists($event)) {
            return;
        }
        $subject = truncate($user->username, 18) . '参加活动' . $event->eventId;
        $body = $user->username . ' 参加 ' . $event->name;
        $metaData = array(KEY_EVENT_ID => $eventId, KEY_USER_ID => $user->id);
        $this->createChargeThenResponse($event->amount, $subject, $body, $metaData, $user);
    }

    function one_get($eventId)
    {
        $user = $this->getSessionUser();
        $event = $this->eventDao->getEvent($eventId, $user);
        if ($this->checkIfObjectNotExists($event)) {
            return;
        }
        $this->succeed($event);
    }
}
