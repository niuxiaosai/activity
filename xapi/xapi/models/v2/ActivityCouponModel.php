<?php

require_once(PHP_ROOT . 'libs/util/Utility.php');
require_once(WEB_ROOT . 'models/extra/ErrorMsg.php');
require_once(WEB_ROOT . 'models/extra/Response.php');
require_once(WEB_ROOT . 'models/extra/AbstractSafeModel.php');
require_once(PHP_ROOT . 'libs/util/HttpRequestHelper.php');
require_once(WEB_ROOT . 'common/GatewayHelper.php');
require_once(PHP_ROOT . '/libs/util/HttpHandlerCurl.php');
require_once (FFAN_ROOT . 'dao/v2/ActivityCouponDao.php');

class ActivityCouponModel extends AbstractSafeModel {
    protected $gateway ;
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

        if(count($path_info_array)==2 && $path_info_array[1]=="activitycoupon" && $method_request=="POST")
        {
            //添加券信息
            return $this->addActivityCoupon();

        }
        elseif (count($path_info_array)==3 && $path_info_array[1]=="activitycoupon"  && $method_request=="GET")
        {
            //获取券详情
            /**
             * 此接口仅供前台调用，传参为couponNumber
             */
            $id=$path_info_array["2"];
            return $this->getActivityCouponByCouponNum($id);

        }
        //getPlazaList
        elseif (count($path_info_array)==3 && $path_info_array[1]=="getplazalist"  && $method_request=="GET")
        {
            //获取券详情
            /**
             * 此接口仅供前台调用，传参为couponNumber
             */
            $id=$path_info_array["2"];
            return $this->getActivityPlazaList($id);

        }
        elseif (count($path_info_array)==4 && $path_info_array[3]=="update" && $path_info_array[1]=="activitycoupon"  && $method_request=="POST")
        {

            //编辑券数据
            $id=$path_info_array["2"];
            return $this->updateActivityCoupon($id);

        }

        elseif (count($path_info_array)==4 && $path_info_array["3"]=="delete" && $path_info_array[1]=="activitycoupon"  && $method_request=="GET")
        {
            //删除券数据
            $id=$path_info_array["2"];
            return $this->deleteActivityCoupon($id);
        }
        elseif (count($path_info_array)==3 && $path_info_array["1"]=="activitycouponlist" && $method_request=="GET")
        {
            $id=$path_info_array["2"];
            return $this->getActivityCouponList($id);
        }
        elseif (count($path_info_array)==3 && $path_info_array["1"]=="activitycouponnumber" && $method_request=="GET")
        {
            $id=$path_info_array["2"];

            return $this->ActivityCouponNumber($id);
        }

        elseif (count($path_info_array)==3 && $path_info_array["2"]=="update" && $path_info_array[1]=="activitycoupons"  && $method_request=="POST")
        {
            return $this->updateActivityCoupons();

        }
        elseif (count($path_info_array)==3 && $path_info_array["2"]=="delete" && $path_info_array[1]=="activitycoupons"  && $method_request=="POST")
        {
            return $this->deleteActivityCoupons();

        }
        elseif ($path_info_array[1] == 'couponlist') {
            return $this->getCouponList();
        } else {
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
    public function getActivityCouponList($id)
    {
        $response = new  Response();

        $condition=$_GET;

        if(isset($condition['offset'])){
            $offset = intval($condition['offset']);
        }else{
            $offset = 0;
        }

        if(isset($condition['limit'])){
            $limit = intval($condition['limit']);
        }else{
            $limit = 10;
        }
            //获取活动券列表
            $result = ActivityCouponDao::activityCoupons($id,$condition,$offset,$limit);

        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }

        //从基础数据获取券详情
        $coupon_numbers = array();
        foreach($result['datas'] as $product){
            $coupon_numbers[] = $product['coupon_number'];
        }
        $CouponCondition['productNos']=$coupon_numbers;
        $this->geteway  = new GatewayHelper();

        $couponList = $this->geteway->getCouponList($CouponCondition,round($offset/$limit+1),$limit);
        if (isset($couponList["error"]) || !$couponList ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($couponList["error"]) ? $couponList["error"] : "数据获取失败!";
            return $response;
        }

        foreach($result['datas'] as $key => $res) {
            foreach ($couponList['datas'] as $product) {
                if($res['coupon_number'] ==$product['productNo']){
                    $result['datas'][$key] = $result['datas'][$key]+ $product;
                }
            }
        }
        $response->status="200";
        $response->data=$result;
        $response->msg="数据获取成功";

        return $response;


    }
    /**
     * BP券是否在活动中，不走券商品接口
     *
     * 邹毅(zouyi6)
     */
    public function ActivityCouponNumber($id)
    {
        $response = new  Response();

        $condition=$_GET;

        if(!isset($condition['plazaId']) && !isset($condition['couponNumber'])){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "未定义参数!";
            return $response;
        }

        //获取活动券列表
        $result = ActivityCouponDao::CheckActivityCouponNumber($id,$condition);

        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }
        $response->status="200";
        //{'query_number':[123,1234,543,33],'coupon_number':[123,1234,543]}
        $response->data=$result;
        $response->msg="数据获取成功";

