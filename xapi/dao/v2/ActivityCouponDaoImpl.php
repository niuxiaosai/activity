<?php
require_once(PHP_ROOT . 'libs/util/MysqlClient.php');
require_once(PHP_ROOT . 'libs/util/Utility.php');
require_once(FFAN_ROOT . 'utils/ToolUtil.php');


class ActivityCouponDaoImpl{
    const DB_NAME ="ff_cloud_marketing_platform";
    const TABLE_NAME= 'activity_v2_coupon';

    protected static $table_fields_map_ = array(
        'id' =>'id',
        'activityId' =>'activity_id',
        'plazaId' =>'plaza_id',
        'plazaName' =>'plaza_name',
        'couponTitle' =>'coupon_title',
        'couponNumber' =>'coupon_number',
        'unit' =>'unit',
        'dayLimit' =>'day_limit',
        'endTime' =>'end_time',
        'rank' =>'rank',
        'price' =>'price',
    );
    protected static $required_fields = array(
        'activityId','plazaId','couponTitle','couponNumber'
    );
    public function ActivityCoupons($id,array $temp, $offset=0, $limit=10){

        $conditions = array();
        foreach (self::$table_fields_map_ as $label=>$fieldName ){
            if(isset($temp[$label]))
            {
                $conditions[$fieldName]=$temp[$label];
            }

        }
            $offset = 'limit '.$offset;
            $offset .= ",$limit";

        $where_clause = '';
        $order = ' order by plaza_id,rank desc,id desc ' ;

        if(isset($conditions['plaza_id'])){
            $where_clause .= " and plaza_id='".ToolUtil::escapeSQL($conditions['plaza_id'])."'";
        }
        if(isset($conditions['coupon_number'])){
            $where_clause .= " and coupon_number='".ToolUtil::escapeSQL($conditions['coupon_number'])."'";
        }
        if(isset($conditions['plaza_name'])){
            $where_clause .= " and plaza_name like '%".ToolUtil::escapeSQL($conditions['plaza_name'])."%'";
        }
        if(isset($temp['title'])){
            $where_clause .= " and coupon_title like '%".ToolUtil::escapeSQL($temp['title'])."%'";
        }
        $where = " where activity_id='".intval($id)."' " . $where_clause ;
        $count = MysqlClient::QueryCount(self::DB_NAME,self::TABLE_NAME, $where);
        $result = MysqlClient::QueryAllFields(self::DB_NAME,self::TABLE_NAME,$where.$order. $offset);

        return array(
            'count'=> $count,
            'datas'=>$result
        );

    }
    public function CheckActivityCouponNumber($id,array $temp){

        $conditions = array();
        foreach (self::$table_fields_map_ as $label=>$fieldName ){
            if(isset($temp[$label]))
            {
                $conditions[$fieldName]=$temp[$label];
            }

        }
        $where_clause = '';
        $order = ' order by plaza_id,rank desc,id desc ' ;


        if(isset($conditions['plaza_id'])){
            $where_clause .= " and plaza_id in(".ToolUtil::escapeSQL(trim($conditions['plaza_id'],"',")).")";
        }
        if(isset($conditions['coupon_number'])){
            $where_clause .= " and coupon_number in(".ToolUtil::escapeSQL(trim($conditions['coupon_number'],"',")).")";
        }
        $where = " where activity_id='".intval($id)."' " . $where_clause ;
        $count = MysqlClient::QueryCount(self::DB_NAME,self::TABLE_NAME, $where);
        $result = MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,array('coupon_number'),$where.$order);

