<?php

require_once(PHP_ROOT . 'libs/util/HttpRequestHelper.php');
require_once(FFAN_ROOT . 'dao/Activity_BeaconActivityDao.php');
require_once (WEB_ROOT . 'models/extra/AbstractSafeModel.php');

/**
 * beacon营销_beacon活动信息
 * @author llh
 *
 */
class ActivityBeaconActivityModel extends AbstractSafeModel {

//     public $required_fields = array();

//     public function GetResponse() {
//         $response = new Response ();
//         return $response;
//     }
    
    public function __construct() {

    }
    /**
     * 请求路由处理，将不同的请求分发到不同的处理器中进行处理。
     * @param unknown $id
     * @return unknown
     */
    public function DoModel() {
      $response = new Response();
        
      $method_request =  strtoupper($_SERVER['REQUEST_METHOD']);
      $path_info = trim($_SERVER["PATH_INFO"],"/");
      
      $path_info_array=explode("/",$path_info);
      
      $path_length = count($path_info_array) ;
      $model_name = $path_info_array[1];
      
      $response->status="500";
      $response->data="";
      $response->msg="请求方式或请求参数不正确";
      
      if ($model_name!="beaconActivity"){
          return $response;
      }
      
      if($path_length==2  && $method_request=="POST")
      {
          //添加
          return $this->addDetail();
          
      }
      elseif ($path_length==3 && is_numeric($path_info_array["2"])  && $method_request=="GET")
      {
          //获取数据详情
          $id=intval( $path_info_array["2"]);
          return $this->getDetail($id);
          
      }
      elseif ($path_length==3 && is_numeric($path_info_array["2"])  && $method_request=="POST")
      {
          
          //编辑数据
          $id=intval( $path_info_array["2"]);
          return $this->updateDetail($id);
          
      }
      elseif ($path_info_array["2"]=="activities" && $method_request=="GET")
      {
          return $this->getListWithJoin();
      }
      /*
      elseif ($path_info_array["3"]=="delete" && $method_request=="POST")
      {
          //删除数据
          $id=$path_info_array["2"];
          return $this->deleteDetail($id);
      }
      
      elseif ($path_info_array["2"]=="shakeActivity"  && $method_request=="GET")
      {
          return $this->shakeActivity();
      }
      if ($path_info_array["2"]=="batchUpdate" && $method_request=="POST")
      {
          return $this->batchUpdate();
      }
      elseif ($path_info_array["1"]=="getStockList" && $method_request=="GET")
      {
          return $this->getList();
      }*/
      else
      {
          return $response;
      }
    }

    public function getListWithJoin()
    {
        $response = new Response();
        
        $condition_offset = HttpRequestHelper::GetParam("offset");
        $condition_offset = (is_numeric($condition_offset) && $condition_offset>0)? intval($condition_offset):0;

        $condition_limit = HttpRequestHelper::GetParam("limit");
        $condition_limit =(is_numeric($condition_limit) && $condition_limit>0)? intval($condition_limit):10 ;

        $condition_orderbyFields = HttpRequestHelper::GetParam("orderbyFields");
        $condition_orderbyType = HttpRequestHelper::GetParam("orderbyType");
        
        $conditions = $_GET ;
        
        $result=Activity_BeaconActivityDao::getListWithJoin($conditions,$condition_orderbyFields,$condition_orderbyType ,$condition_offset,$condition_limit);
        if (isset($result["error"])){
            $response->status="500";
            $response->data="";
            $response->msg="数据获取失败[".$result["error"]."]";
            return $response;
        }
        
        $response->status="200";
        $response->data=$result;
        $response->msg="数据获取成功";
        return $response;
    }

