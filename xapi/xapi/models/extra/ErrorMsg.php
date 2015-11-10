<?php
/**
 * 错误信息.
 */
require_once(PHP_ROOT . 'libs/util/Log.php');

class ErrorMsg {
  // 通用错误信息
  const ERROR_MESSAGE = 1000;
  const INTERFACE_VERSION_NOT_EMPTY = 1001;
  const START_ERROR = 1002;
  const MAX_NUM_ERROR = 1003;
  const REQUEST_PARAM_ERROR = 1004;
  const UNKNOWN_ERROR = 1005;
  const SORRY_MESSAGE = 1006;
  const SPECIFIC_ERROR = 1007;
  const USER_AUTH_FAILED = 12001;
  const FORBIDDEN_WORD_MATCH = 1101; // 敏感关键词


  // 用户相关错误信息
  const USER_EXIST = 2001;
  const USER_PASSWD_EMPTY = 2002;
  const USER_NICK_EXIST = 2003;
  const USER_NICK_EMPTY = 2004;
  const USER_NICK_ERROR = 2005;
  const USER_PICSRC_EMPTY = 2007;
  const USER_OAUTH_UID_EXIST = 2008;
  const USER_OAUTH_EMPTY = 2009;
  const USER_OAUTH_SITE_NOT_SUPPORT = 2010;
  const USER_OAUTH_FAILED = 2011;
  const USER_OAUTH_VERIFICATE_FAILED = 2012;
  const USER_REG_FAILED = 2013;
  const USER_LOGIN_EMPTY = 2014;
  const USER_LOGIN_FAILED = 2015;
  const USER_NAME_EMPTY = 2016;
  const USER_UID_EMPTY = 2017;
  const USER_UID_NOINFO = 2018;
  const USER_ID_OR_NICK_EMPTY = 2019;
  const USER_UPDATE_FAILED = 2020;
  const USER_PUSHTOKEN_EMPTY = 2021;
  const USER_CITY_ERROR = 2022;
  const USER_POSITION_EMPTY = 2023;

  const USER_NAME_MOBILE_ERROR = 2024;
  const USER_NAME_MOBILE_AUTHCODE_ERROR = 2025;
  const USER_NAME_FAILED = 2026;
  const USER_NICK_FAILED = 2027;
  const USER_MOBILE_FAILED = 2028;
  const USER_MOBILE_EXIST = 2029;
  const USER_AUTHCODE_EMPTY = 2030;
  const USER_SEX_EMPTY = 2031;
  const USER_MOBILE_EMPTY = 2032;
  const USER_MOBILE_NOT_FOUND = 2033;
  const USER_MOBILE_UPDATE_FAILED = 2034;
  const USER_IS_OAUTH_BIND = 2035;
  const USER_UNBIND_FAILED = 2036;
  const USER_UNBIND_WANDA_FAILED = 2037;
  const USER_FIND_ERROR = 2038;
  const USER_MOBILE_NOT_EXIST = 2039;
  const USER_MOBILE_ERROR = 2040;
  const USER_PASSWORD_ERROR = 2041;
  const USER_PASSWORD_SAME = 2042;
  const USER_VERIFICATE_TYPE_ERROR = 2043;
  const USER_MEMBERID_FAILED = 2044;
  const USER_DELETE_MESSAGE_FAILED = 2045;
  const ANTISPAM_IMG_CODE = 2050;
  const ANTISPAM_IMG_CODE_ERROR = 2051;

  // 广场、商家、店铺相关错误信息
  const WPID_ERROR = 3001;
  const STORE_LIST_NOT_FOUND = 3002;
  const STORE_LIST_ERROR = 3003;
  const STORE_NOT_FOUND = 3004;
  const SID_ERROR = 3005;
  const STORE_DETAIL_ERROR = 3006;
  const STORE_DETAIL_NOT_FOUND = 3007;
  const BIZ_DETAIL_NOT_FOUND = 3007;
  const STORE_COUPON_NOT_FOUND = 3008;
  const STORE_COUPON_ERROR = 3009;
  const BIZ_DETAIL_ERROR = 3010;
  const STORE_DEAL_ERROR = 3011;
  const STORE_RECOMMEND_MENU_ERROR = 3012;
  const STORE_NOT_FOOD = 3013;
  const STORE_DISHES_ERROR = 3014;

  // 电影相关信息错误
  const CINEMA_ID_ERROR = 3101;
  const FILM_ID_ERROR = 3102;

