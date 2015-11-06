<?php
namespace API\Models;

class APIKey {

	protected $keys;

	function __construct() {
		$this->keys = [
			'a' => ['origin' => 'saruman'],
		];
	}
	
	function verifyKey( $apiKey, $origin) {
		return (
			array_key_exists($apiKey, $this->keys) &&
			$this->keys[$apiKey]['origin'] == $origin
		);				
	}
}
?>
