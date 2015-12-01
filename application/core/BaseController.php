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
        if ($resultCode == REQ_OK) {
            $this->output->set_status_header(200);
        } else {
            $this->output->set_status_header(400);
        }
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
                $this->failure(ERROR_MISS_PARAMETERS, "必须提供以下参数: " . $param);
                return true;
            }
        }
        return false;
    }
}
