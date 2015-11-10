<?php

require_once(PHP_ROOT . 'libs/util/Utility.php');
require_once(WEB_ROOT . 'models/extra/ErrorMsg.php');
require_once(WEB_ROOT . 'models/extra/Response.php');
require_once(WEB_ROOT . 'models/extra/AbstractSafeModel.php');
require_once(PHP_ROOT . 'libs/util/HttpRequestHelper.php');
require_once (FFAN_ROOT . 'dao/v2/AdminLogDao.php');
class AdminLogModel extends AbstractSafeModel {
    public function GetResponse() {
        $response = new Response ();
        return $response;
    }

    /**
     * 请求路由处理，将不同的请求分发到不同的处理器中进行处理。
     * @param unknown $id
     * @return unknown
     */
    public function DoModel()
    {
        $response = new Response();

        $method_request = strtoupper($_SERVER['REQUEST_METHOD']);
        $path_info = trim($_SERVER["PATH_INFO"], "/");

        $path_info_array = explode("/", $path_info);

        if(count($path_info_array)==2 && $path_info_array[1]=="adminlog" && $method_request=="POST")
        {
            return $this->addAdminLog();

        }
        elseif ($path_info_array[1]=="adminloglist" && $method_request=="GET")
        {
            return $this->getAdminLogList();
        }
        else {
            $response->status = "500";
            $response->msg = "请求方式或请求参数不正确";
            return $response;
        }
    }

    /**
     * BP活动券列表
     *
     * 邹毅(zouyi6)
     */
    public function getAdminLogList()
    {

        $response = new Response();

        if(!isset($_GET['activityId']) || !$_GET['activityId']){

            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "未指定活动!";
            return $response;
        }

        $conditions['activityId'] = HttpRequestHelper::GetParam("activityId");
        $limit = HttpRequestHelper::GetParam("limit")?HttpRequestHelper::GetParam("limit"):"10";
        $offset = HttpRequestHelper::GetParam("offset")?HttpRequestHelper::GetParam("offset"):"0";
        $orderByType=HttpRequestHelper::GetParam("orderByType")?HttpRequestHelper::GetParam("orderByType"):"desc";
        $orderByFields=HttpRequestHelper::GetParam("orderByFields")?HttpRequestHelper::GetParam("orderByFields"):"id";

        $result=AdminLogDao::getAdminLogList($conditions,$offset,$limit,$orderByType,$orderByFields);

        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }

        $response->status="200";
        $response->data=$result;
        $response->msg="列表获取请求返回正常";
        return $response;
    }


    public function addAdminLog()
    {
        $response = new  Response();

        $conditions=$_POST;
        $result = AdminLogDao::addAdminLog($conditions);
        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }


        $response->status="200";
        $response->data=$result;
        $response->msg="增加记录成功";
        return $response;
    }

}
