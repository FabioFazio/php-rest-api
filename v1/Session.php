<?php
namespace API\v1;

use API\Core as Core;
use API\Models as Models;

require_once '../Core/API.class.php';
require_once '../Models/Session.class.php';

class Session extends Core\API
{
    protected $user;
    
    public function __construct($request, $origin) {
        parent::__construct($request);
        parent::$_settings = [
            'signin'=>'',
            'signout'=>'',
            'signup'=>'',
            'recover'=>''
        ];
    }

    /**
     * Example of an Endpoint
     */
     protected function signin() {
        if ($this->method == 'GET') {
	    foreach(['username','password'] as $field) {
		if (!array_key_exists($field, $this->request))
		{
		   //TODO standard http code error
		   return "Request input are wrong";
		}
	    }
            $session = new Models\Session();
            
            
            
//            if (array_key_exists('secret', $this->request) &&
//                !$session->get('secret', $this->request['secret'])) {
//            // if token lacks or doesn't grant an authenticated session
//                throw new \Exception('Session not available');
//            }

            // Initialize the user
            $this->session = $session;
            if (true)
            {
               return "Welcome";
            }
            return "Your box content is " . $this->box->get('content');
        } else {
		//TODO standard http code error
		return "Only accepts POST requests";
        }
     }
     
    function defaultResponce () {
         return $this->documentation();
     }
 }
?>
