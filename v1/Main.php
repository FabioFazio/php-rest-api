<?php
namespace API\v1;

use API\Core as Core;
use API\Models as Models;

require_once '../Core/API.class.php';
require_once '../Models/APIKey.class.php';
require_once '../Models/Box.class.php';

class Main extends Core\API
{
    protected $box;
    
    public function __construct($request, $origin) {
        parent::__construct($request);
	parent::$_documentation = [
		'a'=>'1'
	];

        $box = new Models\Box();

	if (false && $apiKeyEvaluation) //TODO prevent attacks with CSR
	{
            $APIKey = new Models\APIKey();
            if (!array_key_exists('apiKey', $this->request)) {
            // if API version lacks
                throw new \Exception('No API Key provided');
            } else if (!$APIKey->verifyKey($this->request['apiKey'], $origin)) {
            // if API version is not valid for the origin
                throw new \Exception('Invalid API Key');
            }
	}
    }

    /**
     * Example of an Endpoint
     */
     protected function example() {

        $box = new Models\Box();

        if ($this->method == 'GET') {
            $required = []; $badFormat = [];
            foreach(['token'] as $field) {
		if (!array_key_exists($field, $this->request)) {
		   $required[] = $field;
                } elseif ($this->request[$field] == '0') {
                    //TODO use regexp
                   $badFormat[] = $field;
                }
	    }
            if (!empty($required) || !empty($badFormat)) {
                $this->badRequest($required, $badFormat);
            }
            
	    if (!$box->get('token', $this->request['token'])) {
		$this->badRequest();
	    }
            $this->box = $box;
            return "Your box content is " . $this->box->get('content');
        } else {
            return "Only accepts GET requests";
        }
     }
 }
?>