        return $response;


    }

    public function addActivityCoupon()
    {
        $response = new  Response();

        $conditions=$_POST;
        $result = ActivityCouponDao::insert($conditions);
        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }


        $response->status="200";
        $response->data=$result;
        $response->msg="数据获取成功";
        return $response;
    }

    public function addActivityCoupons()
    {
        $response = new  Response();

        $conditions=$_POST;
        $result = ActivityCouponDao::batchInsert($conditions);
        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }


        $response->status="200";
        $response->data=$result;
        $response->msg="数据获取成功";
        return $response;
    }
    /**
     *
     * 调用券接口查询券id
     * @param $id
     * @return Response
     */
    public function getActivityCoupon($id)
    {
        $response = new  Response();


        $result = ActivityCouponDao::getActivityCoupon($id);
        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }


        $response->status="200";
        $response->data=$result;
        $response->msg="数据获取成功";
        return $response;
    }
    /**
     *
     * 调用券接口查询券id
     * @param $id
     * @return Response
     */
    public function getActivityCouponByCouponNum($id)
    {
        $response = new  Response();

        $result = ActivityCouponDao::getActivityCouponByCouponNum($id);
        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }

//        //从基础数据获取券详情
//        $CouponCondition['productNo']=$id;
//        $this->geteway  = new GatewayHelper();
//
//        $couponList = $this->geteway->getCouponList($CouponCondition);
//
//        if (isset($couponList["error"]) || !$couponList ){
//            $response->status="500";
//            $response->data="";
//            $response->msg=isset($couponList["error"]) ? $couponList["error"] : "数据获取失败!";
//            return $response;
//        }
//        $result['detail'] = $couponList;

        $response->status="200";
        $response->data=$result;
        $response->msg="数据获取成功";
        return $response;
    }

    public function deleteActivityCoupon($id)
    {
        $response = new  Response();

        $result = ActivityCouponDao::deleteActivityCoupon($id);
        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据删除失败!";
            return $response;
        }


        $response->status="200";
        $response->msg="数据删除成功";
        return $response;
    }
    public function updateActivityCoupon($id)
    {
        $response = new  Response();

        $condition = $_POST;

        $result = ActivityCouponDao::update($id,$condition);
        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }


        $response->status="200";
        $response->data=$result;
        $response->msg="数据获取成功";
        return $response;
    }
    public function deleteActivityCoupons(){
        $response = new  Response();

        $ids = HttpRequestHelper::PostParam('id');
        if(!$ids){
            $response->status="404";
            $response->data='';
            $response->msg="未指定删除id";
            return $response;
        }
        $ids = explode(',', $ids);

        $result = ActivityCouponDao::batchDelete($ids);
        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }


        $response->status="200";
        $response->data=$result;
        $response->msg="数据获取成功";
        return $response;
    }
    public function updateActivityCoupons()
    {
        $response = new  Response();

        $ids = HttpRequestHelper::PostParam('id');
        if(!$ids){
            $response->status="404";
            $response->data='';
            $response->msg="未指定删除id";
            return $response;
        }
        $ids = explode(',', $ids);

        $condition = array();

        $unit = HttpRequestHelper::PostParam('unit');
        if(isset($unit)){
            $condition['unit'] = $unit;
        }
        $dayLimit = HttpRequestHelper::PostParam('dayLimit');
        if(isset($dayLimit)){
            $condition['dayLimit'] = $dayLimit;
        }
        $endTime = HttpRequestHelper::PostParam('endTime');
        if(isset($endTime)){
            $condition['endTime'] = $endTime;
        }
        $rank = HttpRequestHelper::PostParam('rank');
        if(isset($rank)){
            $condition['rank'] = $rank;
        }


        $result = ActivityCouponDao::batchUpdate($ids,$condition);
        if (isset($result["error"]) || !$result ){
            $response->status="500";
            $response->data="";
            $response->msg=isset($result["error"]) ? $result["error"] : "数据获取失败!";
            return $response;
        }


        $response->status="200";
        $response->data=$result;
        $response->msg="数据获取成功";
        return $response;
    }
    // 获取前端券列表
    public function getCouponList()
    {
        $response = new Response();
        $activity_id = intval(HttpRequestHelper::GetParam('activityId'));
        if (empty($activity_id)) {
            $response->status = 400;
            return $response;
        }
        $plaza_id = HttpRequestHelper::GetParam('plazaId');
        $title = HttpRequestHelper::GetParam('title');
        $page = intval(HttpRequestHelper::GetParam('page'));
        if ($page >= 1) {
            $page--;
        }
        $page_size = intval(HttpRequestHelper::GetParam('pageSize'));
        if (empty($page_size)) {
            $page_size = 10;
        }
        $uid = HttpRequestHelper::GetParam('uid');
        $coupon_number = HttpRequestHelper::GetParam('couponNumber');
        $list = ActivityCouponDao::getCouponList($activity_id, $plaza_id, $title, $page, $page_size, $uid, $coupon_number);
        // 查询券接口，获得详细信息；查询db，获得库存情况
        if (!empty($list['list'])) {
            $coupon_numbers = array();
            foreach ($list['list'] as $tmp) {
                $coupon_numbers[] = $tmp['coupon_number'];
            }
            $info = $this->getCouponDetail($coupon_numbers);
            if ($info['status'] != 200) {
                $response->status = 500;
                return $response;
            }
            $datas = $info['data']['datas'];
            foreach ($datas as $key => $tmp) {
                $datas[$tmp['productNo']] = $tmp;
                unset($datas[$key]);
            }
            foreach ($list['list'] as $key => $tmp) {
                if ((!empty($datas[$tmp['coupon_number']]['vendibility']) && $datas[$tmp['coupon_number']]['saleStatus'] != 2)
                    || (empty($datas[$tmp['coupon_number']]['vendibility']) && $datas[$tmp['coupon_number']]['saleStatus'] != 10)
                ) {
                    unset($list['list'][$key]);
                    $list['count']--;
                    continue;
                }
                $list['list'][$key]['detail'] = $datas[$tmp['coupon_number']];
            }
        }

        $response->status = 200;
        $response->data = $list;
        return $response;
    }

    private function getCouponDetail(array $coupon_numbers)
    {
        if (count($coupon_numbers) == 1) {
            $http = new HttpHandlerCurl('UTF-8', 1);
            $url = GATEWAY_URL_ROOT . 'coupon/v2/products/' . $coupon_numbers[0] . '?source=4';
            $result = json_decode($http->get($url), true);
            return array(
                'status' => $result['status'],
                'data' => array('datas' => array($result['data']))
            );
        } else {
            $http = new HttpHandlerCurl('UTF-8', 1);
            $url = sprintf('%scoupon/v2/products?source=4&productNos=%s', GATEWAY_URL_ROOT, json_encode($coupon_numbers));
            return json_decode($http->get($url), true);
        }
    }

    public function getActivityPlazaList($activityId)
    {
        $response = new Response();
        $list = ActivityCouponDao::getActivityPlazaList($activityId);
        $list['activityId'] = $activityId;
        $response->status = 200;
        $response->data = $list;

        return $response;
    }

}
