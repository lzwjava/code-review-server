<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/1/16
 * Time: 下午10:00
 */
class VideoDao extends BaseDao
{
    function addVideo($title, $speaker, $source)
    {
        $data = array(
            KEY_TITLE => $title,
            KEY_SPEAKER => $speaker,
            KEY_SOURCE => $source
        );
        $result = $this->db->insert(TABLE_VIDEOS, $data);
        if ($result) {
            return $this->db->insert_id();
        } else {
            return null;
        }
    }

    private function getPublicFields()
    {
        return $this->mergeFields(array(
            KEY_VIDEO_ID,
            KEY_TITLE,
            KEY_SPEAKER,
            KEY_SOURCE,
            KEY_CREATED,
            KEY_UPDATED
        ), TABLE_VIDEOS);
    }

    function getVideoList()
    {
        $fields = $this->getPublicFields();
        $sql = "select $fields,count(visitId) as visitCount from videos left join video_visits using(videoId) order by created desc";
        return $this->db->query($sql, null)->result();
    }

    function getVideo($videoId)
    {
        $fields = $this->getPublicFields();
        $sql = "select $fields,count(visitId) as visitCount from videos left join video_visits using(videoId)
                where videoId=? order by created desc";
        $array[] = $videoId;
        return $this->db->query($sql, $array)->row();
    }
}
