<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'controllers/extra/ApiController.php');
require_once(WEB_ROOT . 'models/TestModel.php');
class TestController extends ApiController
{
    protected function GetResponse()
    {
        $model = new TestModel();
        return $model->DoModel();
    }
}

