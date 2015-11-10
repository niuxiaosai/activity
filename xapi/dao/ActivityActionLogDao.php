<?php
/*
 * creator: hexuan
 * */
namespace core;
class ActivityActionLogDao extends \BaseDao
{
  protected $DB_NAME = 'ff_cloud_marketing_platform';
  protected $TABLE_NAME = 'activity_actionlog';
  protected $CACHE_TABLE = 'ff_cloud_marketing_platform.activity_actionlog';
  protected $KEY = 'activity_id';
}
