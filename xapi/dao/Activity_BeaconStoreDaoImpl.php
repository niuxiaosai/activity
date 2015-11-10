<?php
require_once(PHP_ROOT . 'libs/util/MysqlClient.php');
require_once(PHP_ROOT . 'libs/util/Utility.php');
 

class Activity_BeaconStoreDaoImpl{
  const DB_NAME = 'ff_cloud_marketing_platform';
  const TABLE_NAME="activity_beacon_store";
//   const TABLE_RENTAL="rental";
  

//   protected static $table_fields_ = array("id","title","description","url","type",
//       "related_id","is_first","sort","status","update_time","update_user",);
  protected static $table_fields_map_ = array(
        "id"=>"id",
        "cityId"=>"city_id",
        "plazaId"=>"plaza_id",
        "storeId"=>"store_id",
        "extraName"=>"extra_name",
        "beaconUUID"=>"beacon_uuid",
        "beaconMajor"=>"beacon_major",
        "beaconMinor"=>"beacon_minor",
        "floorId"=>"floor_id",
        "commonAreaImg"=>"common_area_img",

      "activityId"=>"activity_id",
      "activityStatus"=>"activity_status",
      
        "createAdminId"=>"create_admin_id",
        "createAdminName"=>"create_admin_name",
        "createTime"=>"create_time",
  );

  protected static $required_fields=array(
        "beaconUUID",
        "beaconMajor",
        "beaconMinor",
        "createAdminId",
        "createAdminName",
        "floorId",
        "storeId"
      
  );

  public static $orderByType_enum =array("desc","asc","");
  
  const ORDERBYFIELDS_DEFAULT = "createTime";//默认排序列
  const ORDERBYTYPE_DEFAULT = "desc";//默认排序方向
  
  public static function getListByBeaconList($fields,$conditions){
      
      $beaconList = array();

      $condition_where = " where 1=1 ";
      

      if ( $conditions["plazaId"] && is_numeric($conditions["plazaId"])){
          $condition_where.=" and ".self::$table_fields_map_["plazaId"] ."=".$conditions["plazaId"];
      }elseif ($conditions["plazaId"]){
          $condition_where.=" and ".self::$table_fields_map_["plazaId"] ." like '%".trim( $conditions["plazaId"]) ."%' ";
      }
      
      if ($conditions["beaconUUID"]&&$conditions["beaconMajor"]&&$conditions["beaconMinor"]
          && count($conditions["beaconUUID"]) == count($conditions["beaconMajor"])
          && count($conditions["beaconUUID"]) == count($conditions["beaconMinor"]) ){
          $temp_arr = array();
          foreach ($conditions["beaconUUID"] as $key => $val  ){
             $beaconList[$conditions["beaconUUID"][$key]]
                        [$conditions["beaconMajor"][$key]]
                        [$conditions["beaconMinor"][$key]]= $key;
             $temp_arr[] = " (".self::$table_fields_map_["beaconUUID"] ." = '".trim( $conditions["beaconUUID"][$key]) ."' 
                            and  ".self::$table_fields_map_["beaconMajor"] ." = '".trim( $conditions["beaconMajor"][$key]) ."' 
                            and  ".self::$table_fields_map_["beaconMinor"] ." = '".trim( $conditions["beaconMinor"][$key]) ."') ";
          }
          $condition_where .= " and  (" . implode(" or ", $temp_arr) . ") ";
      }else {
         return array("error"=>"参数异常!");
      }

      
      $condition_fields=array();
      if (!isset($fields)){
          foreach (self::$table_fields_map_ as $lable=>$field){
              $condition_fields[] = $field." ".$lable;
          }
      }else {

          if (!in_array("beaconUUID", $fields)){
              $fields[] = "beaconUUID";
          }
          if (!in_array("beaconMajor", $fields)){
              $fields[] = "beaconMajor";
          }
          if (!in_array("beaconMinor", $fields)){
              $fields[] = "beaconMinor";
          }
          if (!in_array("activityId", $fields)){
              $fields[] = "activityId";
          }
          if (!in_array("activityStatus", $fields)){
              $fields[] = "activityStatus";
          }
          if (!in_array("id", $fields)){
              $fields[] = "id";
          }
          foreach ($fields as $key=>$value)
          {
              if(self::$table_fields_map_[$value])
              {
                  $condition_fields[] = self::$table_fields_map_[$value]." ".$value;
              }
          }
      }
//       if ($conditions["distance"]){
//           $condition_where.=" and ".self::$table_fields_map_["beaconMinor"] ." = '".trim( $conditions["beaconMinor"]) ."' ";
//       }
      

