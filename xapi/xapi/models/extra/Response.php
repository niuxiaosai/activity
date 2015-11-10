<?php

/**
 * Model 中的响应数据
 */
class Response {
  const SUCCESS = 0;

  public $status = self::SUCCESS;
  public $msg = '';
  //public $data = array();
}


class ResponseBase {
  public function Assign($items) {
    if (!$items) return;
    foreach ($items as $property => $value) {
      if (null !== $value && property_exists($this, $property))
        $this->$property = $value;
    }
  }
}

class StoreResponse extends ResponseBase {
  public function __construct() {
    $this->picsrc = new StdClass();
  }

  public function Assign($items) {
    parent::Assign($items);
    if (isset($items->store_number))
      $this->storenumber = $items->store_number;
    if (isset($items->picsrc) && $items->picsrc)
      $this->picsrc = $items->picsrc;
    else
      $this->picsrc = new stdclass();
  }
  public $sid = 0;
  public $bid = 0;
  public $brands = array();
  public $wpid = 0;
  public $name = '';
  public $quanpin = '';
  public $address = '该商户暂无地址';
  public $storenumber = ''; //商铺短地址,对应thrift铺位号
  public $tel = '该商户暂无电话';
  public $picsrc = array(); // 结构体
  public $status = 0;
  public $averagecost = 0;
  public $scid = 0;
  public $bcid = 0;
  public $bcname = '';
  public $x = '0.0';
  public $y = '0.0';
  public $floor = 0;
  public $hasdeal = 0;
  public $hascoupon = 0;
  public $hasactivity = 0;
  public $hasproduct = 0;
  public $acceptpoint = 0;
}

class StoreFoodResponse extends ResponseBase {
  public function Assign($items) {
    parent::Assign($items);
    if (isset($items->supporttakeout))
      $this->supporttakeout = (int)$items->supporttakeout;
    if (isset($items->supportreservation))
      $this->supportreservation = (int)$items->supportreservation;
  }
  public $sid = 0;
  public $supporttakeout = 0;     // 是否支持外卖
  public $supportreservation = 0; // 是否支持预定
  public $blackboard = array();   // 0~3张图片
}

class DishResponse extends ResponseBase {
  public $dsid = 0;
  public $sid = 0;
  public $name = '';
  public $shortname = '';
  public $category = 0;
  public $price = 0;
  public $discountprice = 0;
  public $picsrc = array(); // 结构体
  public $description = '';
}

/**
 * @status thrift状态参见 http://dianshang.wanda.cn/svn/dssvn/trunk/thrift/WanhuiCommon.thrift
 */
class ProductResponse extends ResponseBase {
  public function Assign($items) {
    parent::Assign($items);
    if (isset($items->picsrc) && $items->picsrc)
      $this->picsrc = $items->picsrc[0];
    else
      $this->picsrc = new StdClass();
    if (isset($items->name))
      $this->name = $items->name;
    if (isset($items->shorttitle))
      $this->shortname = $items->shorttitle;
    if (isset($items->relatedbrand))
      $this->relatedbrand = $items->relatedbrand;
    if (isset($items->status) && (GoodsStatus::ON_SHELVE == $items->status || GoodsStatus::OFF_SHELVE_CHECK == $items->status))
      $this->status = 0;
    else
      $this->status = 1;
  }
  public $pid = 0;
  public $bid = 0;
  public $relatedbrand = '';
  public $name = '';
  public $shortname = '';
  public $price = 0;
  public $discountprice = 0;
  public $picsrc = array();
  public $category = 0;
  public $isnew = 0;
  public $ispromotion = 0;
  public $ishot = 0;
  public $status = 0;
}

/**
 * 不展示storeids, description
 * 活动列表只展示头图
 * 格式化picsrc
 * @status thrift状态参见 http://dianshang.wanda.cn/svn/dssvn/trunk/thrift/YazuoCommon.thrift
 */
class ActivityResponse extends ResponseBase {
  public function __construct() {
    $this->picsrc = new StdClass();
  }

  public function Assign($items) {
    parent::Assign($items);
    if (isset($items->aid))
      $this->aiid = $items->aid;
    if (isset($items->picsrc) && $items->picsrc)
      $this->picsrc = $items->picsrc[0];
    else
      $this->picsrc = new StdClass();
    if (isset($items->relatedbrand))
      $this->relatedbrand = $items->relatedbrand;
    if (isset($items->name))
      $this->name = $items->name;
    if (isset($items->shortname))
      $this->shortname = $items->shortname;
    if (isset($items->summary))
      $this->summary = $items->summary;
    if (isset($items->status) && (ActivityStatus::ON_SHELVE == $items->status || ActivityStatus::OFF_SHELVE_CHECK == $items->status))
      $this->status = 0;
    else
      $this->status = 1;
  }
  public $aiid = 0;
  public $bid = 0;
  public $relatedbrand = '';
  public $name = '';
  public $shortname = '';
  public $summary = '';
  public $picsrc = '';
  public $starttime = 0;
  public $endtime = 0;
  public $category = 0;
  public $status = 0;
}


/**
 * 用户基本信息结构
 * @package   main
 * @subpackage  classes
 * @abstract  Classes defined as abstract may not be instantiated
 */
class UserResponse extends ResponseBase{
  public $uid = 0;
  public $memberid = 0;
  public $nick = '';
  public $sex = 0;
  public $birthday = '1901-01-01';
  public $mobile = '';
}


