<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 下午7:33
 */
class Attendances extends BaseController
{
    public $attendanceDao;
    public $eventDao;

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
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_EVENT_ID))) {
            return;
        }
        $eventId = $this->post(KEY_EVENT_ID);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $attendance = $this->attendanceDao->getAttendance($user->id, $eventId);
        if ($attendance) {
            $this->failure(ERROR_ALREADY_DO_IT, '您已报名过该活动了');
            return;
        }
        $attendanceId = $this->attendanceDao->addAttendance($user->id, $eventId);
        if ($this->checkIfSQLResWrong($attendanceId)) {
            return;
        }
        $this->succeed(array(KEY_ATTENDANCE_ID => $attendanceId));
    }

    function pay_post($attendanceId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $attendance = $this->attendanceDao->getAttendanceById($attendanceId);
        if (!$attendance) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, '您还没有报名该活动');
            return;
        }
        $event = $this->eventDao->getEvent($attendance->eventId, $user);
        if ($this->checkIfObjectNotExists($event)) {
            return;
        }
        $subject = truncate($user->username, 18) . '参加活动' . $event->eventId;
        $body = $user->username . ' 参加 ' . $event->name;
        $metaData = array(KEY_ATTENDANCE_ID => $attendance->attendanceId);
        $this->createChargeThenResponse($event->amount, $subject, $body, $metaData, $user);
    }

    function one_get($eventId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $attendance = $this->attendanceDao->getAttendance($user->id, $eventId);
        if ($this->checkIfObjectNotExists($attendance)) {
            return;
        }
        $this->succeed($attendance);
    }

    function list_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        $attendances = $this->attendanceDao->getAttendances($user->id, $skip, $limit);
        $this->succeed($attendances);
    }
}
