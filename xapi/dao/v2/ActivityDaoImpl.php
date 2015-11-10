<?php
require_once(PHP_ROOT . 'libs/util/MysqlClient.php');
require_once(PHP_ROOT . 'libs/util/Utility.php');
/**
 * 活动api
* author: zhangyongwei1 
 */

class ActivityDaoImpl{
	const DB_NAME ="ff_cloud_marketing_platform";
	const TABLE_NAME = 'activity_v2_activity';
	const TABLE_NAME_LINK = 'activity_v2_activity_link';

	protected static $table_fields_map_ = array(
	'id' =>'id',
	'title' =>'title',
	'startTime' =>'start_time',
	'endTime' =>'end_time',
	'limitPerDay' =>'limit_per_day',
	'limitTotal' =>'limit_total',
	'webImg' =>'web_img',
	'h5Img' =>'h5_img',
	'h5PromoteLink' =>'h5_promote_link',
	'needCaptcha' =>'need_captcha',
	'insert_time' =>'insert_time',
	'submituid' =>'submituid',
	'submitter' =>'submitter',
	'status' =>'status',
	);

	protected static $activity_link_fields_map_ = array(
	'id' =>'id',
	'activity_id' =>'activity_id',
	'name' =>'name',
	'source' =>'source',
	'insert_time' =>'insert_time',
	);


	/**
	 * 添加活动数据
	 *
	 * @param unknown_type $conditions
	 * @return unknown
	 */
	public function addActivity($conditions)
	{
		$addData = array();
		foreach (self::$table_fields_map_  as $key=>$value)
		{
			if(isset($conditions[$key]))
			{
				$addData[$value]=$conditions[$key];
			}

		}
		if(!$addData)
		{
			return array("error"=>"参数错误或缺失!");
		}
		if(isset($addData['id'])) unset($addData['id']);
		$addData["insert_time"]=strtotime(date("YmdHis"));
		$addData["status"]=1;
		$result = MysqlClient::InsertData(self::DB_NAME, self::TABLE_NAME, array_values(self::$table_fields_map_), array($addData));
		if ($result){
			$result = MysqlClient::GetInsertID(self::DB_NAME);
		}
		return $result;
	}