  // 交易支付相关错误码段
  const FAIL_GET_PAY_URL = 4001;
  const FAIL_CREATE_PAY_ORDER = 4002;
  const FAIL_ORDER = 4003;
  const FAIL_ORDER_ID_ERROR = 4004;
  const FAIL_GET_BANKCARD_INFO = 4005;

  const FAIL_REFUND = 4101;

  // 图片相关
  const IMAGE_SERVER_UNAVAILABLE = 5001; // 图片服务器问题
  const IMAGE_UPLOAD_ERROR = 5010;       // 图片上传出错

  // APP相关
  const APP_IMEI_EMPTY = 7001;
  const APP_NAME_EMPTY = 7002;
  const APP_VERSION_EMPTY = 7003;
  const APP_OPERATOR_EMPTY = 7004;
  const APP_NETTYPE_EMPTY = 7005;
  const APP_DEVICEVERSION_EMPTY = 7006;
  const APP_MODEL_EMPTY = 7007;
  const APP_IMSI_EMPTY = 7008;
  const APP_DEVICETOKEN_EMPTY = 7009;

  // 8xxx 非通用模块错误码段
  const WANHUI_CLIENTTYPE_EMPTY = 8000;
  const WANHUI_CLIENTTYPE_NO_VERSION = 8001;
  const INTERFACE_VERSION_INVALID = 8005;
  const PLAZA_ID_INVALID = 8006;
  const EMAIL_INVALID = 8007;
  const MOBILE_INVALID = 8808;
  const FEEDBACK_COMMENT_EMPTY = 8009;
  const FEEDBACK_COMMIT_FAILED = 8810;
  const STORE_CATEGORY_INVALID = 8815;
  const START_NUM_INVALID = 8816;
  const LIST_NUM_INVALID = 8817;
  const ACTIVITY_TYPE_INVALID = 8818;
  const ACTIVITY_ID_INVALID = 8819;
  const PRODUCT_ID_INVALID = 8820;
  const PRODUCT_TYPE_INVALID = 8821;
  const STORE_ID_INVALID = 8825;
  const ADVERTISE_TYPE_INVALID = 8826;
  const ADVERTISE_LIST_ERROR = 8827;
  const CATEGORY_TYPE_INVALID = 8828;
  const MENU_CATEGORY_ID_INVALID = 8829;
  const DATA_EMPY = 8830;
  const USER_SET_PAY_PWD_OUTTIME = 9000;

  // 大歌星频道相关
  const KTV_COMPANY_ID_EMPTY = 10001;
  const KTV_COMPANY_ID_INVALID = 10002;
  const KTV_BIZDATE_EMPTY = 10003;
  const KTV_BIZDATE_INVALID = 10004;
  const KTV_CITY_ID_EMPTY = 10005;
  const KTV_CUSTOMERNAME_EMPTY = 10006;
  const KTV_CUSTOMERPHONE_EMPTY = 10007;
  const KTV_ROOMTYPE_EMPTY = 10008;
  const KTV_BEGINTIME_INVALID = 10009;
  const KTV_ROOMTYPE_INVALID = 10010;
  const KTV_BOOKINGROOM_FAILED = 10011;
  const KTV_BEGINTIME_EMPTY = 100012;
  const KTV_ROOMPRICE_EMPTY = 10013;
  const KTV_ROOMPRICE_INVALID = 10014;
  // 大歌星预定相关返回状态
  const KTV_OVER_BOOKABLE_NUM = 10020;
  const KTV_REPEAT_BOOKING = 10021;
  const KTV_BUCKET_NOT_ALLOW_BOOKING = 10022;
  const KTV_NO_ROOM_FOR_BOOKING = 10023;

  // 汉秀 Hanshow
  const HANSHOW_SEAT_SOLD_OUT = 20000;
  const HANSHOW_PRESALE_SOLD_OUT = 20001;
  // 汉秀 优惠券购买
  const TICKET_LIMITED_EDITION = 21000;
  const TICKET_NOT_GEN = 21001;

  // 预约
  const RESERVATION_MID_ERROR = 30000;
  const RESERVATION_DETAIL_FAIL = 30001;
  const RESERVATION_LIST_FAIL = 30002;
  const RESERVATION_APPID_EMPTY = 30003;
  const RESERVATION_NAME_EMPTY = 30004;
  const RESERVATION_PICSRC_EMPTY = 30005;
  const RESERVATION_TIME_EMPTY = 30006;
  const RESERVATION_INSERT_ERROR = 30007;
  const RESERVATION_USE_FAIL = 30008;
  const RESERVATION_CANCEL_FAIL = 30009;

