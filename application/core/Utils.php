<?php
/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午3:44
 */

if (!function_exists('checkIfParamsNotExist')) {
    function checkIfParamsNotExist($request, $params)
    {
        foreach ($params as $param) {
            if (isset($request[$param]) == false) {
                responseJson(4, null, "必须提供以下参数: " . $param);
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('responseJson')) {
    function responseJson($resultCode, $resultData, $resultInfo)
    {
        header("Content-type:application/json;charset=utf-8");
        $arr = array(
            'resultCode' => $resultCode,
            'resultData' => $resultData,
            'resultInfo' => $resultInfo
        );
        echo json_encode($arr);;
    }
}