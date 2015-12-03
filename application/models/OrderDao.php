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
            dbField(TABLE_ORDERS, KEY_CREATED),
            dbField(TABLE_ORDERS, KEY_UPDATED),
            dbField(TABLE_REVIEWS, KEY_REVIEW_ID)));
    }

    private function mergeReview($order)
    {
        if ($order->reviewId) {
            $order->review = $this->reviewDao->getOne($order->reviewId);
        }
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

    function mergeOrderChildren($order)
    {
        if ($order) {
            $order->learner = $this->userDao->findPublicUser(KEY_ID, $order->learnerId);
            $order->reviewer = $this->userDao->findPublicUser(KEY_ID, $order->reviewerId);
            $this->mergeReview($order);
        }
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
}