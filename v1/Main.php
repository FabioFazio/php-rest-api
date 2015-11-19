<?php
namespace API\v1;

use API\Core as Core;
use API\Models as Models;

require_once '../Core/API.class.php';
require_once '../Models/APIKey.class.php';
require_once '../Models/Box.class.php'; // An example of model used for exercice

/**
 * Main Class is required in any version of API and will manage default behaviour
 * and wrong entity requirest
 */
class Main extends Core\API
{
    protected $box;
    
    public function __construct($request, $origin) {
        parent::__construct($request);
        $file = CONFIG_PATH.join('', array_slice(explode('\\', __CLASS__), -1)).'.config.php';
        parent::$_config = require $file;

        $box = new Models\Box();

	if (false && $apiKeyEvaluation) //TODO
	{
            // Prevent CSRF attacks evaluating if apiKey is active for $origin
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
        if (!$box->get('token', $this->request['token'])) {
            return [];
        }
        $this->box = $box;
        return ["message" => "Your box content is " . $this->box->get('content')];
     }
     
     function defaultResponce () {
         return self::documentation();
     }
 }
?>