<?php
/*
 * creator: hexuan
 * */
namespace core;
class ActivityLogDao extends \BaseDao
{
  protected $DB_NAME = 'ff_cloud_marketing_platform';
  protected $TABLE_NAME = 'activity_log';
  protected $CACHE_TABLE = 'ff_cloud_marketing_platform.activity_log';
  protected $KEY = 'activity_id';
}
