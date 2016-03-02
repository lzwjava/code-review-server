<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:03
 */
class UserEventDao extends BaseDao
{
    public $eventDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(EventDao::class);
        $this->eventDao = new EventDao();
    }

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
            KEY_EVENT_ID, KEY_CHARGE_ID, KEY_CREATED), TABLE_USER_EVENTS);
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

    function getUserEvents($userId, $skip = 0, $limit = 100)
    {
        $fields = $this->publicFields();
        $eventFields = $this->eventDao->publicFields('e', true);
        $sql = "select $fields,$eventFields from user_events
                left join events as e USING(eventId)
                where userId=?
                limit $limit offset $skip";
        $binds = array($userId);
        $userEvents = $this->db->query($sql, $binds)->result();
        $this->handleUserEvents($userEvents);
        return $userEvents;
    }

    protected function handleUserEvents($userEvents)
    {
        foreach ($userEvents as $userEvent) {
            $es = $this->prefixFields($this->eventDao->fields(), 'e');
            $userEvent->event = extractFields($userEvent, $es, 'e');
        }
    }

}