        foreach($result as $v){
            $re[]= $v['coupon_number'];
        }
        $re = implode(',',$re);
        return array(
            'count'=> $count,
            'datas'=>array(
                'plazaId'=>ToolUtil::escapeSQL(trim($conditions['plaza_id'],"',")),
                'queryCouponNumber'=>ToolUtil::escapeSQL(trim($conditions['coupon_number'],"',")),
                'couponNumber'=>$re,

            )
        );

    }
    public function getActivityCoupon($activityCouponId){

        $fields = array();
        foreach (self::$table_fields_map_ as $label=>$field){
            $fields[] = $field." ".$label;
        }
        $where = " where id='".ToolUtil::escapeSQL($activityCouponId)."'";
        return MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$fields,$where);
    }

    public function getActivityCouponByCouponNum($CouponNum){

        $fields = array();
        foreach (self::$table_fields_map_ as $label=>$field){
            $fields[] = $field." ".$label;
        }
        $where = " where coupon_number='".ToolUtil::escapeSQL($CouponNum)."'";
        return MysqlClient::QueryFields(self::DB_NAME,self::TABLE_NAME,$fields,$where);

    }
    public static function deleteActivityCoupon($activityCouponId){

        $where = " where id='".ToolUtil::escapeSQL($activityCouponId)."'";
        return $result = MysqlClient::Delete(self::DB_NAME,self::TABLE_NAME,$where);

    }


    public static function batchInsert(array $recordList){
        $result= array();
        foreach ($recordList as $row ){
            $result[] = self::insert($row);
        }
        return $result;

    }


    public static function insert(array $temp) {

        foreach (self::$required_fields as $fieldName){
            if (!isset($temp[$fieldName]) || empty($temp[$fieldName])){
                return array("error"=>"请求参数非法或缺失".$fieldName);
            }
        }

        $conditions = array();
        foreach (self::$table_fields_map_ as $label=>$fieldName ){
            if(isset($temp[$label]))
            {
                $conditions[$fieldName]=$temp[$label];
            }

        }

        if(!$conditions)
        {
            return array("error"=>"未获取任何参数");
        }
        if(!isset($conditions['unit']) || $conditions['unit'] == '')
        {
            $conditions['unit'] = 1;
        }

        $result = MysqlClient::InsertData(self::DB_NAME,
            self::TABLE_NAME,
            array_values(self::$table_fields_map_),
            array($conditions));
        if ($result){
            $result = MysqlClient::GetInsertID(self::DB_NAME);
        }
        return $result;
    }


    public static function update($id,$temp)
    {
        if (!is_numeric($id)){
            return array("error"=>"参数错误或缺失!");
        }

        $where=" where id='".ToolUtil::escapeSQL($id)."'";

        $conditions = array();
        foreach (self::$table_fields_map_ as $label=>$fieldName ){
            if(isset($temp[$label]))
            {
                $conditions[$fieldName]=$temp[$label];
            }

        }
        if(empty($conditions)) {
            return array("error"=>"未获取任何修改参数");
        }

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

    public static function batchUpdate(array $ids,$condition){
        $result= array();
        foreach ($ids as $id ){
            $result[$id] = self::update($id,$condition);
        }
        return $result;
    }
    public static function batchDelete(array $ids){
        $result= array();
        foreach ($ids as $id ){
            $result[$id] = self::deleteActivityCoupon($id);
        }
        return $result;
    }

    public function getActivityPlazaList($activity_id)
    {
        $sql = 'SELECT `plaza_id`,`plaza_name` FROM `'
            . self::TABLE_NAME
            . '` WHERE `activity_id`=' . intval($activity_id) . ' group by plaza_id';

        $coupons = (array)MysqlClient::ExecuteQuery(self::DB_NAME, $sql);
        return array('list' => $coupons, 'count' => count($coupons));
    }

    /**
     * 获取活动配置的券的列表
     *
     * @param int $activity_id 活动id，必须
     * @param int $plaza_id 广场id，可多选，用逗号分隔，已经确定广场时使用
     * @param string $title 券标题，可选，搜索标题时使用
     * @param int $page 页数，可选，从0开始
     * @param int $page_size 每页条数，可选，默认10条
     * @param int $uid
     * @param string $coupon_number
     * @return array
     */
    public function getCouponList($activity_id, $plaza_id = 0, $title = '', $page = 0, $page_size = 10, $uid = 0, $coupon_number='')
    {
        $now = time();
        $activity_id = intval($activity_id);
        $sql = 'SELECT `coupon_title`,`coupon_number`,`end_time`,`price`,`day_limit`,`plaza_id`,`plaza_name` FROM `'
            . self::TABLE_NAME
            . '` WHERE `activity_id`=' . intval($activity_id) . ' AND `end_time`>' . $now;
        $total_sql = 'SELECT COUNT(`id`) AS `total` FROM `'
            . self::TABLE_NAME
            . '` WHERE `activity_id`=' . intval($activity_id) . ' AND `end_time`>' . $now;
        if ($plaza_id) {
            $sql .= ' AND `plaza_id` IN(' . ToolUtil::escapeSQL($plaza_id) . ')';
            $total_sql .= ' AND `plaza_id` IN(' . ToolUtil::escapeSQL($plaza_id) . ')';
        }
        if ($title) {
            $sql .= ' AND `coupon_title` LIKE \'%' . ToolUtil::escapeSQL($title) . '%\''; // 临时的搜索策略，后续需要搜索服务支持
            $total_sql .= ' AND `coupon_title` LIKE \'%' . ToolUtil::escapeSQL($title) . '%\''; // 临时的搜索策略，后续需要搜索服务支持
        }
        if($coupon_number){
            $sql .= ' AND `coupon_number` =\'' . ToolUtil::escapeSQL($coupon_number) . '\'';
            $total_sql .= ' AND `coupon_number` =\'' . ToolUtil::escapeSQL($coupon_number) . '\'';
        }
        $start = (int)($page * $page_size);
        $end = (int)$page_size;
        $sql .= " ORDER BY `plaza_id`,`rank` DESC, `id` DESC LIMIT {$start},{$end}";
        $coupons = (array)MysqlClient::ExecuteQuery(self::DB_NAME, $sql);
        $total = MysqlClient::ExecuteQuery(self::DB_NAME, $total_sql);
        $count = empty($total) ? 0 : $total[0]['total'];
        if (empty($coupons)) {
            return array('list' => $coupons, 'count' => $count);
        }

        $coupon_numbers = array();
        foreach ($coupons as $tmp) {
            $coupon_numbers[] = "'{$tmp['coupon_number']}'";
        }
        //检查和处理日限量的余量
        $coupon_numbers = implode(',', $coupon_numbers);
        $today = date('Ymd');
        $used_sql = "SELECT COUNT(`id`) AS `used`,`coupon_number`
                      FROM `activity_v2_user_coupon`
                      WHERE `activity_id`={$activity_id} AND `coupon_number` IN ({$coupon_numbers}) AND `get_day`={$today}
                      GROUP BY `coupon_number`";
        $used = MysqlClient::ExecuteQuery(self::DB_NAME, $used_sql);
        foreach ($used as $key => $tmp) {
            $used[$tmp['coupon_number']] = $tmp['used'];
            unset($used[$key]);
        }
        foreach ($coupons as &$tmp) {
            if (!empty($tmp['day_limit'])) {
                $spend = empty($used[$tmp['coupon_number']]) ? 0 : $used[$tmp['coupon_number']];
                $tmp['day_limit'] = $tmp['day_limit'] - $spend;
            } else {
                $tmp['day_limit'] = '-'; // 以-表示没有配置日限量
            }
            $tmp['has'] = 0;
        }
        unset($tmp);

        // 检查是否抢过
        if ($uid) {
            $uid = ToolUtil::escapeSQL($uid);
            $sql = "SELECT `coupon_number` FROM `activity_v2_user_coupon`
                    WHERE `activity_id`={$activity_id} AND `uid`='{$uid}' AND `coupon_number` IN ({$coupon_numbers}) AND `get_day`={$today}";
            $have = MysqlClient::ExecuteQuery(self::DB_NAME, $sql);
            if ($have) {
                foreach ($have as $key => $tmp) {
                    $have[$tmp['coupon_number']] = 1;
                    unset($have[$key]);
                }
                foreach ($coupons as &$tmp) {
                    if (isset($have[$tmp['coupon_number']])) {
                        $tmp['has'] = 1;
                    }
                }
            }
        }

        return array('list' => $coupons, 'count' => $count);
    }

}
