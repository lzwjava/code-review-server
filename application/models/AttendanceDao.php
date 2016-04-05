<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:03
 */
class AttendanceDao extends BaseDao
{
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    function addAttendance($userId, $eventId, $chargeId)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_EVENT_ID => $eventId,
            KEY_CHARGE_ID => $chargeId
        );
        $this->db->insert(TABLE_ATTENDANCES, $data);
        return $this->db->insert_id();
    }

    function getAttendance($userId, $eventId)
    {
        $fields = $this->attendancePublicFields();
        $sql = "select $fields from attendances where userId=? and eventId=?";
        $binds = array($userId, $eventId);
        return $this->db->query($sql, $binds)->row();
    }

    function getAttendanceById($attendanceId)
    {
        $fields = $this->attendancePublicFields();
        $sql = "select $fields from attendances where attendanceId=?";
        $binds = array($attendanceId);
        return $this->db->query($sql, $binds)->row();
    }

    private function update($attendanceId, $data)
    {
        $this->db->where(KEY_ATTENDANCE_ID, $attendanceId);
        return $this->db->update(TABLE_ATTENDANCES, $data);
    }

    function getAttendancesByUserId($userId, $skip, $limit)
    {
        return $this->getAttendances(KEY_USER_ID, $userId, $skip, $limit);
    }

    function getAttendancesByEventId($eventId, $skip, $limit)
    {
        return $this->getAttendances(KEY_EVENT_ID, $eventId, $skip, $limit);
    }

    private function getAttendances($field, $value, $skip = 0, $limit = 100)
    {
        $fields = $this->attendancePublicFields('a');
        $eventFields = $this->eventPublicFields('e', true);
        $userFields = $this->userDao->publicFields('u', true);
        $sql = "select $fields,$eventFields,$userFields
                from attendances as a
                left join events as e USING(eventId)
                left join users as u on u.id=a.userId
                where a.$field=?
                limit $limit offset $skip";
        $binds = array($value);
        $attendances = $this->db->query($sql, $binds)->result();
        $this->handleAttendances($attendances);
        return $attendances;
    }

    protected function handleAttendances($attendances)
    {
        foreach ($attendances as $attendance) {
            $es = $this->prefixFields($this->eventFields(), 'e');
            $attendance->event = extractFields($attendance, $es, 'e');
            $us = $this->prefixFields($this->userDao->publicRawFields(), 'u');
            $attendance->user = extractFields($attendance, $us, 'u');
        }
    }

}
