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
            KEY_ORDER_ID,
            KEY_GITHUB_URL,
            KEY_LEARNER_ID,
            KEY_REVIEWER_ID,
            KEY_CODE_LINES,
            KEY_STATUS,
            KEY_REMARK,
            KEY_CREATED,
            KEY_UPDATED));
    }


    function getOrdersByField($field, $value, $status = null, $skip, $limit)
    {
        $fields = $this->getPublicFields();
        if ($status === null) {
            $statusSql = '';
        } else {
            $statusSql = ' AND status = ? ';
        }
        $sql = "SELECT $fields FROM orders WHERE $field = ? $statusSql
                ORDER BY updated DESC limit $limit  offset $skip";
        $array[] = $value;
        if ($status !== null) {
            $array[] = $status;
        }
        $orders = $this->db->query($sql, $array)->result();
        $this->mergeChildrenOfOrders($orders);
        return $orders;
    }

    function getOrdersOfLearner($learnerId, $status = null, $skip = 0, $limit = 100)
    {
        $orders = $this->getOrdersByField(KEY_LEARNER_ID, $learnerId, $status, $skip, $limit);
        return $orders;
    }

    function getOrdersOfReviewer($reviewerId, $status = null, $skip = 0, $limit = 100)
    {
        $orders = $this->getOrdersByField(KEY_REVIEWER_ID, $reviewerId, $status, $skip, $limit);
        return $orders;
    }

    function getOrdersOfReviewerWithLearner($reviewerId, $learnerId)
    {
        $fields = $this->getPublicFields();
        $sql = "select $fields from orders where reviewerId=? and learnerId = ? ORDER BY orders.updated DESC";
        $array[] = $reviewerId;
        $array[] = $learnerId;
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

    function mergeChildrenOfOrders($orders)
    {
        foreach ($orders as $order) {
            $order->learner = $this->userDao->findPublicUser(KEY_ID, $order->learnerId);
            $order->reviewer = $this->userDao->findPublicUser(KEY_ID, $order->reviewerId);
            $order->review = $this->reviewDao->getOneByOrderId($order->orderId);
            $order->tags = $this->tagDao->getOrderTags($order->orderId);
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

    function getOne($orderId)
    {
        $fields = $this->getPublicFields();
        $sql = "SELECT $fields FROM orders WHERE orders.orderId = ?";
        $array[] = $orderId;
        $order = $this->db->query($sql, $array)->row();
        if ($order) {
            $this->mergeChildrenOfOrders(array($order));
        }
        return $order;
    }

    function add($gitHubUrl, $remark, $reviewerId, $learnerId, $codeLines)
    {
        $data = array(
            KEY_GITHUB_URL => $gitHubUrl,
            KEY_REMARK => $remark,
            KEY_REVIEWER_ID => $reviewerId,
            KEY_LEARNER_ID => $learnerId,
            KEY_CODE_LINES => $codeLines
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

    function updateOrderToPaid($orderId)
    {
        return $this->updateStatus($orderId, ORDER_STATUS_PAID);
    }
}
