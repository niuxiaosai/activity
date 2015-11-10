<?php
require_once(PHP_ROOT . 'libs/util/MysqlClient.php');
require_once(PHP_ROOT . 'libs/util/Utility.php');
require_once(FFAN_ROOT . 'dao/Activity_BeaconStoreDao.php'); 

class Activity_BeaconActivityDaoImpl{
  const DB_NAME = 'ff_cloud_marketing_platform';
  const TABLE_NAME="activity_beacon_activity";
//   const TABLE_RENTAL="rental";
  

//   protected static $table_fields_ = array("id","title","description","url","type",
//       "related_id","is_first","sort","status","update_time","update_user",);
  protected static $table_fields_map_ = array(
        "id"=>"id",
        "beaconStoreMapId"=>"beacon_store_map_id",
        "name"=>"name",
        "code"=>"code",
        "beginDate"=>"begin_date",
        "endDate"=>"end_date",
        "urlSort"=>"url_sort",
        "urlContent"=>"url_content",
        "status"=>"status",
        "createAdminId"=>"create_admin_id",
        "createAdminName"=>"create_admin_name",
        "createTime"=>"create_time",
  );

  protected static $required_fields=array(
        "beaconStoreMapId",
        "name",
        "beginDate",
        "endDate",
        "urlSort",
        "urlContent",
        "status",
        "createAdminId",
        "createAdminName",
  );

  public static $orderByType_enum =array("desc","asc","");
  
  const ORDERBYFIELDS_DEFAULT = "createTime";//默认排序列
  const ORDERBYTYPE_DEFAULT = "desc";//默认排序方向
  
