<?php
require_once(PHP_ROOT . '/libs/util/HttpHandlerCurl.php');
require_once(PHP_ROOT . '/libs/util/Log.php');
//获取api数据
class GatewayHelper {

	const URL_ROOT = GATEWAY_URL_ROOT;
	const API_KEY = GATEWAY_KEY;
	const API_SECRET = GATEWAY_SECRET;

	protected $curl;

	public function __construct() {
		$this->curl = new HttpHandlerCurl();
	}

	public function get($url, $params = array()) {
		$url = $this->getSecurityUrl($url, 'GET');
		Log::Debug('gateway get url: ' . $url);
		$str = $this->curl->get($url);
		if (!$str) {
			Log::Debug('gateway url return null.');
			if (ENV !== 'pro') {
				echo '[gateway return null! url: '.$url.']';
			}
		}
		$arr = json_decode($str, true);
		return $arr;
	}
	public function post($url, $params = array()) {
		$url = $this->getSecurityUrl($url, 'POST');
		Log::Debug('gateway post url: ' . $url . '; params: ' . print_r($params, true));
		$str = $this->curl->post($url, $params);
		if (!$str) {
			Log::Debug('gateway url return null.');
			if (ENV !== 'pro') {
				echo '[gateway return null! url: '.$url.']';
			}
		}
		$arr = json_decode($str, true);
		return $arr;
	}
	/**
   * 获取return中的data字段
   */
	public function retData($arr, $succCode = 200, $retMsg = false) {
		if (is_array($arr)){
			if ($arr['status'] == $succCode) {
				// 返回data字段
				return isset($arr['data']) ? $arr['data'] : true;
			} else {
				Log::Debug('gateway url return: ' . print_r($arr, true));
				// 返回错误消息
				if ($retMsg) {
					return isset($arr['message']) ? $arr['message'] :
					(isset($arr['msg']) ? $arr['msg'] : '');
				}
			}
		}
	}
	private function getSecurityUrl($url, $method) {
		$app_key = self::API_KEY;
		$app_secret = self::API_SECRET;
		$ts = time();

		$params = array(
		'app_key='.$app_key,
		'app_secret='.$app_secret,
		'method='.$method,
		'ts='.$ts);
		sort($params);
		$sign = md5(join('&', $params));

		$params2 = array(
		'app_key='.$app_key,
		'sign='.$sign,
		'method='.$method,
		'ts='.$ts);

		$url .= (strpos($url, '?') === false ? '?' : '&') . join('&', $params2);
		return $url;
	}
	

	/**
  	 * 获取旅游路线列表根据条件
 	  */
	public function getCouponListByPlaza($plazaId,$condition,$page=1,$limit=10) {

        $condition['plazaId'] = $plazaId;
		$arr = $this->getCouponList($condition,$page,$limit);
		return $this->retData($arr);
	}

    /**
     * 获取旅游路线列表根据条件
     */
    public function getCouponList($condition,$page=1,$limit=10) {
        //限制是活动营销券
        $plaza = '?source=4';
        if ($page>1) {
            $offset = ($page-1)*$limit;
            $offset = "&offset=$offset";
            $offset .= "&limit=$limit";
        }else{
            $offset = "&limit=$limit";
        }
        $param = '';

        //广场筛选这个，就是这个接口，当source=4时，把 plazaId传对应的广场id就行
        if(isset($condition['plazaId'])){
            $param.='&plazaId='.$condition['plazaId'];
        }
        if(isset($condition['merchantNo'])){
            $param.='&merchantNo='.$condition['merchantNo'];
        }
        //商品编号
        if(isset($condition['productNo'])){
            $param.='&productNo='.$condition['productNo'];
        }
        //商品编号列表
        if(isset($condition['productNos'])){
            $param.='&productNos='.json_encode($condition['productNos']);
        }
        //商户名称
        if(isset($condition['productName'])){
            $param.='&productName='.$condition['productName'];
        }
        //商品状态
        if(isset($condition['saleStatus'])){
            $param.='&saleStatus='.$condition['saleStatus'];
        }
        //类型
        if(isset($condition['productType'])){
            $param.='&productType='.$condition['productType'];
        }
        //有效期
        if(isset($condition['validStartTime'])){
            $param.='&validStartTime='.$condition['validStartTime'];
        }
        if(isset($condition['validEntTime'])){
            $param.='&validEntTime='.$condition['validEntTime'];
        }
        $url = self::URL_ROOT . 'coupon/v2/products'. $plaza . $param . $offset;
        $arr = $this->get($url);
        return $this->retData($arr);
    }

}