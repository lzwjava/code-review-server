<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午12:56
 */
class EventDao extends BaseDao
{
    function addEvent($name)
    {
        $data = array(KEY_NAME => $name);
        $this->db->insert(TABLE_EVENTS, $data);
        return $this->db->insert_id();
    }

    function updateEvent($eventId, $data)
    {
        $this->db->where(KEY_EVENT_ID, $eventId);
        $this->db->update(TABLE_EVENTS, $data);
    }

    function getEvent()
    {
        
    }
}
