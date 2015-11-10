<?php
/*
 * creator: hexuan
 */
require_once (WEB_ROOT . 'models/extra/BaseModel.php');
class NotFoundModel extends BaseModel {
	public function GetResponse() {
		$response = new Response ();
		$data = array (
				'hello all' => 'your uri is not found' 
		);
		$response->data = $data;
		return $response;
	}
}
