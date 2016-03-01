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
        return $this->db->insert(TABLE_USER_EVENTS, $data);
    }

}
