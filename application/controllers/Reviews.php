<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午2:22
 */
class Reviews extends BaseController
{

    public function add()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_ORDER_ID,
            KEY_CONTENT, KEY_TITLE))
        ) {
            return;
        }
        $orderId = $_POST[KEY_ORDER_ID];
        $title = $_POST[KEY_TITLE];
        $content = $_POST[KEY_CONTENT];
        $order = $this->orderDao->getOne($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "找不到相应的 review 订单");
            return;
        }
        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $user = $this->getSessionUser();
        if ($user->id != $order->reviewerId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, "当前登录的用户不是该 review 指定的大神");
            return;
        }
        if ($order->review != null) {
            $this->failure(ERROR_ALREADY_DO_IT, "已经填写过 Review 了, 请编辑相应的 Review");
            return;
        }
        if (strlen($title) <= 0) {
            $this->failure(ERROR_PARAMETER_ILLEGAL, "标题长度应该大于 0");
            return;
        }
        $insertId = $this->reviewDao->add($orderId, $title, $content);
        $this->succeed($this->reviewDao->getOne($insertId));
    }

    public function edit()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_REVIEW_ID))) {
            return;
        }
        if ($this->checkIfNotAtLeastOneParam($_POST, array(KEY_CONTENT, KEY_TITLE))) {
            return;
        }
        $reviewId = $_POST[KEY_REVIEW_ID];
        $data = array();
        if (isset($_POST[KEY_CONTENT])) {
            $data[KEY_CONTENT]= $_POST[KEY_CONTENT];
        }
        if (isset($_POST[KEY_TITLE])) {
            $data[KEY_TITLE] = $_POST[KEY_TITLE];
        }
        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $user = $this->getSessionUser();
        $review = $this->reviewDao->getOne($reviewId);
        if ($review == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "找不到相应的 Review");
            return;
        }
        $orderId = $review->orderId;
        $order = $this->orderDao->getOne($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "找不到相应的 review 订单");
            return;
        }
        if ($user->id != $order->reviewerId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, "当前编辑的用户不是该 Review 指定的大神");
            return;
        }
        $this->reviewDao->update($reviewId, $data);
        $this->succeed($this->reviewDao->getOne($reviewId));
    }

}
