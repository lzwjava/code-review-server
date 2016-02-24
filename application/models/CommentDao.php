<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/10
 * Time: 下午3:47
 */
class CommentDao extends BaseDao
{
    function addComment($reviewId, $parentId, $content, $authorId)
    {
        $data = array(
            KEY_REVIEW_ID => $reviewId,
            KEY_CONTENT => $content,
            KEY_AUTHOR_ID => $authorId
        );
        if ($parentId != null) {
            $data[KEY_PARENT_ID] = $parentId;
        }
        $this->db->insert(TABLE_COMMENTS, $data);
        return $this->db->insert_id();
    }

    private function publicFields()
    {
        return $this->mergeFields(array(
            KEY_COMMENT_ID, KEY_REVIEW_ID, KEY_PARENT_ID,
            KEY_CONTENT, KEY_AUTHOR_ID, KEY_CREATED
        ), TABLE_COMMENTS);
    }

    function getComments($reviewId, $skip, $limit)
    {
        $fields = $this->publicFields();
        $sql = "SELECT $fields,
                u.id,u.username
                FROM comments
                left join users as u on u.id=comments.authorId
                WHERE reviewId=?
                group by commentId
                order by created desc
                limit $limit offset $skip";
        $binds = array($reviewId);
        $comments = $this->db->query($sql, $binds)->result();
        $this->assembleComments($comments);
        return $comments;
    }

    private function assembleComments($comments)
    {
        foreach ($comments as $comment) {
            $comment->author = $this->extractFields($comment,
                array(KEY_ID, KEY_USERNAME));
            unset($comment->authorId);
        }
    }

}
