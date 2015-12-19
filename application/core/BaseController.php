<?php

if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

class BaseController extends CI_Controller
{
    protected function response($resultCode, $resultData = null, $resultInfo = null)
    {
        $arr = array(
            'resultCode' => $resultCode,
            'resultData' => $resultData,
            'resultInfo' => $resultInfo
        );
        $this->output->set_status_header(200);
        $this->output->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($arr));
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
        return $limit;
    }

    protected function castToNumber($genericStringNumber)
    {
        return $genericStringNumber + 0;
    }
}