  protected static $beaconStore_fields_map_ = array(
//       "id"=>"id",
      "cityId"=>"city_id",
      "plazaId"=>"plaza_id",
      "storeId"=>"store_id",
      "extraName"=>"extra_name",
      "beaconUUID"=>"beacon_uuid",
      "beaconMajor"=>"beacon_major",
      "beaconMinor"=>"beacon_minor",
      "floorId"=>"floor_id",
      "commonAreaImg"=>"common_area_img",
//       "createAdminId"=>"create_admin_id",
//       "createAdminName"=>"create_admin_name",
//       "createTime"=>"create_time",
  );
  
  
  public static function getList($fields,$conditions,$orderbyFields ,$orderbyType,$offset=0,$limit=10) {
      
     $orderbyFields = $orderbyFields ? $orderbyFields : self::ORDERBYFIELDS_DEFAULT ;
      if (!isset( self::$table_fields_map_[trim($orderbyFields)] )) {
         return array("error"=>"orderbyFields非法![".print_r($orderbyFields,1)."]");
     }
     $orderbyFields = self::$table_fields_map_[trim($orderbyFields)] ;
     
     $orderbyType = $orderbyType ? $orderbyType : self::ORDERBYTYPE_DEFAULT ;
     if (!in_array(strtolower(trim($orderbyType)), self::$orderByType_enum)){
         return array("error"=>"orderbyType非法![".print_r($orderbyType,1)."]");
     }
      
      $condition_where = " where 1=1 ";
      $condition_where.=  !isset($conditions["status"]) ? "" : " and status = ".$conditions["status"]." "  ;

      if ( $conditions["id"] && is_numeric($conditions["id"])){
          $condition_where.=" and id=".$conditions["id"];
      }elseif ( $conditions["id"] && is_array($conditions["id"])){
          $condition_where.=" and ".self::$table_fields_map_["id"] ." in (".implode(",", $conditions["id"]) .") ";
      }
      if ( $conditions["beaconStoreMapId"] && is_numeric($conditions["beaconStoreMapId"])){
          $condition_where.=" and ".self::$table_fields_map_["beaconStoreMapId"] ."=".$conditions["beaconStoreMapId"];
      }elseif ( $conditions["beaconStoreMapId"] && is_array($conditions["beaconStoreMapId"])){
          $condition_where.=" and ".self::$table_fields_map_["beaconStoreMapId"] ." in (".implode(",", $conditions["beaconStoreMapId"]) .") ";
      }
      if ($conditions["name"]){
          $condition_where.=" and ".self::$table_fields_map_["name"] ." like '%".trim( $conditions["name"]) ."%' ";
      }
      if ($conditions["code"]){
          $condition_where.=" and ".self::$table_fields_map_["code"] ." = '".trim( $conditions["code"]) ."' ";
      }
//       "beginDate"=>"begin_date",
//       "endDate"=>"end_date",
      if ( $conditions["urlSort"] && is_numeric($conditions["urlSort"])){
          $condition_where.=" and ".self::$table_fields_map_["urlSort"] ."=".$conditions["urlSort"];
      }
//       "urlContent"=>"url_content",
      if ( $conditions["createAdminId"] && is_numeric($conditions["createAdminId"])){
          $condition_where.=" and ".self::$table_fields_map_["createAdminId"] ."=".$conditions["createAdminId"];
      }
      if ($conditions["createAdminName"]){
          $condition_where.=" and ".self::$table_fields_map_["createAdminName"] ." = '".trim( $conditions["createAdminName"]) ."' ";
      }

      if ($conditions["createTimeStart"]){
          if (!is_numeric($conditions["createTimeStart"]) || strlen($conditions["createTimeStart"])!=10 ){
              return array("error"=>"createTimeStart非法![".$conditions["createTimeStart"]."]");
          }
          $condition_where.=" and ".self::$table_fields_map_["createTime"] ." >= ".$conditions["createTimeStart"]." ";
      }
      if ($conditions["createTimeEnd"]){
          if (!is_numeric($conditions["createTimeEnd"]) || strlen($conditions["createTimeEnd"])!=10 ){
              return array("error"=>"createTimeEnd非法![".$conditions["createTimeEnd"]."]");
          }
          $condition_where.=" and ".self::$table_fields_map_["createTime"] ." <= ".$conditions["createTimeEnd"]." ";
      }

      if ($conditions["beginDateStart"]){
          if (!is_numeric($conditions["beginDateStart"]) || strlen($conditions["beginDateStart"])!=10 ){
              return array("error"=>"beginDateStart非法![".$conditions["beginDateStart"]."]");
          }
          $condition_where.=" and ".self::$table_fields_map_["beginDate"] ." >= ".$conditions["beginDateStart"]." ";
      }
      if ($conditions["beginDateEnd"]){
          if (!is_numeric($conditions["beginDateEnd"]) || strlen($conditions["beginDateEnd"])!=10 ){
              return array("error"=>"beginDateEnd非法![".$conditions["beginDateEnd"]."]");
          }
          $condition_where.=" and ".self::$table_fields_map_["beginDate"] ." <= ".$conditions["beginDateEnd"]." ";
      }
      if ($conditions["endDateStart"]){
          if (!is_numeric($conditions["endDateStart"]) || strlen($conditions["endDateStart"])!=10 ){
              return array("error"=>"endDateStart非法![".$conditions["endDateStart"]."]");
          }
          $condition_where.=" and ".self::$table_fields_map_["endDate"] ." >= ".$conditions["endDateStart"]." ";
      }
      if ($conditions["endDateEnd"]){
          if (!is_numeric($conditions["endDateEnd"]) || strlen($conditions["endDateEnd"])!=10 ){
              return array("error"=>"endDateEnd非法![".$conditions["endDateEnd"]."]");
          }
          $condition_where.=" and ".self::$table_fields_map_["endDate"] ." <= ".$conditions["endDateEnd"]." ";
      }

      $condition_fields=array();
      if (!isset($fields)){
          foreach (self::$table_fields_map_ as $lable=>$field){
              $condition_fields[] = $field." ".$lable;
          }
      }else {
          foreach ($fields as $key=>$value)
          {
              if(self::$table_fields_map_[$value])
              {
                  $condition_fields[] = self::$table_fields_map_[$value]." ".$value;
              }
          }
      }
      if (empty($condition_fields)){
          $condition_fields[]="id id";
      }
  
      $conditions_limit = ' limit ' . $offset . ',' . $limit;
      $conditions_orderby = " order by ".$orderbyFields." ".$orderbyType." ";
      $total = 0;
      $list = array();
      //获取总条数
      $count = MysqlClient::QueryCount(self::DB_NAME,self::TABLE_NAME,$condition_where);
      if(!empty($count)) {
          $total = $count;
          $list = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$condition_fields,$condition_where.$conditions_orderby.$conditions_limit);
      }
      return array("datas"=>$list,"count"=>$count);
  }
  

  public static function getListWithJoin($conditions,$orderbyFields ,$orderbyType,$offset=0,$limit=10) {
  
      $orderbyFields = $orderbyFields ? $orderbyFields : self::ORDERBYFIELDS_DEFAULT ;
      if (!isset( self::$table_fields_map_[trim($orderbyFields)] )) {
          return array("error"=>"orderbyFields非法![".print_r($orderbyFields,1)."]");
      }
      $orderbyFields = self::$table_fields_map_[trim($orderbyFields)] ;
       
      $orderbyType = $orderbyType ? $orderbyType : self::ORDERBYTYPE_DEFAULT ;
      if (!in_array(strtolower(trim($orderbyType)), self::$orderByType_enum)){
          return array("error"=>"orderbyType非法![".print_r($orderbyType,1)."]");
      }
      
//       $conditions["extraName"] = HttpRequestHelper::GetParam("extraName");
  
      $condition_where = " where 1=1 ";
      $condition_where.=  !isset($conditions["status"]) ? ""  : " and a.status = ".$conditions["status"]." "  ;
  
      if ( $conditions["id"] && is_numeric($conditions["id"])){
          if ($conditions["id"] <1 ){
             $condition_where.=" and a.id=".$conditions["id"];
          }else {
              $condition_where.=" and a.id != null ";
          }
      }
      if ($conditions["name"]){
          $condition_where.=" and a.name like '%".trim( $conditions["name"]) ."%' ";
      }
      if ( $conditions["code"] ){
          $condition_where.=" and a.code ='".$conditions["code"]."' ";
      }
      if ( $conditions["urlSort"] && is_numeric($conditions["urlSort"])){
          $condition_where.=" and a.url_sort =".$conditions["urlSort"];
      }
      if ($conditions["urlContent"]){
          $condition_where.=" and a.urlContent like '%".trim( $conditions["urlContent"]) ."%' ";
      }
      if ( $conditions["createAdminId"] && is_numeric($conditions["createAdminId"])){
          $condition_where.=" and a.create_admin_id =".$conditions["createAdminId"];
      }
      if ($conditions["createAdminName"]){//"createAdminName"=>"create_admin_name",
          $condition_where.=" and a.create_admin_name ='".$conditions["createAdminName"]."'";
      }
      
      if ( $conditions["beaconStoreMapId"] && is_numeric($conditions["beaconStoreMapId"])){
          $condition_where.=" and s.id=".$conditions["beaconStoreMapId"];
      }
      if ( $conditions["cityId"] && is_numeric($conditions["cityId"])){
          $condition_where.=" and s.city_id=".$conditions["cityId"];
      }
      if ( $conditions["plazaId"] && is_numeric($conditions["plazaId"])){
          $condition_where.=" and s.plaza_id=".$conditions["plazaId"];
      }elseif ($conditions["plazaId"]){
          $condition_where.=" and s.plaza_id like '%".trim( $conditions["plazaId"]) ."%' ";
      }
      if ( $conditions["storeId"] && is_numeric($conditions["storeId"])){
          $condition_where.=" and s.store_id=".$conditions["storeId"];
      }
      if ($conditions["extraName"]){
          $condition_where.=" and s.extra_name like '%".trim( $conditions["extraName"]) ."%' ";
      }
      if ( $conditions["beaconUUID"] ){
          $condition_where.=" and s.beacon_uuid ='".$conditions["beaconUUID"]."' ";
      }
      if ( $conditions["beaconMajor"] ){
          $condition_where.=" and s.beacon_major ='".$conditions["beaconMajor"]."' ";
      }
      if ( $conditions["beaconMinor"] ){
          $condition_where.=" and s.beacon_minor ='".$conditions["beaconMinor"]."' ";
      }
      if ( $conditions["floorId"] ){
          $condition_where.=" and s.floor_id ='".$conditions["floorId"]."' ";
      }
      
      if ($conditions["createTimeStart"]){
          if (!is_numeric($conditions["createTimeStart"]) || strlen($conditions["createTimeStart"])!=10 ){
              return array("error"=>"createTimeStart非法![".$conditions["createTimeStart"]."]");
          }
          $condition_where.=" and a.".self::$table_fields_map_["createTime"] ." >= ".$conditions["createTimeStart"]." ";
      }
      if ($conditions["createTimeEnd"]){
          if (!is_numeric($conditions["createTimeEnd"]) || strlen($conditions["createTimeEnd"])!=10 ){
              return array("error"=>"createTimeEnd非法![".$conditions["createTimeEnd"]."]");
          }
          $condition_where.=" and a.".self::$table_fields_map_["createTime"] ." <= ".$conditions["createTimeEnd"]." ";
      }

      if ($conditions["activityTimeStart"] && $conditions["activityTimeEnd"]){
          if (!is_numeric($conditions["activityTimeStart"]) || strlen($conditions["activityTimeStart"])!=10 ){
              return array("error"=>"activityTimeStart非法![".$conditions["activityTimeStart"]."]");
          }elseif (!is_numeric($conditions["activityTimeEnd"]) || strlen($conditions["activityTimeEnd"])!=10 ){
              return array("error"=>"activityTimeEnd非法![".$conditions["activityTimeEnd"]."]");
          }
          $condition_where.=" and (
                (a.begin_date <= ".$conditions["activityTimeStart"]." and a.end_date >= ".$conditions["activityTimeStart"]." ) 
                or 
                (a.begin_date <= ".$conditions["activityTimeEnd"]." and a.end_date >= ".$conditions["activityTimeEnd"]." )  )  ";
      }
  
      $condition_fields=array();
      foreach (self::$table_fields_map_ as $lable=>$field){
          $condition_fields[] = "a.".$field." ".$lable;
      }
      foreach (self::$beaconStore_fields_map_ as $lable=>$field){
          $condition_fields[] = "s.".$field." ".$lable;
      }
      