/**
 * 用户扩展信息结构
 * @package   main
 * @subpackage  classes
 * @abstract  Classes defined as abstract may not be instantiated
 */
class UserExtendResponse extends ResponseBase {
  public $uid = 0;
  public $point = 0;
  public $grade = 0;
  public $gradename = '绿卡会员';
  public $realname = '';
  public $idcardno = '';
  public $token = '';
}


/**
 * 消息中心消息结构体
 * @package   main
 * @subpackage  classes
 * @abstract  Classes defined as abstract may not be instantiated
 */
class MessageResponse extends ResponseBase {
  public $mid = 0;
  public $senderpic = array();
  public $title = '';
  public $redirectschema = '';
  public $content = '';
  public $picsrc = array();
  public $isread = 0;
  public $createtime = 0;
}


/**
 * Short description.
 * @package   main
 * @subpackage  classes
 * @abstract  Classes defined as abstract may not be instantiated
 */
class UserPointResponse extends ResponseBase {
  public $uid = 0;
  public $id = 0;
  public $createtime = 0;
  public $address = '';
  public $storename = '';
  public $plazaname = '';
  public $type = 1;
  public $status = 1;
  public $point = 0;
}


/**
 * Short description.
 * @package   main
 * @subpackage  classes
 * @abstract  Classes defined as abstract may not be instantiated
 */
class MemberCardResponse extends ResponseBase {
  public $cardnumber = '';
  public $cardname = '';
  public $description = '';
  public $displaystatus = '';
  public $status = 0;
  public $picsrc = array('name' => 'h004fe62c2af46b444516f121e065ea83c7','scale' => 0);
  public $cardgrade = 0;
  public $cardgradename = '';
  public $isprimary = 1;
}

/**
 * 城市基本结构
 */
class CityResponse extends ResponseBase{
  public $cid = 0;
  public $name = '';
  public $cityletter = '';
  public $area = '';
  public $specialstring = ''; // 特殊标识，可存任何数据，大歌星用于存放城市id
}

/**
 * KTV门店结构体
 * @description: 部分字段数据结构体返回不稳定，判断是否为Array
 *               如果返回array，取0索引的值
 */
class KTVResponse extends ResponseBase {
  public function __construct() {
    $this->picsrc = new StdClass();
  }

  public function Assign($items) {
    if (isset($items['CompanyID']))
      $this->companyid = (int)$items['CompanyID'];
    if (isset($items['PlazaId']))
      $this->wpid = (int)$items['PlazaId'];
    if ($items['StoreId']) //StoreId结构体返回不稳定
      $this->storeid = is_array($items['StoreId']) ? (int)current($items['StoreId'])
                                                   : (int)$items['StoreId'];

    if (isset($items['CompanyName']))
      $this->companyname = $items['CompanyName'];
    if (isset($items['CompanyCode']))
      $this->companycode = $items['CompanyCode'];
    if (isset($items['CompanyFullName']))
      $this->companyfullname = $items['CompanyFullName'];
    if (isset($items['picsrc']) && $items['picsrc'])
      $this->picsrc = $items['picsrc'];

    if (isset($items['CityID']))
      $this->cityid = $items['CityID'];
    if (isset($items['CityName']))
      $this->cityname = $items['CityName'];
    if (isset($items['CompanyAdd']))
      $this->companyadd = $items['CompanyAdd'];
    if (isset($items['RoomCount']))
      $this->roomcount = (int)$items['RoomCount'];
    if (isset($items['CompanyPhone']))
      $this->companyphone = $items['CompanyPhone'];
    if (isset($items['MapAddr']))
      $this->mapaddr = is_array($items['MapAddr']) ? strval(current($items['MapAddr']))
                                                   : $items['MapAddr'];
    if (isset($items['Longitude']))
      $this->longitude = (float)$items['Longitude'];
    if (isset($items['Latitude']))
      $this->latitude = (float)$items['Latitude'];
    if (isset($items['AlipayAccount']))
      $this->alipayaccount = is_array($items['AlipayAccount']) ? strval(current($items['AlipayAccount']))
                                                               : $items['AlipayAccount'];
    if (isset($items['AlipayPartnerId']))
      $this->alipaypartnerid = is_array($items['AlipayPartnerId']) ? strval(current($items['AlipayPartnerId']))
                                                                  : $items['AlipayPartnerId'];
    if (isset($items['AlipaySecurityCode']))
      $this->alipaysecuritycode = is_array($items['AlipaySecurityCode']) ? strval(current($items['AlipaySecurityCode']))
                                                                         : $items['AlipaySecurityCode'];
  }
  public $companyid = 0;
  public $wpid = 0;
  public $storeid = 0;
  public $companyname = '';
  public $companycode = 0;
  public $companyfullname = '';
  public $picsrc;
  public $cityid = '';
  public $cityname = '';
  public $companyadd = '';
  public $roomcount = 0;
  public $companyphone = '';
  public $mapaddr = '';
  public $longitude = '';
  public $latitude = '';
  public $alipayaccount = '';
  public $alipaypartnerid = 0;
  public $alipaysecuritycode = '';
}

// 包房预定结构体
class KTVBookingResponse extends ResponseBase {
  public $bookingid = ''; //预定ID，预定表的主键
  public $bookingno = ''; //预定号，到店凭证
  public $companyid = 0;
  public $companyname = '';
  public $roomtype = 0;
  public $roomtypename = '';
  public $price = 0;
  public $scheduletime = 0;
  public $state = 0;
}
