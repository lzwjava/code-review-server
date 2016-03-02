<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:03
 */
class UserEventDao extends BaseDao
{
    function addUserEvent($userId, $eventId)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_EVENT_ID => $eventId
        );
        $this->db->insert(TABLE_USER_EVENTS, $data);
        return $this->db->insert_id();
    }

    private function publicFields()
    {
        return $this->mergeFields(array(KEY_USER_EVENT_ID, KEY_USER_ID,
            KEY_EVENT_ID, KEY_CHARGE_ID, KEY_CREATED));
    }

    function getUserEvent($userId, $eventId)
    {
        $fields = $this->publicFields();
        $sql = "select $fields from user_events where userId=? and eventId=?";
        $binds = array($userId, $eventId);
        return $this->db->query($sql, $binds)->row();
    }

    function getUserEventById($userEventId)
    {
        $fields = $this->publicFields();
        $sql = "select $fields from user_events where userEventId=?";
        $binds = array($userEventId);
        return $this->db->query($sql, $binds)->result();
    }

    private function update($userEventId, $data)
    {
        $this->db->where(KEY_USER_EVENT_ID, $userEventId);
        return $this->db->update(TABLE_USER_EVENTS, $data);
    }

    function updateUserEventToPaid($userEventId, $chargeId)
    {
        return $this->update($userEventId, array(KEY_CHARGE_ID => $chargeId));
    }

}
