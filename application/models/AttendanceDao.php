<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:03
 */
class AttendanceDao extends BaseDao
{
    public $eventDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(EventDao::class);
        $this->eventDao = new EventDao();
    }

    function addAttendance($userId, $eventId)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_EVENT_ID => $eventId
        );
        $this->db->insert(TABLE_ATTENDANCES, $data);
        return $this->db->insert_id();
    }

    private function publicFields()
    {
        return $this->mergeFields(array(KEY_ATTENDANCE_ID, KEY_USER_ID,
            KEY_EVENT_ID, KEY_CHARGE_ID, KEY_CREATED), TABLE_ATTENDANCES);
    }

    function getAttendance($userId, $eventId)
    {
        $fields = $this->publicFields();
        $sql = "select $fields from attendances where userId=? and eventId=?";
        $binds = array($userId, $eventId);
        return $this->db->query($sql, $binds)->row();
    }

    function getAttendanceById($attendanceId)
    {
        $fields = $this->publicFields();
        $sql = "select $fields from attendances where attendanceId=?";
        $binds = array($attendanceId);
        return $this->db->query($sql, $binds)->row();
    }

    private function update($attendanceId, $data)
    {
        $this->db->where(KEY_ATTENDANCE_ID, $attendanceId);
        return $this->db->update(TABLE_ATTENDANCES, $data);
    }

    function updateAttendanceToPaid($attendanceId, $chargeId)
    {
        return $this->update($attendanceId, array(KEY_CHARGE_ID => $chargeId));
    }

    function getAttendances($userId, $skip = 0, $limit = 100)
    {
        $fields = $this->publicFields();
        $eventFields = $this->eventDao->publicFields('e', true);
        $sql = "select $fields,$eventFields from attendances
                left join events as e USING(eventId)
                where userId=?
                limit $limit offset $skip";
        $binds = array($userId);
        $attendances = $this->db->query($sql, $binds)->result();
        $this->handleAttendances($attendances);
        return $attendances;
    }

    protected function handleAttendances($attendances)
    {
        foreach ($attendances as $attendance) {
            $es = $this->prefixFields($this->eventDao->fields(), 'e');
            $attendance->event = extractFields($attendance, $es, 'e');
        }
    }

}