//       if (empty($condition_fields)){
//           $condition_fields[]="a.id id";
//       }
  
      $conditions_limit = ' limit ' . $offset . ',' . $limit;
      $conditions_orderby = " order by a.".$orderbyFields." ".$orderbyType." ";
      $total = 0;
      $list = array();
      $condition_join = "  a inner JOIN activity_beacon_store s ON a.beacon_store_map_id=s.id  ";
      //获取总条数
      $count = MysqlClient::QueryCount(self::DB_NAME,self::TABLE_NAME,$condition_join.$condition_where );
      if(!empty($count)) {
          $total = $count;
          $list = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$condition_fields,$condition_join.$condition_where.$conditions_orderby.$conditions_limit);
      }
      return array("datas"=>$list,"count"=>$count);
  }

  public static function batchUpdate(array $recordList){
      $result= array();
      foreach ($recordList as $id=>$row ){
          foreach ($row as $key=>$val){
              if (isset(self::$table_fields_map_[$key])){
                  $row[self::$table_fields_map_[$key]]=$val;
              }
          }
          $result[] = self::update($id,$row);
      }
      return $result;
  }

  public function batchInsert(array $recordList){
      $result= array();
      foreach ($recordList as $row ){
          $result[] = self::insert($row);
      }
      return $result;
  
  }
  

  public function update($id,$temp=array())
  {
      if (!is_numeric($id)){
          return array("error"=>"参数错误或缺失!");
      }
      $where=" where id=$id";
  
      $conditions = array();
      foreach (self::$table_fields_map_ as $lable=>$fieldName ){
          if(isset($temp[$lable]))
          {
              $conditions[$fieldName]=$temp[$lable];
          }
      }
      
      if (!empty($temp["beaconStoreMapId"])){
          $temp_result = Activity_BeaconStoreDao::getDetail($temp["beaconStoreMapId"]);
          if (isset($temp_result["error"]) || !$temp_result ){
              return array("error"=>"参数非法[beaconStoreMapId],未找到相关信息!");
          }
      }

  
      if(empty($conditions)) {
          return array("error"=>"未获取任何修改参数");
      }
  
//       $conditions["update_time"]=strtotime(date("YmdHis"));
      $result = MysqlClient::UpdateFields(self::DB_NAME,self::TABLE_NAME,$conditions,$where);
  
      if($result)
      {
          $affectedRows = MysqlClient::UpdateAffectedRows(self::DB_NAME);
          
          $this->syncUpdateStore($id, $temp["beaconStoreMapId"], $temp["status"]);
          
          return  $affectedRows > 0 ? $id : $affectedRows;
      }
      else
      {
          return false;
      }
  
  }
  

  public function insert(array $temp) {

      if (!empty($temp["id"])){
          return array("error"=>"请求参数非法![禁止参数id]");
      }
      
      $conditions = array();
      foreach (self::$table_fields_map_ as $lable=>$fieldName ){
          if(isset($temp[$lable]))
          {
              $conditions[$fieldName]=$temp[$lable];
          }
      }
  
      foreach (self::$required_fields as $fieldName){
          if (!isset($temp[$fieldName]) || empty($temp[$fieldName])){
              return array("error"=>"请求参数非法或缺失".$fieldName);
          }
      }

      $temp_result = Activity_BeaconStoreDao::getDetail($temp["beaconStoreMapId"]);
      if (isset($temp_result["error"]) || !$temp_result ){
          return array("error"=>"参数非法[beaconStoreMapId],未找到相关信息!");
      }
      
      $condition_where = " where ".self::$table_fields_map_["beaconStoreMapId"] ."=".$temp["beaconStoreMapId"] ." and status!=5  " ;
      $count = MysqlClient::QueryCount(self::DB_NAME,self::TABLE_NAME,$condition_where);
      if ($count > 0 ){
          return array("error"=>"参数非法[beaconStoreMapId],该beacon当前已存在活动,且当前每个beacon只能存在一个有效的活动!");
      }
  
      if(!$conditions)
      {
          return array("error"=>"未获取任何参数");
      }
  
  
      //       if ( $temp["status"] ==2 ){
      //           $conditions["publish_time"]=strtotime(date("YmdHis"));
      //           if (!isset($temp["publish_user"])||empty($temp["publish_user"])){
      //               $conditions["publish_user"]=$conditions["updateUser"];
      //           }
      //       }
      $conditions["create_time"]=strtotime(date("YmdHis"));
  
      $result = MysqlClient::InsertData(self::DB_NAME,
          self::TABLE_NAME,
          array_values(self::$table_fields_map_),
          array($conditions));
      if ($result){
          $result = MysqlClient::GetInsertID(self::DB_NAME);
           
          $this->syncUpdateStore($result, $temp["beaconStoreMapId"], $temp["status"]);
          
          $code = "B" . date("Ymd") .str_pad( ($result%1000),3,"0",STR_PAD_LEFT);
          $result_updateCode = MysqlClient::UpdateFields(self::DB_NAME,self::TABLE_NAME,array("code"=>$code)," where id= $result ");
          
      }
      return $result;
  }
  
  public static function getDetail($id,$fields) {//$condition_fields
  
      if (!is_numeric($id)){
          return array("error"=>"参数错误或缺失!");
      }
      
      $where = "where id=$id ";
  
      $condition_fields=array();
      if (!$fields){
          foreach (self::$table_fields_map_ as $lable=>$field){
              $condition_fields[] = $field." ".$lable;
          }
      }else {
          foreach ($fields as $key=>$value)
          {
              if(self::$table_fields_map_[$value])
              {
                  $condition_fields[] = self::$table_fields_map_[$value]." ".$value;
              }
          }
      }
      if (!in_array("id", $fields)){
          $condition_fields[]="id id";
      }
      
      $result = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$condition_fields,$where);
      if(isset($result[0]) && isset($result[0]["id"]))
      {
//               $result[0]["typeName"] = self::$typeList[$row["type"]]  ;
            return $result[0];
      }
      else
      {
            return false;
      }
  }
  
  

  /*

  public function delete($id,$conditions)
  {
      if (!is_numeric($id)){
          return array("error"=>"参数错误或缺失!");
      }
  
      $where = " where id=$id  ";
  
      $result = MysqlClient::UpdateFields(self::DB_NAME,self::TABLE_NAME,$conditions,$where);
      if($result)
      {
          return  MysqlClient::UpdateAffectedRows(self::DB_NAME);
      }
      else
      {
          return false;
      }
  }
  

*/

  public function syncUpdateStore($id,$beaconStoreMapId,$status)
  {
      $conditions = array("activityId"=>$id);
      if ($status && is_numeric($status)){
          $conditions["activityStatus"]=$status;
      }
      $result=Activity_BeaconStoreDao::update($beaconStoreMapId,$conditions);
      return $result;
  }
  

}