      $list = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$condition_fields,$condition_where);
      
      $result = array();
      foreach ($list as $row){
          $key =   $beaconList[$row["beaconUUID"]][$row["beaconMajor"]][$row["beaconMinor"]];
          $result[$key] = $row;
      }
      ksort($result);
      
      return  $result ;
      
  }
  
  
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
      }
      if ( $conditions["cityId"] && is_numeric($conditions["cityId"])){
          $condition_where.=" and ".self::$table_fields_map_["cityId"] ."=".$conditions["cityId"];
      }
      if ( $conditions["plazaId"] && is_numeric($conditions["plazaId"])){
          $condition_where.=" and ".self::$table_fields_map_["plazaId"] ."=".$conditions["plazaId"];
      }elseif ($conditions["plazaId"]){
          $condition_where.=" and ".self::$table_fields_map_["plazaId"] ." like '%".trim( $conditions["plazaId"]) ."%' ";
      }
      if ( $conditions["storeId"] && is_numeric($conditions["storeId"])){
          $condition_where.=" and ".self::$table_fields_map_["storeId"] ."=".$conditions["storeId"];
      }
      if ($conditions["extraName"]){
          $condition_where.=" and ".self::$table_fields_map_["extraName"] ." like '%".trim( $conditions["extraName"]) ."%' ";
      }
      if ($conditions["beaconUUID"]){
          $condition_where.=" and ".self::$table_fields_map_["beaconUUID"] ." = '".trim( $conditions["beaconUUID"]) ."' ";
      }
      if ($conditions["beaconMajor"]){
          $condition_where.=" and ".self::$table_fields_map_["beaconMajor"] ." = '".trim( $conditions["beaconMajor"]) ."' ";
      }
      if ($conditions["beaconMinor"]){
          $condition_where.=" and ".self::$table_fields_map_["beaconMinor"] ." = '".trim( $conditions["beaconMinor"]) ."' ";
      }
      if ($conditions["floorId"]){
          $condition_where.=" and ".self::$table_fields_map_["floorId"] ." = '".trim( $conditions["floorId"]) ."' ";
      }
      if ( $conditions["createAdminId"] && is_numeric($conditions["createAdminId"])){
          $condition_where.=" and ".self::$table_fields_map_["createAdminId"] ."=".$conditions["createAdminId"];
      }
      if ($conditions["createAdminName"]){
          $condition_where.=" and ".self::$table_fields_map_["createAdminName"] ." = '".trim( $conditions["createAdminName"]) ."' ";
      }

      if ( $conditions["activityId"] && is_numeric($conditions["activityId"])){
          $condition_where.=" and ".self::$table_fields_map_["activityId"] ."=".$conditions["activityId"];
      }
      if ( isset($conditions["activityStatus"]) && is_numeric($conditions["activityStatus"])){
          $condition_where.=" and ".self::$table_fields_map_["activityStatus"] ."=".$conditions["activityStatus"];
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
      
      /*
      if ( $conditions["activityStatus"] && is_numeric($conditions["activityStatus"])){
          if ($conditions["activityStatus"] > 0 ){
              $condition_where.=" AND id in (
                  SELECT beacon_store_map_id FROM activity_beacon_activity 
                  WHERE status = ".$conditions["activityStatus"].") ";
//               $condition_where.=" AND id in (
//                       SELECT beacon_store_map_id FROM (
//                           SELECT beacon_store_map_id,status FROM activity_beacon_activity 
//                           ORDER BY beacon_store_map_id ,status
//                       ) a WHERE status = ".$conditions["activityStatus"]."
//                       GROUP BY beacon_store_map_id " .") ";
          }else {
              $condition_where.=" AND id not in (SELECT beacon_store_map_id FROM activity_beacon_activity) ";
          }
      }*/
      
      $condition_fields=array();
      if (!isset($fields)){
          foreach (self::$table_fields_map_ as $lable=>$field){
              $condition_fields[] = $field." ".$lable;
          }
      }else {
          if (!in_array("id", $fields)){
              $fields[]="id";
          }
//           if (!in_array("activityId", $fields)){
//               $fields[]="activityId";
//           }
          foreach ($fields as $key=>$value)
          {
              if(self::$table_fields_map_[$value])
              {
                  $condition_fields[] = self::$table_fields_map_[$value]." ".$value;
              }
          }
      }
//       if (empty($condition_fields)){
//           $condition_fields[]="id id";
//       }
  
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
      if ($id && is_numeric($id)){
          $where=" where id=$id";
      }elseif ($temp["activityId"] && is_numeric($temp["activityId"])){
          $where=" where ".self::$table_fields_map_["activityId"] ."=".$temp["activityId"] ;
      }else {
          return array("error"=>"参数错误或缺失!");
      }
  
      $conditions = array();
      foreach (self::$table_fields_map_ as $lable=>$fieldName ){
          if(isset($temp[$lable]))
          {
              $conditions[$fieldName]=$temp[$lable];
          }
      }
  
      if(empty($conditions)) {
          return array("error"=>"未获取任何修改参数");
      }
  
//    $conditions["update_time"]=strtotime(date("YmdHis"));
      $result = MysqlClient::UpdateFields(self::DB_NAME,self::TABLE_NAME,$conditions,$where);
  
      if($result)
      {
          $affectedRows = MysqlClient::UpdateAffectedRows(self::DB_NAME);
          return  $affectedRows > 0 ? $id : $affectedRows;
      }
      else
      {
          return false;
      }
  
  }
  

  public function insert(array $temp) {
  

      if ( $temp["storeId"]==-1 && empty($temp["extraName"])  ){
          return array("error"=>"门店id[storeId]为-1时,必须extraName不能为空!");
      }
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

  

}
