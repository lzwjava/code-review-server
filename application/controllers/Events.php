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
