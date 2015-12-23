<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 上午12:31
 */
class ReviewerDao extends BaseDao
{
    public function setReviewerValid($id)
    {
        $sql = "UPDATE reviewers SET valid=1 WHERE id=?";
        $array[] = $id;
        return $this->db->query($sql, $array);
    }

    private function publicFields()
    {
        return $this->mergeFields(array(KEY_ID, KEY_USERNAME,
            KEY_AVATAR_URL, KEY_CREATED, KEY_INTRODUCTION, KEY_EXPERIENCE));
    }

    public function getList($skip = 0, $limit = 100)
    {
        $fields = $this->publicFields();
        $sql = "SELECT $fields FROM reviewers where valid=1 ORDER BY created limit $limit OFFSET
$skip";
        $result = $this->db->query($sql)->result();
        foreach ($result as $reviewer) {
            $this->mergeCount($reviewer);
        }
        return $result;
    }

    private function mergeCount($reviewer)
    {
        $reviewer->orderCount = $this->orderDao->countFinishOrders($reviewer->id);
        $reviewer->rewardCount = $this->rewardDao->countRewards($reviewer->id);
    }

    public function getOne($id)
    {
        $fields = $this->publicFields();
        $sql = "SELECT $fields FROM reviewers WHERE valid=1 AND id=?";
        $array[] = $id;
        $reviewer = $this->db->query($sql, $array)->row();
        if ($reviewer) {
            $this->mergeCount($reviewer);
        }
        return $reviewer;
    }

    public function update($id, $data)
    {

    }

}

