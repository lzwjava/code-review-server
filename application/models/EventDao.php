<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午12:56
 */
class EventDao extends BaseDao
{
    function addEvent($name, $amount, $maxPeople)
    {
        $data = array(
            KEY_NAME => $name,
            KEY_AMOUNT => $amount,
            KEY_MAX_PEOPLE => $maxPeople
        );
        $this->db->insert(TABLE_EVENTS, $data);
        return $this->db->insert_id();
    }

    function updateEvent($eventId, $data)
    {
        $this->db->where(KEY_EVENT_ID, $eventId);
        $this->db->update(TABLE_EVENTS, $data);
    }

    function fields()
    {
        return array(KEY_EVENT_ID, KEY_NAME, KEY_AMOUNT, KEY_CREATED);
    }

    function publicFields($prefix = TABLE_EVENTS, $alias = false)
    {
        return $this->mergeFields($this->fields(), $prefix, $alias);
    }

    function getEvent($eventId, $user)
    {
        if ($user) {
            $userId = $user->id;
        } else {
            $userId = 'null';
        }
        $fields = $this->publicFields('e');
        $sql = "SELECT $fields,a.attendanceId,a.chargeId
                FROM events as e
                left join attendances as a on a.userId=? and a.eventId=e.eventId
                where e.eventId=?";
        $binds = array($userId, $eventId);
        $event = $this->db->query($sql, $binds)->row();
        $this->handleEvents(array($event));
        return $event;
    }

    private function handleEvents($events)
    {
        foreach ($events as $event) {
            if ($event->attendanceId == null) {
                $event->status = EVENT_STATUS_NONE;
            } else {
                $event->status = EVENT_STATUS_ATTENDED;
            }
            $event->attendance = extractFields($event, array(KEY_ATTENDANCE_ID, KEY_CHARGE_ID));
        }
    }
}
