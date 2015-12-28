<?php

if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

class BaseController extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        \Pingpp\Pingpp::setApiKey('sk_test_9Giz1SPG8mD4OW94OSTmPGyL');
    }

    protected function response($resultCode, $resultData = null, $resultInfo = null)
    {
        if ($resultData === null) {
            $resultData = new stdClass;
        }
        if ($resultInfo === null) {
            $resultInfo = new stdClass;
        }
        $arr = array(
            'resultCode' => $resultCode,
            'resultData' => $resultData,
            'resultInfo' => $resultInfo
        );
        $this->responseJSON($arr);
    }

    protected function responseJSON($obj) {
        $this->output->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($obj));
    }

    protected function succeed($resultData = null)
    {
        $this->response(REQ_OK, $resultData);
    }

    protected function failure($resultCode, $resultInfo)
    {
        $this->response($resultCode, null, $resultInfo);
    }

    protected function checkIfParamsNotExist($request, $params)
    {
        foreach ($params as $param) {
            if (isset($request[$param]) == false) {
                $this->failureOfParam($param);
                return true;
            }
            $trim = trim($request[$param]);
            if ($trim === '') {
                $this->failureOfParam($param);
                return true;
            }
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

    protected function failureOfParam($param)
    {
        $this->failure(ERROR_MISS_PARAMETERS, "必须提供以下参数且不为空: " . $param);
    }

    protected function requestToken()
    {
        if (isset($_COOKIE[KEY_COOKIE_TOKEN])) {
            $token = $_COOKIE[KEY_COOKIE_TOKEN];
        } else {
            $token = $this->input->get_request_header(KEY_SESSION_HEADER, TRUE);
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

    protected function checkIfNotInSessionAndResponse()
    {
        if ($this->checkIfInSession()) {
            return false;
        } else {
            $this->failure(ERROR_NOT_IN_SESSION, "未登录");
            return true;
        }
    }

    protected function getSessionUser()
    {
        $token = $this->requestToken();
        $user = $this->userDao->findUserBySessionToken($token);
        return $user;
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
}
