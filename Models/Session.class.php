<?php
namespace API\Models;

class Session {

	static $_users;
	
	private $secret = '';
	private $content = '';

	function __construct() {
		self::$_users = [
			['secret'=>'123',
			'content'=>'Pippo'],
		];
	}
	
	function get($keyType, $value = '') {
		if ($keyType == 'secret') {
			foreach(self::$_users as $user) {
				if ($user[$keyType] == $value) {
					$this->secret = $user['secret'];
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
