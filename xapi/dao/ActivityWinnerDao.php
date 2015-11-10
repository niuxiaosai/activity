<?php
/*
 * creator: hexuan
 * */
namespace core;
class ActivityWinnerDao extends \BaseDao
{
  protected $DB_NAME = 'ff_cloud_marketing_platform';
  protected $TABLE_NAME = 'activity_winner';
  protected $CACHE_TABLE = 'ff_cloud_marketing_platform.activity_winner';
  protected $KEY = 'activity_id';
}
