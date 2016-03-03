<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午12:56
 */
class EventDao extends BaseDao
{
    function addEvent($name, $amount)
    {
        $data = array(
            KEY_NAME => $name,
            KEY_AMOUNT => $amount
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
        $sql = "SELECT $fields,uv.userEventId FROM events as e
                left join user_events as uv on uv.userId=$userId and uv.eventId=e.eventId
                where e.eventId=?";
        $binds = array($eventId);
        return $this->db->query($sql, $binds)->row();
    }
}
