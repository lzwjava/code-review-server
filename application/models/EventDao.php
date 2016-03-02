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

    private function publicFields()
    {
        return $this->mergeFields(array(KEY_EVENT_ID, KEY_NAME, KEY_AMOUNT, KEY_CREATED));
    }

    function getEvent($eventId)
    {
        $fields = $this->publicFields();
        return $this->getOneFromTable(TABLE_EVENTS, KEY_EVENT_ID, $eventId, $fields);
    }
}
