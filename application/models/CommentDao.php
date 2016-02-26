<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/10
 * Time: 下午3:47
 */
class CommentDao extends BaseDao
{
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('UserDao');
        $this->userDao = new UserDao();
    }

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

    function fields()
    {
        return array(KEY_COMMENT_ID, KEY_REVIEW_ID, KEY_PARENT_ID,
            KEY_CONTENT, KEY_AUTHOR_ID, KEY_CREATED
        );
    }

    function publicFields($prefix = TABLE_COMMENTS, $alias = false)
    {
        return $this->mergeFields($this->fields(), $prefix, $alias);
    }

    function getComment($commentId)
    {
        return $this->getOneFromTable(TABLE_COMMENTS,
            KEY_COMMENT_ID, $commentId, $this->publicFields());
    }

    function getComments($reviewId, $skip, $limit)
    {
        $fields = $this->publicFields('c');
        $fields2 = $this->publicFields('c2', true);
        $userFields = $this->userDao->publicFields('u');
        $userFields2 = $this->userDao->publicFields('u2', true);
        $sql = "SELECT $fields, $fields2,
                $userFields,$userFields2
                FROM comments as c
                left join users as u on u.id=c.authorId
                left join comments as c2 on c.parentId=c2.commentId
                left join users as u2 on u2.id=c2.authorId
                WHERE c.reviewId=?
                group by c.commentId
                order by c.created desc
                limit $limit offset $skip";
        $binds = array($reviewId);
        $comments = $this->db->query($sql, $binds)->result();
        $this->assembleComments($comments);
        return $comments;
    }

    protected function removePrefix(&$object, $prefix)
    {
        $newObj = new StdClass();
        foreach ($object as $key => $value) {
            if (substr($key, 0, strlen($prefix)) == $prefix) {
                $key = substr($key, strlen($prefix));
                $newObj->$key = $value;
            }
        }
        $object = $newObj;
    }

    private function assembleComments($comments)
    {
        foreach ($comments as $comment) {
            $comment->author = extractFields($comment, $this->userDao->publicRawFields());
            $c2s = $this->prefixFields($this->fields(), 'c2');
            $u2s = $this->prefixFields($this->userDao->publicRawFields(), 'u2');
            if ($comment->parentId) {
                $comment->parent = extractFields($comment, $c2s, 'c2');
                $comment->parent->author = extractFields($comment, $u2s, 'u2');
            } else {
                $this->unsets($comment, $c2s);
                $this->unsets($comment, $u2s);
            }
        }
    }

}
