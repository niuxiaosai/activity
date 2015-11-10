<?php
/*
* author: zhangyongwei1
* */
require_once(PHP_ROOT . 'libs/util/Utility.php');
require_once(WEB_ROOT . 'models/extra/ErrorMsg.php');
require_once(WEB_ROOT . 'models/extra/Response.php');
require_once(WEB_ROOT . 'models/extra/AbstractSafeModel.php');
require_once(PHP_ROOT . 'libs/util/HttpRequestHelper.php');
require_once (FFAN_ROOT . 'dao/v2/ActivityDao.php');

class ActivityModel extends AbstractSafeModel {
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

		$method_request = strtoupper($_SERVER['REQUEST_METHOD']);
		$path_info = trim($_SERVER["PATH_INFO"], "/");
		$path_info_array = explode("/", $path_info);
		$method_name = strtolower($path_info_array['1']);
		if (count($path_info_array)==2 && $method_name == "activitylist" && $method_request == "GET") {

			return $this->getActivityList();
		}elseif (count($path_info_array)==3 && $path_info_array['2']=='add' && $method_name == "activity" && $method_request == "POST"){

			return $this->addActivity();
		}elseif (count($path_info_array)==4 && $path_info_array['3']=='addlink' && $method_name == "activity" && $method_request == "POST"){
			$id= intval($path_info_array['2']);
			return $this->addActivityLink($id);
		}elseif (count($path_info_array)==4 && $path_info_array['3']=='update' && $method_name == "activity" && $method_request == "POST"){

			$id= intval($path_info_array['2']);
			return $this->updateActivity($id);
		}elseif (count($path_info_array)==4 && $path_info_array['3']=='detail' && $method_name == "activity" && $method_request == "GET"){

			$id= $path_info_array['2'];
			return $this->activityDetail($id);
		}
		else {
			return $this->responseData();
		}
	}

	/**
	 * 返回response数据
	 *
	 * @param unknown_type $status
	 * @param unknown_type $msg
	 * @param unknown_type $data
	 * @return  subject
	 */
	protected function responseData($status="500",$msg="请求方式或请求参数不正确",$data=array())
	{
		$response = new Response();
		$response->status = $status;
		$response->msg = $msg;
		$response->data = $data;
		return $response;
	}


	protected function getActivityList()
	{
		$result = array();

		$limit = HttpRequestHelper::GetParam("limit")?HttpRequestHelper::GetParam("limit"):"10";
		$offset = HttpRequestHelper::GetParam("offset")?HttpRequestHelper::GetParam("offset"):"0";
		$conditions=$_GET;
		$orderByType=HttpRequestHelper::GetParam("orderByType")?HttpRequestHelper::GetParam("orderByType"):"desc";
		$orderByFields=HttpRequestHelper::GetParam("orderByFields")?HttpRequestHelper::GetParam("orderByFields"):"id";
		if (isset($conditions['fieldsList'])) {
			$fieldsList=explode(",",$conditions["fieldsList"]);
		}else{
			$fieldsList ="";
		}

		$result=ActivityDao::getActivityList($conditions,$offset,$limit,$orderByType,$orderByFields);
		if ($result==false ) {
			return $this->responseData();
		}elseif (isset($result['error'])){
			return $this->responseData("500",$result['error'],$result);
		} 
		if (!empty($result['datas'])) {
			foreach ($result['datas'] as $k=>$val)
			{ 
				$result['datas'][$k]["urlcode"] = $this->jiami($val['id']);
			}
		}
		return $this->responseData("200","获取活动列表请求返回正常",$result);
	}

	/**
	 * 添加活动
	 *
	 * @return unknown
	 */
	protected function addActivity()
	{
		$result = array();
		$conditions = $_POST;
		$result=ActivityDao::addActivity($conditions);
		if ($result==false ) {
			return $this->responseData();
		}elseif (isset($result['error'])){
			return $this->responseData("500",$result['error'],$result);
		}
		return $this->responseData("200","添加活动请求返回正常",$result);
	}

	/**
	 * 添加活动链接
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function addActivityLink($id)
	{
		$result = array();
		if (isset($_POST['name'])) {
			$name = $_POST['name'];
			$source = $this->toCode($name,2);
		}else{
			return $this->responseData();
		}
		
		$result=ActivityDao::addActivityLink($id,$name,$source); 
		if ($result==false ) {
			return $this->responseData();
		}elseif (isset($result['error'])){
			return $this->responseData("500",$result['error'],$result);
		}elseif (isset($result['ishave'])){
			$res = array();
			$res['urlCode'] = $this->jiami($id);
			$res['name'] = $name; 
			$res['shotCode'] = $this->toCode($name,2); 
			return $this->responseData("200",$result['ishave'],$res);
		} 
		$res = array();
		$res['urlCode'] = $this->jiami($id);
		$res['shotCode'] = $this->toCode($name,2); 
		$res['id'] = $result;
		return $this->responseData("200","添加活动链接请求返回正常",$res);
	}

	/**
	 * 更新活动信息
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	protected function updateActivity($id)
	{
		$result = array();
		$conditions = $_POST;
		$result=ActivityDao::updateActivity($id,$conditions);
		if ($result==false ) {
			return $this->responseData();
		}elseif (isset($result['error'])){
			return $this->responseData("500",$result['error'],$result);
		}
		return $this->responseData("200","编辑活动请求返回正常",$result);
	}


	/**
	 * 获取活动详细
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	protected function activityDetail($id)
	{
		$result = array();
		if (empty($id)) {
			return $this->responseData();
		}
		if (!is_numeric($id)) {
			$id = $this->jiemi($id);
		} 
		$conditions = $_GET;
		$result=ActivityDao::getActivityById($id,$conditions);
		if ($result==false ) {
			return $this->responseData("500","获取数据为空");
		}elseif (isset($result['error'])){
			return $this->responseData("500",$result['error'],$result);
		}
		if (isset($result['id'])) {
			$result['urlCode'] = $this->jiami($result['id']);
		} 
		return $this->responseData("200","获取活动详细请求返回正常",$result);
	}

	/**
	 * 生成短链接
	 *
	 * @param unknown_type $idcode
	 * @param unknown_type $len
	 * @return unknown
	 */
	private function toCode( $idcode ,$len="7")
	{
		$key = 'activity_link';
		$base32 = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$hex = hash('md5', $idcode.$key);
		$hexLen = strlen($hex);
		$subHexLen = $hexLen / 8;
		$output = array();
		for( $i = 0; $i < $subHexLen; $i++ )
		{
			$subHex = substr($hex, $i*8, 8);
			$idx = 0x3FFFFFFF & (1 * ('0x' . $subHex));
			$out = '';
			for( $j = 0; $j < 6; $j++ )
			{
				$val = 0x0000003D & $idx;
				$out .= $base32[$val];
				$idx = $idx >> 5;
			}
			$output[$i] = $out;
		}
		return substr($output['0'],0,$len);
	}

	/**
	 * 加密函数
	 *
	 * @param unknown_type $str
	 * @param unknown_type $key
	 * @return unknown
	 */
	private function jiami($str,$key="activityid"){
		$key=md5($key);
		$k=md5("activity");//相当于动态密钥
		$k=substr($k,0,3);
		$tmp="";
		for($i=0;$i<strlen($str);$i++){
			$tmp.=substr($str,$i,1) ^ substr($key,$i,1);
		}
		return base64_encode($k.$tmp);
	}

	/**
	 * 解密函数
	 *
	 * @param unknown_type $str
	 * @param unknown_type $key
	 * @return unknown
	 */
	private function jiemi($str,$key="activityid"){
		$len=strlen($str);
		$key=md5($key);
		$str=base64_decode($str);
		$str=substr($str,3,$len-3);
		$tmp="";
		for($i=0;$i<strlen($str);$i++){
			$tmp.=substr($str,$i,1) ^ substr($key,$i,1);
		}
		return $tmp;
	}

}