    /*

    public function batchUpdate(){
    

        $hotelId=HttpRequestHelper::PostParam("hotelId");
        $status=HttpRequestHelper::PostParam("status");
        $updateNum=HttpRequestHelper::PostParam("updateNum"); 
        
        $roomDateStart = HttpRequestHelper::PostParam("roomDateStart");
        $roomDateEnd = HttpRequestHelper::PostParam("roomDateEnd");

        $roomIdList = HttpRequestHelper::PostParam("roomId");
        $roomTypeList = HttpRequestHelper::PostParam("roomType");
        
        $updateUser = HttpRequestHelper::PostParam("updateUser");

        $msg = "";
        $currDate = strtotime(date('Y-m-d')." 00:00:00");
        
        if (!$hotelId || !is_numeric($hotelId)){
            $msg="必须参数缺失或错误[hotelId]";
        }elseif (!$status && !$updateNum){
            $msg="必须参数缺失或错误,无法确认请求的操作!";
        }elseif($updateNum && $status==2){
            $msg="参数不匹配或异常!";
        }elseif(!$updateUser){
            $msg="必须参数缺失或错误[updateUser]";
        }elseif(!$roomIdList){
            $msg="必须参数缺失或错误[roomId]";
        }elseif(!$roomTypeList){
            $msg="必须参数缺失或错误[roomType]";
        }elseif (!is_numeric($roomDateStart) || strlen($roomDateStart)!=10 ){
            $msg="必须参数缺失或错误[roomDateStart]";
        }elseif (!is_numeric($roomDateEnd) || strlen($roomDateEnd)!=10 ){
            $msg="必须参数缺失或错误[roomDateEnd]";
        }elseif ($roomDateStart <  $currDate ){
            $msg="参数值非法[roomDateStart必须大于当前时间]";
        }elseif ($roomDateEnd < $currDate  ){
            $msg="参数值非法[roomDateEnd必须大于当前时间]";
        }elseif ($roomDateEnd < $roomDateStart ){
            $msg="参数值非法[roomDateEnd必须大于roomDateStart]";
        }

        if (is_numeric($roomIdList)){
            $roomIdList = array($roomIdList);
        }elseif (!is_array($roomIdList)){
            $msg="必须参数错误[roomId]";
        }
        if (is_numeric($roomTypeList)){
            $roomTypeList = array($roomTypeList);
        }elseif (!is_array($roomTypeList)){
            $msg="必须参数错误[roomType]";
        }
        
        if ($msg){
            $response->status="400";
            $response->data=null;
            $response->msg=$msg;
            return $response;
        }

        $field = array('room_id','name','status','room_nums');
        $tempData = HotelDao::GetHotelRoomAll(array("hotel_id"=>$hotelId,"status"=>1),$field);
        $roomData = array();
        foreach ($tempData as $row ){
            $roomData[$row["room_id"]]=$row["room_nums"];
        }
        
        $roomDateStart =  strtotime(date('Y-m-d', $roomDateStart)." 00:00:00");
        $roomDateEnd =  strtotime(date('Y-m-d', $roomDateEnd)." 00:00:00");

        $param = array("hotelId"=>$hotelId,"roomDateStart"=>$roomDateStart,"roomDateEnd"=>$roomDateEnd,"roomId"=>$roomIdList,"roomType"=>$roomTypeList,);
        $data = Hotel_StockDao::getList(null,$param);
        if (isset($data["error"])){
            $response->status="500";
            $response->data="";
            $response->msg="数据获取失败[".$data["error"]."]";
            return $response;
        }
        
// print_r($data);exit();
        
        $data = Hotel_StockDao::groupList($data["datas"]);
        
//         $data = Hotel_StockDao::fillData($conditions["roomDateStart"],$conditions["roomDateEnd"],$data,$roomIdList);

        $roomCount = array();//分房型统计
        $hotelCount = array(); //整个酒店统计

        $updateList = array();
        $insertList = array();
        
        //$roomDateStart,$roomDateEnd
        //60*60*24 = 86400
        for ($i = $roomDateStart ; $i<= $roomDateEnd ; $i=$i + 86400  ){
            $roomDate =  date('Y-m-d', $i);
        
            foreach ($roomIdList as $roomId){
                foreach ($roomTypeList as $key){
                    $temp = $data[$i][$roomId][$key] ;
                    if (!$temp){
                        $temp =  array(
                            //"id"=>"id",
                            "hotelId"=>$hotelId,
                            "roomDate"=>$i,
                            "roomId"=>$roomId,
                            "roomType"=>$key,
                            
                            "stockTotal"=>0,
                            "stockSale"=>0,
                            "stockLocked"=>0,
                            "stockLeft"=>0,
                            "stockRefund"=>0,
                            
//                             "updateTime"=>time(),
                            "updateUser"=>$updateUser,
                            "status"=>$status
                        );
                    }
                    if ($status){
                        $temp["status"] = $status;
                    }
                    if ($updateNum){
                        $temp["stockTotal"] += $updateNum;
                        $temp["stockLeft"] += $updateNum;
                        $temp["status"] = 1;
                        if ($temp["stockLeft"]<0){//如果剩余房量下雨0  则抵平
                            $temp["stockTotal"] -= $temp["stockLeft"];
                            $temp["stockLeft"] -= $temp["stockLeft"];
                        }
                        if ($temp["stockLeft"]>$roomData[$roomId]){
                            $temp["stockTotal"] = $roomData[$roomId];
                            $temp["stockLeft"] = $roomData[$roomId];
                        }
                    }
                    if ($key==3){//超售房
                        $temp["stockTotal"] = "999999999";
                        $temp["stockLeft"] = "999999999";
                    }
                    
                    if ($temp["id"]){
                        $updateList[$temp["id"]] = $temp;
                    }else {
                        $insertList[] = $temp;
                    }
                }
            }
        }

        $updateList = Hotel_StockDao::batchUpdate($updateList);
        $insertList = Hotel_StockDao::batchInsert($insertList);

        $data = array("update"=>$updateList,"insert"=>$insertList);
        
        $response->status=0;
        $response->data=$data;
        $response->msg="数据获取成功";
        return $response;
    }*/
    
    
    public function getDetail($id)
    {
        $response = new  Response();

        $condition_fields=array();
        $fields=HttpRequestHelper::GetParam('fieldsList');
        if($fields)
        {
            $fields_arr=explode(",",$fields);
            $condition_fields = $fields_arr;
        }
        else
        {
            $condition_fields = null;
        }

        $result = Activity_BeaconActivityDao::getDetail($id,$condition_fields);
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
     * 删除房源信息
     * @param unknown $id
     * @return unknown
     */
    /*
    public function deleteDetail($id)
    {
        $response = new  Response();
        $conditions["status"]=2;
        $result=Hotel_StockDao::update($id,$conditions);
        if(isset($result["error"])|| !$result){//===false
            $response->status="500";
            $response->data="";
            if (isset($result["error"])){
                $response->msg= $result["error"];
            }elseif (is_numeric($result) && $result==0){
                $response->msg= "参数数据异常或所操作数据状态异常!";
            }else {
                $response->msg= "数据操作失败!";
            }
            return $response;
        }
        
        $response->status="200";
        $response->data=$result;
        $response->msg="数据操作成功!";
        return $response;
    }
    
 */
    
    public function addDetail()
    {
        $response = new Response();

        $result=Activity_BeaconActivityDao::insert($_POST);

        //if( (is_array($result) && isset($result["error"]))|| !$result){//===false
        if(isset($result["error"])|| !$result){//===false
            $response->status="500";
            $response->data="";
            if (isset($result["error"])){
                $response->msg= $result["error"];
            }elseif (is_numeric($result) && $result<1){
                $response->msg= "插入异常!";
            }else {
                $response->msg= "数据添加失败";
            }
            return $response;
        }

        $response->status="200";
        $response->data=array("id"=>$result,   );
        $response->msg="数据添加成功";
        return $response;
            
}
    
    public function updateDetail($id)
    {
        $response = new Response();

        $result=Activity_BeaconActivityDao::update($id,$_POST);
        //if( (is_array($result) && isset($result["error"]))|| !$result){
        if(isset($result["error"])|| !$result){//===false
            $response->status="500";
            $response->data="";
            if (isset($result["error"])){
                $response->msg= $result["error"];
            }elseif (is_numeric($result) && $result==0){
                $response->msg= "参数数据异常或所操作数据已经被改变!";
            }else {
                $response->msg= "数据操作失败!";
            }
            return $response;
        }
        
        $response->status="200";
        $response->data="";
        $response->msg="数据修改成功";
        return $response;
        
    }
    
    
    }