  // 错误信息
  static $error_msg_array = array(
    // 通用错误信息
    self::ERROR_MESSAGE => '错误：%s',
    self::INTERFACE_VERSION_NOT_EMPTY => '接口版本号不能为空',
    self::START_ERROR => '查询起始位置参数不合法',
    self::MAX_NUM_ERROR => '查询最大结果数参数不合法',
    self::UNKNOWN_ERROR => '未知错误',
    self::SORRY_MESSAGE => '抱歉：%s',
    self::SPECIFIC_ERROR => '抱歉：%s',
    self::REQUEST_PARAM_ERROR => '请求数据错误',
    self::FORBIDDEN_WORD_MATCH => '%s，含有不恰当词语',

    // 用户相关错误信息
    self::USER_EXIST => '当前用户名(%s)已存在.',
    self::USER_PASSWD_EMPTY => '用户密码不能为空.',
    self::USER_NICK_EMPTY => '用户昵称不能为空.',
    self::USER_PICSRC_EMPTY => '用户头像不能为空.',
    self::USER_NICK_EXIST => '当前用户昵称(%s)已存在.',
    self::USER_OAUTH_EMPTY => '未能获取当前联合登陆的用户信息.',
    self::USER_OAUTH_SITE_NOT_SUPPORT => '不支持当前联合登陆的站点.',
    self::USER_OAUTH_UID_EXIST => '%s账号(%s)已存在.',
    self::USER_REG_FAILED => '服务器繁忙，请稍后重新获取验证码',
    self::USER_LOGIN_EMPTY => '未能获取当前登陆的用户信息.',
    self::USER_LOGIN_FAILED => '登陆失败, 用户(%s)不存在或者密码不正确.',
    self::USER_OAUTH_FAILED => '登陆失败, %s用户(%s)不存在.',
    self::USER_OAUTH_VERIFICATE_FAILED => '第三方验证失败, %s用户(%s)不合法.',
    self::USER_NAME_EMPTY => '未能获取当前用户名信息.',
    self::USER_UID_EMPTY => '未能获取想要查询的用户ID',
    self::USER_UID_NOINFO => '未查询到(UID:%s)的相关信息.',
    self::USER_ID_OR_NICK_EMPTY => '未获取到用户的ID或昵称',
    self::USER_UPDATE_FAILED => '更新用户信息失败.',
    self::USER_AUTH_FAILED => '用户验证失败，请重新登陆',
    self::USER_PUSHTOKEN_EMPTY => '推送标识不能为空',
    self::USER_CITY_ERROR => '获取用户城市失败',
    self::USER_POSITION_EMPTY => '用户坐标不能为空',
    self::USER_NICK_ERROR => '用户昵称包含非法字符',
    self::ANTISPAM_IMG_CODE => '访问频繁，请输入验证码',
    self::ANTISPAM_IMG_CODE_ERROR => '验证码错误',

    self::USER_NAME_MOBILE_ERROR => '手机用户名(%s)非法.',
    self::USER_NAME_MOBILE_AUTHCODE_ERROR => '验证码输入有误，请重新输入.',
    self::USER_NAME_FAILED => '用户名插入失败.',
    self::USER_NICK_FAILED => '昵称插入失败.',
    self::USER_MOBILE_FAILED => '手机号注册失败.',
    self::USER_MOBILE_EXIST => '手机号%s已存在.',
    self::USER_AUTHCODE_EMPTY => '手机验证码不能为空.',
    self::USER_SEX_EMPTY => '性别不能为空.',
    self::USER_MOBILE_EMPTY => '手机号不能为空.',
    self::USER_MOBILE_NOT_FOUND => '未获取到手机号.',
    self::USER_MOBILE_UPDATE_FAILED => '手机号绑定失败.',
    self::USER_MEMBERID_FAILED => '用户会员信息异常',

    self::USER_IS_OAUTH_BIND => '用户为第三方绑定手机(%s)用户，请进入修改密码流程',
    self::USER_UNBIND_FAILED => '用户解除绑定失败',
    self::USER_FIND_ERROR => '查找用户信息失败.',
    self::USER_MOBILE_NOT_EXIST => '用户手机号不存在.',
    self::USER_MOBILE_ERROR => '手机号格式错误',
    self::USER_PASSWORD_ERROR => '密码格式错误',
    self::USER_PASSWORD_SAME => '密码相同',
    self::USER_VERIFICATE_TYPE_ERROR => '验证码类型错误',
    self::USER_DELETE_MESSAGE_FAILED => '删除消息数据失败',

    // 广场、商家、店铺相关错误信息
    self::WPID_ERROR => '广场ID不合法',
    self::STORE_LIST_NOT_FOUND => '获取门店列表失败',
    self::STORE_LIST_ERROR => '获取门店列表失败，错误码(%s)错误信息(%s).',
    self::STORE_NOT_FOUND => '获取店铺详情失败',
    self::STORE_NOT_FOOD => '非餐饮类门店',
    self::SID_ERROR => '门店ID不合法',
    self::STORE_DETAIL_ERROR => '获取门店详细信息失败，错误码(%s)错误信息(%s).',
    self::STORE_DETAIL_NOT_FOUND => '获取门店详细信息失败',
    self::BIZ_DETAIL_NOT_FOUND => '获取门店详细信息失败',
    self::STORE_COUPON_NOT_FOUND => '获取门店详细信息失败',
    self::STORE_COUPON_ERROR => '获取门店优惠券失败，错误码(%s)错误信息(%s).',
    self::BIZ_DETAIL_ERROR => '获取商家详细信息失败，错误码(%s)错误信息(%s).',
    self::STORE_DEAL_ERROR => '获取门店团购信息失败.',
    self::STORE_RECOMMEND_MENU_ERROR => '获取门店推荐菜单失败.',
    self::STORE_DISHES_ERROR => '获取门店菜品失败.',

    // 电影相关信息错误
    self::CINEMA_ID_ERROR => '影院ID不合法',
    self::FILM_ID_ERROR => '影片ID不合法',

    // 交易支付相关
    self::FAIL_CREATE_PAY_ORDER => '创建订单失败',
    self::FAIL_GET_PAY_URL => '获取支付URL地址失败',
    self::FAIL_ORDER => '抱歉，您的订单已失效',
    self::FAIL_REFUND => '申请退款失败',
    self::FAIL_ORDER_ID_ERROR => '订单ID有误',
    self::FAIL_GET_BANKCARD_INFO => '查询用户银行卡信息失败',

    // 图片相关
    self::IMAGE_SERVER_UNAVAILABLE => '图片服务器问题',
    self::IMAGE_UPLOAD_ERROR => '图片上传出错',

    // APP相关
    self::APP_IMEI_EMPTY => '手机设备码不能为空',
    self::APP_NAME_EMPTY => '客户端名称不能为空',
    self::APP_VERSION_EMPTY => '用户客户端版本不能为空',
    self::APP_OPERATOR_EMPTY => '运营商不能为空',
    self::APP_NETTYPE_EMPTY => '网络类型不能为空',
    self::APP_DEVICEVERSION_EMPTY => '系统版本号不能为空',
    self::APP_MODEL_EMPTY => '机型不能为空',
    self::APP_IMSI_EMPTY => '用户识别码不能为空',
    self::APP_DEVICETOKEN_EMPTY => '推送标识不能为空',

    // 8xxx 非通用模块错误码段
    self::WANHUI_CLIENTTYPE_EMPTY => '未获取到客户端类型数据.',
    self::WANHUI_CLIENTTYPE_NO_VERSION => '未获取到客户端的最新版本信息',
    self::INTERFACE_VERSION_INVALID => '接口版本号不合法',
    self::EMAIL_INVALID => 'Email格式错误',
    self::PLAZA_ID_INVALID => '广场ID错误',
    self::FEEDBACK_COMMENT_EMPTY => '反馈内容不能为空',
    self::MOBILE_INVALID => '手机号不合法',
    self::FEEDBACK_COMMIT_FAILED => '反馈提交失败,请重试',
    self::STORE_CATEGORY_INVALID => '门店类别错误',
    self::ACTIVITY_TYPE_INVALID => '活动类型错误',
    self::START_NUM_INVALID => '查询起始位置参数不合法',
    self::LIST_NUM_INVALID => '查询最大结果数参数不合法',
    self::ACTIVITY_ID_INVALID => '活动ID错误',
    self::PRODUCT_ID_INVALID => '商品ID错误',
    self::STORE_ID_INVALID => '门店ID错误',
    self::PRODUCT_TYPE_INVALID => '商品类型错误',
    self::ADVERTISE_TYPE_INVALID => '请求广告类型错误',
    self::ADVERTISE_LIST_ERROR => '获取广告数据失败',
    self::CATEGORY_TYPE_INVALID => '分类类型错误',
    self::MENU_CATEGORY_ID_INVALID => '菜单分类ID错误',
    self::DATA_EMPY => '暂无数据',
    self::USER_SET_PAY_PWD_OUTTIME => '验证用户信息过期，请重试',

    // 大歌星频道相关
    self::KTV_COMPANY_ID_EMPTY => '大歌星公司ID不能为空',
    self::KTV_COMPANY_ID_INVALID => '大歌星公司ID错误',
    self::KTV_BIZDATE_EMPTY => '营业日期不能为空',
    self::KTV_BIZDATE_INVALID => '营业日期错误',
    self::KTV_CITY_ID_EMPTY => '大歌星城市ID为空',
    self::KTV_BEGINTIME_EMPTY => '预定开始时间不能为空',
    self::KTV_CUSTOMERNAME_EMPTY => '预定姓名必须填写',
    self::KTV_CUSTOMERPHONE_EMPTY => '手机号必须填写',
    self::KTV_ROOMTYPE_EMPTY => '包房类型不能为空',
    self::KTV_BEGINTIME_INVALID => '预定开始时间数据错误',
    self::KTV_ROOMTYPE_INVALID => '包房类型格式有误',
    self::KTV_BOOKINGROOM_FAILED => '预定失败',
    self::KTV_ROOMPRICE_EMPTY => '包房价格不能为空',
    self::KTV_ROOMPRICE_INVALID => '包房价格格式有误',
    // 大歌星预定相关返回状态
    self::KTV_OVER_BOOKABLE_NUM => '本时段包房已订满',
    self::KTV_REPEAT_BOOKING => '重复预定',
    self::KTV_BUCKET_NOT_ALLOW_BOOKING => '本时段暂不接受预定',
    self::KTV_NO_ROOM_FOR_BOOKING => '无可预订包房',

    // 汉秀 Hanshow
    self::HANSHOW_SEAT_SOLD_OUT => '您选购的座位已经被别人快一步抢走啦~ 请重新选择哦',
    self::HANSHOW_PRESALE_SOLD_OUT => '您所选的预购场已经被抢光啦~ 请重新选择哦',
    // 汉秀 优惠券购买
    self::TICKET_LIMITED_EDITION => '一位顾客只能购买一张优惠券哦~',
    self::TICKET_NOT_GEN => '您的优惠券暂未生成，请稍后再试~',
    // 预约
    self::RESERVATION_MID_ERROR => '会员ID不合法',
    self::RESERVATION_DETAIL_FAIL => '获取预约详情失败',
    self::RESERVATION_LIST_FAIL => '获取预约列表失败',
    self::RESERVATION_APPID_EMPTY => '来源类型不能为空',
    self::RESERVATION_NAME_EMPTY => '预约名称不能为空',
    self::RESERVATION_PICSRC_EMPTY => '预约图片不能为空',
    self::RESERVATION_TIME_EMPTY => '预约时间不能为空',
    self::RESERVATION_INSERT_ERROR => '创建预约失败',
    self::RESERVATION_USE_FAIL => '使用预约失败',
    self::RESERVATION_CANCEL_FAIL => '取消预约失败',

  );

