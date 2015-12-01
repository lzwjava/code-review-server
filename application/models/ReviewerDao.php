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
}