	/**
	 * 获取活动列表
	 *
	 * @param array $conditions  条件
	 * @param unknown_type $offset
	 * @param unknown_type $limit
	 * @param unknown_type $orderByType
	 * @param unknown_type $orderByFields
	 * @return unknown
	 */
	public function getActivityList($conditions=array(),$offset=0,$limit=10,$orderByType="desc",$orderByFields="id")
	{
		$this->updateAllActivityStatus5();
		$conditions_where="where  (1=1) ";

		if(isset($conditions["status"]) && $conditions["status"])
		{

            $conditions["status"] = intval($conditions["status"]);

			$conditions_where.=" and status=".$conditions["status"];
		}


		if(isset($conditions["title"]) && !empty($conditions["title"]))
		{
            $conditions["title"] = ToolUtil::escapeSQL($conditions["title"]);
			$conditions_where.=" and (title like '%".$conditions["title"]."%')";
		}


		if(isset($conditions["submituid"]) && $conditions["submituid"])
		{
            $conditions["submituid"] = intval($conditions["submituid"]);
			$conditions_where.=" and (submituid=".$conditions["submituid"].")";
		}

		if(isset($conditions["submitter"]) && $conditions["submitter"])
		{
            $conditions["submitter"] = ToolUtil::escapeSQL($conditions["submitter"]);
			$conditions_where.=" and (submitter like '%".$conditions["submitter"]."%')";
		}

		if (isset($conditions["startTime"]) &&  $conditions["startTime"]){
			if (!is_numeric($conditions["startTime"]) || strlen($conditions["startTime"])!=10 ){
				return array("error"=>"startTime非法");
			}
			$conditions_where.=" and start_time >= ".$conditions["startTime"]." ";
		}
		if (isset($conditions["endTime"]) &&  $conditions["endTime"]){
			if (!is_numeric($conditions["endTime"]) || strlen($conditions["endTime"])!=10 ){
				return array("error"=>"endTime非法");
			}
			$conditions_where.=" and start_time <= ".$conditions["endTime"]." ";//查活动开始时间区间
		}

		if(isset($conditions["fieldsList"]))
		{
			$fields_arr=explode(",",$conditions["fieldsList"]);
			foreach ($fields_arr as $key=>$value)
			{
				if(self::$table_fields_map_[$value])
				{
					$conditions_fields[]=self::$table_fields_map_[$value]." ".$value;
				}
			}

		}
		else
		{
			foreach (self::$table_fields_map_ as $key=>$value)
			{
				$conditions_fields[]=$value." ".$key;
			}
		}


        $orderByFields = ToolUtil::escapeSQL($orderByFields);
        $orderByType = ToolUtil::escapeSQL($orderByType);
        $offset = intval($offset);
        $limit = intval($limit);
		$orderByFields=self::$table_fields_map_[$orderByFields];
		$conditions_where_fields=$conditions_where." order by $orderByFields $orderByType limit $offset,$limit";
		$result = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$conditions_fields,$conditions_where_fields);
		if (!empty($result)) {
			foreach ($result as $key=>$value)
			{
				$result[$key] = $this->checkEndTime($value);
			}
		}
		$count  = MysqlClient::QueryCount(self::DB_NAME,self::TABLE_NAME,$conditions_where);
		return array("datas"=>$result,"count"=>$count);
	}


	/**
	 * 更新所有活动结束且status不等于5的活动状态为5
	 * 后期活动增多会舍弃
	 *
	 */
	protected function updateAllActivityStatus5()
	{
		$conditions_fields = array();
		foreach (self::$table_fields_map_ as $key=>$value)
		{
			$conditions_fields[]=$value." ".$key;
		}
		$conditions_where="where  (status!=5) ";
		$result = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$conditions_fields,$conditions_where);
		if (!empty($result)) {
			foreach ($result as $key=>$value)
			{
				$result[$key] = $this->checkEndTime($value);
			}
		}

	}

	/**
	 * 检查是否结束并且修改 已结束的status
	 *
	 * @param unknown_type $info
	 */
	protected function checkEndTime($info)
	{
		$endTime = intval($info['endTime']);
		$status = intval($info['status']);
		$activityid = intval($info['id']);
		if ($endTime<time()&&$status!=5) {
			$info['status'] = 5;
			//echo "update $activityid \n";print_r($info);
			$this->updateActivity($activityid,array("status"=>5));
		}
		return $info;
	}

	/**
	 * 编辑活动详细
	 *
	 * @param unknown_type $id
	 * @param unknown_type $conditions
	 * @return unknown
	 */
	public function updateActivity($id,$conditions)
	{
		if (!is_numeric($id)){
			return array("error"=>"参数错误或缺失!");
		}
		$where=" where id=$id";
		$updateData = array();
		foreach (self::$table_fields_map_   as $key=>$value)
		{
			if(isset($conditions[$key]))
			{
				$updateData[$value]=$conditions[$key];
			}

		}
		if(!$updateData)
		{
			return array("error"=>"参数错误或缺失!");
		}

		$result = MysqlClient::UpdateFields(self::DB_NAME,self::TABLE_NAME,$updateData,$where);
		return $result;
	}

	/**
	 * 获取活动详细
	 *
	 * @param unknown_type $id
	 * @param unknown_type $conditions
	 * @return unknown
	 */
	public function getActivityById($id,$conditions="")
	{
		if (!is_numeric($id)){
			return array("error"=>"参数错误或缺失!");
		}
		$where=" where id=$id";
		if(isset($conditions["fieldsList"]))
		{
			$fields_arr=explode(",",$conditions["fieldsList"]);
			foreach ($fields_arr as $key=>$value)
			{
				if(self::$table_fields_map_[$value])
				{
					$conditions_fields[]=self::$table_fields_map_[$value]." ".$value;
				}
			}

		}
		else
		{
			foreach (self::$table_fields_map_ as $key=>$value)
			{
				$conditions_fields[]=$value." ".$key;
			}
		}

		$result = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$conditions_fields,$where);
		if (isset($result['0']) ) {
			$result = $result['0'];
			$result = $this->checkEndTime($result);
			if (!empty($result) && !isset($conditions['no_link']) ) {
				$where = "where activity_id = $id";
				$field = array("name","source");
				$result['links'] = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME_LINK,$field,$where);
			}
		}
		return $result;

	}

	/**
	 *  添加链接
	 *
	 * @param unknown_type $activity_id
	 * @param unknown_type $name
	 * @param unknown_type $source
	 * @return unknown
	 */
	public function addActivityLink($activity_id,$name,$source)
	{
		$addData = array();
		if(empty($name))
		{
			return array("error"=>"参数错误或缺失!");
		}

        $activity_id = intval($activity_id);
        $name = ToolUtil::escapeSQL($name);
        $source = ToolUtil::escapeSQL($source);
		$where = "where activity_id = $activity_id and source='$source'";
		$field = array("name","source");
		$_res = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME_LINK,$field,$where);
		if($_res)
		{
			return array("ishave"=>"已添加");
		}

		$addData["activity_id"]=$activity_id;
		$addData["name"]=$name;
		$addData["source"]=$source;
		$addData["insert_time"]=strtotime(date("YmdHis"));
		$result = MysqlClient::InsertData(self::DB_NAME, self::TABLE_NAME_LINK, array_values(self::$activity_link_fields_map_), array($addData));
		if ($result){
			$result = MysqlClient::GetInsertID(self::DB_NAME);
		}
		return $result;
	}


}
