<?php
namespace API\Models;

class Box {

	static $_boxes;
	
	private $token = '';
	private $content = '';

	function __construct() {
		self::$_boxes = [
			['token'=>'123',
			'content'=>'Pippo'],
		];
	}
	
	function get($keyType, $value = '') {
		if ($keyType == 'token') {
			foreach(self::$_boxes as $user) {
				if ($user[$keyType] == $value) {
					$this->token = $user['token'];
					$this->content = $user['content'];
	                        	return true;
				}
			}
                } elseif (isset ($this->$keyType)) {
			return $this->$keyType;
		}
		return false;
	}
}
?>
