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
        $skip = $this->skip();
        $limit = $this->limit();
        $attendances = $this->attendanceDao->getAttendancesByUserId($user->id, $skip, $limit);
        $this->succeed($attendances);
    }

    function eventList_get($eventId)
    {
        $skip = $this->skip();
        $limit = $this->limit();
        $attendances = $this->attendanceDao->getAttendancesByEventId($eventId, $skip, $limit);
        $this->succeed($attendances);
    }
}
