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
    public $notify;

    function __construct()
    {
        parent::__construct();
        $this->load->model(EventDao::class);
        $this->eventDao = new EventDao();
        $this->load->model(AttendanceDao::class);
        $this->attendanceDao = new AttendanceDao();
        $this->load->library(Notify::class);
        $this->notify = new Notify();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_NAME, KEY_AMOUNT,
            KEY_MAX_PEOPLE, KEY_START_DATE, KEY_LOCATION))
        ) {
            return;
        }
        $name = $this->post(KEY_NAME);
        $amount = $this->post(KEY_AMOUNT);
        $maxPeople = $this->post(KEY_MAX_PEOPLE);
        $location = $this->post(KEY_LOCATION);
        $startDate = $this->post(KEY_START_DATE);
        $id = $this->eventDao->addEvent($name, $amount, $maxPeople, $location, $startDate);
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
        if ($event->restCount <= 0) {
            $this->failure(ERROR_EXCEED_MAX, '报名已满, 下次再约, 感谢关注.');
            return;
        }
        if ($event->attendance != null) {
            $this->failure(ERROR_ALREADY_DO_IT, '您已报名,无需再次报名.');
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

    function adminNotifyComing_get($eventId)
    {
        $attendances = $this->attendanceDao->getAttendancesByEventId($eventId, 0, 1000);
        foreach ($attendances as $attendance) {
            $this->notify->notifyEventComing($attendance->userId, $eventId);
        }
        $this->succeed();
    }
}
