<?php
require_once(PHP_ROOT . 'libs/util/MysqlClient.php');
require_once(PHP_ROOT . 'libs/util/Utility.php');
 

class AdminLogDaoImpl{
    const DB_NAME ="ff_cloud_marketing_platform";
    const TABLE_NAME = 'activity_v2_admin_log';

  protected static $table_fields_map_ = array(
      'id' =>'id',
      'activityId' =>'activity_id',
      'submitter' =>'submitter',
      'uid' =>'uid',
      'time' =>'time',
      'action' =>'action',
   );

    static public function getAdminLogList($conditions,$offset,$limit,$orderByType,$orderByFields){

        $fields = array();
        foreach (self::$table_fields_map_ as $label=>$field){
            $fields[] = $field." ".$label;
        }
        $activityId = ToolUtil::escapeSQL($conditions['activityId']);
        $where = "where activity_id='$activityId'";
        $orderByFields = ToolUtil::escapeSQL($orderByFields);
        $orderByType = ToolUtil::escapeSQL($orderByType);
        $offset = intval($offset);
        $limit = intval($limit);
        $where .= " order by $orderByFields $orderByType limit $offset,$limit";
        $result = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$fields,$where);

        if($result)
        {
            return $result;
        }
        else
        {
            return false;
        }
    }

    static public function addAdminLog(array $temp){

//        foreach (self::$required_fields as $fieldName){
//            if (!isset($temp[$fieldName]) || empty($temp[$fieldName])){
//                return array("error"=>"请求参数非法或缺失".$fieldName);
//            }
//        }

        $conditions = array();
        foreach (self::$table_fields_map_ as $label=>$fieldName ){
            if(isset($temp[$label]))
            {
//                $conditions[$fieldName]=$temp[$label];
                $conditions[$fieldName] = ToolUtil::escapeSQL($temp[$label]);
            }

        }

        if(!$conditions)
        {
            return array("error"=>"未获取任何参数");
        }

        $conditions["time"]=time();

        $result = MysqlClient::InsertData(self::DB_NAME,
            self::TABLE_NAME,
            array_values(self::$table_fields_map_),
            array($conditions));
        if ($result){
            $result = MysqlClient::GetInsertID(self::DB_NAME);
        }
        return $result;
    }
}
