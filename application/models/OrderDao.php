<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 下午6:57
 */
class OrderDao extends BaseDao
{

    function getPublicFields()
    {
        return $this->mergeFields(array(
            dbField(TABLE_ORDERS, KEY_ORDER_ID),
            KEY_GITHUB_URL,
            KEY_LEARNER_ID,
            KEY_REVIEWER_ID,
            KEY_STATUS,
            KEY_REMARK,
            dbField(TABLE_ORDERS, KEY_CREATED),
            dbField(TABLE_ORDERS, KEY_UPDATED),
            dbField(TABLE_REVIEWS, KEY_REVIEW_ID)));
    }

    function getOrdersOfLearner($learnerId, $skip = 0, $limit = 100)
    {
        $fields = $this->getPublicFields();
        $sql = "SELECT $fields FROM orders LEFT JOIN reviews ON orders.orderId = reviews.orderId
          WHERE orders.learnerId = ? ORDER BY orders.updated DESC limit $limit offset $skip";
        $array[] = $learnerId;
        $orders = $this->db->query($sql, $array)->result();
        foreach ($orders as $order) {
            $this->mergeOrderChildren($order);
        }
        return $orders;
    }

    function getOrdersOfReviewer($reviewerId, $skip = 0, $limit = 100)
    {
        $fields = $this->getPublicFields();
        $sql = "SELECT $fields FROM orders LEFT JOIN reviews ON orders.orderId = reviews.orderId
           WHERE orders.reviewerId = ? ORDER BY orders.updated DESC limit $limit offset $skip";
        $array[] = $reviewerId;
        $orders = $this->db->query($sql, $array)->result();
        foreach ($orders as $order) {
            $this->mergeOrderChildren($order);
        }
        return $orders;
    }

    function getOrdersOfReviewerWithLearner($reviewerId, $learnerId)
    {
        $fields = $this->getPublicFields();
        $sql = "select $fields from orders LEFT JOIN reviews ON orders.orderId = reviews.orderId
                where orders.reviewerId=? and orders.learnerId = ? ORDER BY orders.updated DESC";
        $array[] = $reviewerId;
        $array[] = $learnerId;
        $orders = $this->db->query($sql, $array)->result();
        foreach ($orders as $order) {
            $this->mergeOrderChildren($order);
        }
        return $orders;
    }

    function hasSameOrder($reviewerId, $learnerId, $gitHubUrl)
    {
        $sql = "SELECT count(*) AS cnt FROM orders WHERE orders.reviewerId = ?
                AND orders.learnerId=? AND gitHubUrl=?";
        $array = array($reviewerId, $learnerId, $gitHubUrl);
        $result = $this->db->query($sql, $array)->row();
        return $result->cnt > 0;
    }

    function mergeOrderChildren($order)
    {
        if ($order) {
            $order->learner = $this->userDao->findPublicUser(KEY_ID, $order->learnerId);
            $order->reviewer = $this->userDao->findPublicUser(KEY_ID, $order->reviewerId);
            if ($order->reviewId) {
                $order->review = $this->reviewDao->getOne($order->reviewId);
            }
            $order->tags = $this->tagDao->getOrderTags($order->orderId);
        }
    }

    private function countOrders($reviewerId, $status) {
        $sql = "SELECT count(*) AS cnt FROM orders WHERE status=? AND reviewerId=?";
        $array[] = $status;
        $array[] = $reviewerId;
        $result = $this->db->query($sql, $array)->row();
        return $result->cnt;
    }

    function countFinishOrders($reviewerId)
    {
        return $this->countOrders($reviewerId, ORDER_STATUS_FINISHED);
    }

    function countPaidOrders($reviewerId) {
        return $this->countOrders($reviewerId, ORDER_STATUS_PAID);
    }

    function getOne($orderId)
    {
        $fields = $this->getPublicFields();
        $sql = "SELECT $fields FROM orders LEFT JOIN reviews ON orders.orderId = reviews.orderId WHERE orders.orderId = ?";
        $array[] = $orderId;
        $order = $this->db->query($sql, $array)->row();
        if ($order) {
            $this->mergeOrderChildren($order);
        }
        return $order;
    }

    function getOneByReviewId($reviewId)
    {
        $fields = $this->getPublicFields();
        $sql = "SELECT $fields FROM orders LEFT JOIN reviews ON orders.orderId = reviews.orderId WHERE reviews.reviewId = ?";
        $array[] = $reviewId;
        $order = $this->db->query($sql, $array)->row();
        if ($order) {
            $this->mergeOrderChildren($order);
        }
        return $order;
    }

    function add($gitHubUrl, $remark, $reviewerId, $learnerId)
    {
        $data = array(
            KEY_GITHUB_URL => $gitHubUrl,
            KEY_REMARK => $remark,
            KEY_REVIEWER_ID => $reviewerId,
            KEY_LEARNER_ID => $learnerId
        );
        $this->db->trans_start();
        $this->db->insert(TABLE_ORDERS, $data);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();
        return $insert_id;
    }

    function update($id, $data)
    {

    }

    function updateOrderToPaid($orderId)
    {
        $sql = "UPDATE orders SET status=? WHERE orderId= ?";
        $array[] = ORDER_STATUS_PAID;
        $array[] = $orderId;
        return $this->db->query($sql, $array);
    }
}
