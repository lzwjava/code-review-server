<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午12:56
 */
class EventDao extends BaseDao
{

    function addEvent($name, $amount, $maxPeople, $location, $startDate)
    {
        $data = array(
            KEY_NAME => $name,
            KEY_AMOUNT => $amount,
            KEY_MAX_PEOPLE => $maxPeople,
            KEY_LOCATION => $location,
            KEY_START_DATE => $startDate
        );
        $this->db->insert(TABLE_EVENTS, $data);
        return $this->db->insert_id();
    }

    function updateEvent($eventId, $data)
    {
        $this->db->where(KEY_EVENT_ID, $eventId);
        $this->db->update(TABLE_EVENTS, $data);
    }

    function getEvent($eventId, $user)
    {
        if ($user) {
            $userId = $user->id;
        } else {
            $userId = 'null';
        }
        $fields = $this->eventPublicFields('e');
        $aFields = $this->attendancePublicFields('a', true);
        $sql = "SELECT $fields,$aFields,
                count(aa.attendanceId) as attendCount
                FROM events as e
                left join attendances as a on a.userId=? and a.eventId=e.eventId
                left join attendances as aa on aa.eventId=e.eventId
                where e.eventId=?";
        $binds = array($userId, $eventId);
        $event = $this->db->query($sql, $binds)->row();
        $this->handleEvents(array($event));
        return $event;
    }

    private function handleEvents($events)
    {
        foreach ($events as $event) {
            $prefixFields = $this->prefixFields($this->attendanceFields(), 'a');
            $event->attendance = extractFields($event, $prefixFields, 'a');
            $event->restCount = $event->maxPeople - $event->attendCount;
        }
    }
}