  private function __construct() {
  }

  public static function GetErrorMsg() {
    $argc = func_num_args();
    if (0 == $argc)
      return NULL;
    $argv = func_get_args();
    if (array_key_exists($argv[0], self::$error_msg_array)) {
      if (1 == $argc)
        return self::$error_msg_array[$argv[0]];
      $argv[0] = self::$error_msg_array[$argv[0]];
      return call_user_func_array('sprintf', $argv);
    } else
      Log::Warn('Invalid errno:' . $argv[0]);
    return NULL;
  }

  /**
   * 填充response数组并记录日志
   *
   * @param obj $response
   * @param string $status
   * @param array $other_info
   */
  public static function FillResponseAndLog(&$response, $status, $other_info=array()) {
    $response->status = $status;

    // 如果有超过两个或更多的参数则都传递给如下方法
    array_unshift($other_info, $status); // 在数组插入一个单元
    $response->msg = call_user_func_array('ErrorMsg::GetErrorMsg', $other_info);

    $call_info = debug_backtrace();
    $log_title = isset($call_info[1]) ? $call_info[0]['file'] . ' ' .
                 $call_info[0]['line'] . ': ' . $call_info[1]['function'] .
                 ' failed:' :
                 $call_info[0]['file'] . ' ' . $call_info[0]['line'] . ': ';
    Log::Warn($log_title . $response->msg, false);
  }
}
