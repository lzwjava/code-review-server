<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 下午6:57
 */
class OrderDao extends BaseDao
{

    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model('UserDao');
        $this->userDao = new UserDao();
    }

    private function getPublicFields($prefix = TABLE_ORDERS)
    {
        return $this->mergeFields(array(
            KEY_ORDER_ID,
            KEY_GITHUB_URL,
            KEY_LEARNER_ID,
            KEY_REVIEWER_ID,
            KEY_CODE_LINES,
            KEY_STATUS,
            KEY_AMOUNT,
            KEY_REMARK,
            KEY_FIRST_REWARD_ID,
            KEY_CREATED,
            KEY_UPDATED), $prefix);
    }

    function getOrdersOfLearner($learnerId, $status = null, $skip = 0, $limit = 100)
    {
        $fields = $this->getPublicFields();
        if ($status === null) {
            $statusSql = '';
        } else {
            $statusSql = ' AND status = ? ';
        }
        $sql = "SELECT $fields,reviewId FROM orders
                LEFT JOIN reviews on reviews.orderId = orders.orderId
                WHERE learnerId = ? $statusSql
                ORDER BY created DESC limit $limit  offset $skip";
        $array[] = $learnerId;
        if ($status !== null) {
            $array[] = $status;
        }
        $orders = $this->db->query($sql, $array)->result();
        $this->mergeChildrenOfOrders($orders);
        return $orders;
    }

    function getOrdersOfReviewer($reviewerId, $status = null, $skip = 0, $limit = 100)
    {
        $fields = $this->getPublicFields();
        if ($status === null) {
            $statusSql = " AND status != 'unpaid'";
        } else {
            $statusSql = ' AND status = ? ';
        }
        $sql = "SELECT $fields,reviewId FROM orders
                LEFT JOIN reviews on orders.orderId=reviews.orderId
                WHERE reviewerId = ? $statusSql
                ORDER BY created DESC limit $limit offset $skip";
        $array[] = $reviewerId;
        if ($status !== null) {
            $array[] = $status;
        }
        $orders = $this->db->query($sql, $array)->result();
        $this->mergeChildrenOfOrders($orders);
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

    private function mergeChildrenOfOrders($orders)
    {
        foreach ($orders as $order) {
            $order->learner = $this->userDao->findPublicUser(KEY_ID, $order->learnerId);
            $order->reviewer = $this->userDao->findPublicUser(KEY_ID, $order->reviewerId);
        }
    }

    private function countOrders($reviewerId, $status)
    {
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

    function countPaidOrders($reviewerId)
    {
        return $this->countOrders($reviewerId, ORDER_STATUS_PAID);
    }

    function getOrder($orderId)
    {
        $fields = $this->getPublicFields();
        $sql = "SELECT $fields,reviewId FROM orders
                LEFT JOIN reviews on reviews.orderId=orders.orderId
                WHERE orders.orderId=? limit 1";
        $array[] = $orderId;
        $order = $this->db->query($sql, $array)->row();
        if ($order) {
            $this->mergeChildrenOfOrders(array($order));
        }
        return $order;
    }

    function getOrderByReviewId($reviewId)
    {
        $fields = $this->getPublicFields('o');
        $sql = "select $fields from orders as o
                LEFT JOIN reviews USING(orderId)
                where reviewId=?";
        $binds[] = $reviewId;
        $order = $this->db->query($sql, $binds)->row();
        return $order;
    }

    function addOrder($gitHubUrl, $remark, $reviewerId, $learnerId, $codeLines, $amount)
    {
        $data = array(
            KEY_GITHUB_URL => $gitHubUrl,
            KEY_REMARK => $remark,
            KEY_REVIEWER_ID => $reviewerId,
            KEY_LEARNER_ID => $learnerId,
            KEY_CODE_LINES => $codeLines,
            KEY_AMOUNT => $amount
        );
        $this->db->trans_start();
        $this->db->insert(TABLE_ORDERS, $data);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();
        return $insert_id;
    }

    function update($orderId, $data)
    {
        $this->db->where(KEY_ORDER_ID, $orderId);
        return $this->db->update(TABLE_ORDERS, $data);
    }

    function updateStatus($orderId, $status)
    {
        return $this->update($orderId, array(
            KEY_STATUS => $status
        ));
    }

    function updateOrderToPaid($orderId, $rewardId)
    {
        return $this->update($orderId, array(
            KEY_STATUS => ORDER_STATUS_PAID,
            KEY_FIRST_REWARD_ID => $rewardId
        ));
    }

    function deleteOrder($orderId)
    {
        return $this->db->delete(TABLE_ORDERS, array(KEY_ORDER_ID => $orderId));
    }
}
