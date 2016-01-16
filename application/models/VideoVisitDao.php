<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/1/16
 * Time: 下午10:01
 */
class VideoVisitDao extends BaseDao
{

    function addVideoVisit($visitorId, $videoId, $referrer, $userId)
    {
        $data = array(
            KEY_VISITOR_ID => $visitorId,
            KEY_VIDEO_ID => $videoId,
            KEY_REFERRER => $referrer
        );
        if ($userId) {
            $data[KEY_USER_ID] = $userId;
        }
        return $this->db->insert(TABLE_VIDEO_VISITS, $data);
    }
}
