<?php

if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class BaseController extends REST_Controller
{
    public $userDao;
    public $chargeDao;

    function __construct()
    {
        parent::__construct();

        if (isLocalDebug()) {
            \Pingpp\Pingpp::setApiKey('sk_test_nz9af5CKmb5CnXn10Ou1eHq5');
        } else {
            \Pingpp\Pingpp::setApiKey('sk_live_SSijL0KO8eHK5qzfPG0mjDW9');
        }
        $this->load->model('UserDao');
        $this->userDao = new UserDao();
        $this->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
    }

    protected function responseResult($code, $result = null, $error = null, $total = null)
    {
        if ($result === null) {
            $result = new stdClass;
        }
        if ($error === null) {
            $error = "";
        }
        $arr = array(
            'code' => $code,
            'result' => $result
        );
        if ($total !== null) {
            $arr['total'] = $total;
        }
        $arr['error'] = $error;
        $this->response($arr, REST_Controller::HTTP_OK);
        //$this->responseJSON($arr);
    }

    protected function responseJSON($obj)
    {
        $this->output->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($obj));
    }

    protected function succeed($resultData = null, $total = null)
    {
        $this->responseResult(REQ_OK, $resultData, null, $total);
    }

    protected function failure($resultCode, $resultInfo)
    {
        $this->responseResult($resultCode, null, $resultInfo);
    }

    protected function checkIfSQLResWrong($sqlRes)
    {
        if (!$sqlRes) {
            $this->failure(ERROR_RUN_SQL_FAILED, '内部数据库错误');
            return true;
        } else {
            return false;
        }
    }

    protected function responseBySQLRes($ok)
    {
        if ($this->checkIfSQLResWrong($ok)) {
            return;
        }
        $this->succeed();
    }

    protected function checkIfParamsNotExist($request, $params, $checkEmpty = true)
    {
        foreach ($params as $param) {
            if (isset($request[$param]) == false) {
                $this->failureOfParam($param);
                return true;
            }
            if ($checkEmpty) {
                $trim = trim($request[$param]);
                if ($trim === '') {
                    $this->failureOfParam($param);
                    return true;
                }
            }
        }
        return false;
    }

    private function checkIfParamNotExists($param, $value)
    {
        if ($value == null || trim($value) === '') {
            $this->failureOfParam($param);
            return true;
        }
        return false;
    }

    protected function checkIfNotAtLeastOneParam($request, $params)
    {
        foreach ($params as $param) {
            if (isset($request[$param])) {
                return false;
            }
        }
        $this->failure(ERROR_AT_LEAST_ONE_UPDATE, "请至少提供一个可以修改的信息");
        return true;
    }

    protected function checkIfObjectNotExists($object)
    {
        if ($object == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "object with that id not exits");
            return true;
        } else {
            return false;
        }
    }

    protected function checkIfAmountWrong($amount)
    {
        if (is_int($amount) == false) {
            $this->failure(ERROR_AMOUNT_UNIT, 'amount 必须为整数, 单位为分钱. 例如 10 元, amount = 1000.');
            return true;
        }
        if ($amount < LEAST_COMMON_REWARD) {
            $this->failure(ERROR_AMOUNT_UNIT, '打赏金额至少为 1 元');
            return true;
        }
        if ($amount > MAX_COMMON_REWARD) {
            $this->failure(ERROR_AMOUNT_UNIT, '打赏金额最多为 1000 元');
            return true;
        }
        return false;
    }

    protected function failureOfParam($param)
    {
        $this->failure(ERROR_MISS_PARAMETERS, "必须提供以下参数且不为空: " . $param);
    }

    protected function requestToken()
    {
        $token = $this->get(KEY_SESSION_TOKEN);
        if (!$token) {
            $token = $this->input->get_request_header(KEY_SESSION_HEADER, TRUE);
            if (!$token) {
                if (isset($_COOKIE[KEY_COOKIE_TOKEN])) {
                    $token = $_COOKIE[KEY_COOKIE_TOKEN];
                }
            }
        }
        return $token;
    }

    protected function checkIfInSession()
    {
        $token = $this->requestToken();
        if ($token == null) {
            return false;
        } else {
            $user = $this->userDao->findUserBySessionToken($token);
            return $user != null;
        }
    }

    protected function checkAndGetSessionUser()
    {
        $user = $this->getSessionUser();
        if ($user == null) {
            $this->failure(ERROR_NOT_IN_SESSION, "未登录");
            return null;
        } else {
            return $user;
        }
    }

    protected function checkIfNotInArray($value, $array)
    {
        foreach ($array as $obj) {
            if ($obj === $value) {
                return false;
            }
        }
        $json = json_encode($array);
        $this->failure(ERROR_PARAMETER_ILLEGAL, "$value 不在 $json 之中");
        return true;
    }

    protected function allOrderStatus()
    {
        return array(
            ORDER_STATUS_NOT_PAID,
            ORDER_STATUS_PAID,
            ORDER_STATUS_CONSENTED,
            ORDER_STATUS_REJECTED,
            ORDER_STATUS_FINISHED
        );
    }

    protected function getSessionUser()
    {
        $token = $this->requestToken();
        if ($token) {
            return $this->userDao->findUserBySessionToken($token);
        } else {
            return null;
        }
    }

    protected function getSkip()
    {
        $skip = 0;
        if (isset($_GET[KEY_SKIP])) {
            $skip = (int)$_GET[KEY_SKIP];
        }
        return $skip;
    }

    protected function getLimit()
    {
        $limit = 100;
        if (isset($_GET[KEY_LIMIT])) {
            $limit = (int)$_GET[KEY_LIMIT];
        }
        if ($limit > 1000) {
            $limit = 1000;
        }
        return $limit;
    }

    protected function castToNumber($genericStringNumber)
    {
        return $genericStringNumber + 0;
    }

    protected function patchParams($selectedKeys)
    {
        $toArray = array();
        foreach ($selectedKeys as $field) {
            $value = $this->patch($field);
            if ($value !== null) {
                $toArray[$field] = $value;
            }
        }
        return $toArray;
    }

    protected function checkIfNotAdmin()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, 'Not allow to do it');
            return true;
        } else {
            $user = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            if ($user != 'admin' && $password != 'Pwx9uVJM') {
                $this->failure(ERROR_PARAMETER_ILLEGAL, '用户名或密码有误');
                return true;
            } else {
                return false;
            }
        }
    }

    private function getOrderNo()
    {
        return getToken(16);
    }

    protected function createChargeThenResponse($amount, $subject, $body, $metaData, $user)
    {
        $orderNo = $this->getOrderNo();
        if (isLocalDebug()) {
            // CodeReviewTest
            $appId = 'app_nn9qHKPafHCSDKq5';
            // $appId = 'app_jTSKu5CmXbHC0q5q';
        } else {
            // CodeReviewProd
            // $appId = 'app_XzDynH4qX5u510mz';
            $appId = 'app_jTSKu5CmXbHC0q5q';
        }
        $ipAddress = $this->input->ip_address();
        if ($ipAddress == '::1') {
            // local debug case
            $ipAddress = '127.0.0.1';
        }
        $ch = \Pingpp\Charge::create(
            array(
                'order_no' => $orderNo,
                'app' => array('id' => $appId),
                'channel' => 'alipay_pc_direct',
                'amount' => $amount,
                'client_ip' => $ipAddress,
                'currency' => 'cny',
                'subject' => $subject,
                'body' => $body,
                'metadata' => $metaData,
                'extra' => array('success_url' => 'http://api.reviewcode.cn/rewards/success')
            )
        );
        if ($ch == null || $ch->failure_code != null) {
            logInfo("charge create failed\n");
            if ($ch != null) {
                logInfo("reason $ch->failure_message");
            }
            $this->failure(ERROR_PINGPP_CHARGE, "创建支付失败");
            return;
        }
        $this->chargeDao->add($orderNo, $amount, $user->id, $ipAddress);

        $this->output->set_status_header(200);
        $this->output->set_content_type('application/json', 'utf-8');
        echo($ch);
    }

}

