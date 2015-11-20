<?php
namespace API\v1;

use API\Core as Core;
use API\Models as Models;

require_once '../Core/API.class.php';
require_once '../Models/Session.class.php';

class Session extends Core\API
{
    protected $session;
    
    public function __construct($request, $origin) {
        parent::__construct($request);
        $file = CONFIG_PATH .join('', array_slice(explode('\\', __CLASS__), -1)).'.config.php';
        parent::$_config = require $file;
        
        $this->session = new Models\Session();
    }

    /**
     * Default responce with documentation
     * @return array to encode in json
     */     
    function defaultResponce () {
         return $this->documentation();
    }
    
    /**
     * Session authentication
     * @param (see config file)
     * @return string
     */
     protected function signin() {
        $auth_res = $this->session->authenticate($this->request['username'],$this->request['password']);
        if ($auth_res !== true)
        {
            switch ($auth_res) {
                case -3: 
                case -1:
                    throw new \Exception("Incorrect username or password", 200);
                    break;
                case 'DOUBLE_LOGIN':
                    throw new \Exception("mainsim session already open", 200);
                    break;
                case -100 :
                    throw new \Exception("Application under maintenance. Please retry later", 503);
                    break;
                default:
                    throw new \Exception("Database error", 500);
                    break;
            }
        }
        return [
            "feedback"  =>["success"=>"Welcome!"],
            "result" =>["secret"    =>$auth_res],
        ];
     }
     
     /**
      * Get all session data
      * @return json array with medatada
      * @throws \Exception if something go wrong
      */
     protected function get() {
        
        $session = $this->session->get();
        if (empty($session)){
            throw new \Exception("No session available", 406);
        }
        return [
            "result" =>["session" => $session ]
        ];
     }
    
    /**
     * 
     */
    public function signout() {

        if ($this->session->delete())
        {
            return ["feedback"=>["success"=>"Bye Bye!"]];
        } else {
            throw new \Exception(); 
        }
    }
    
    /**
     * 
     * @return type
     * @throws \Exception
     */
    public function recover() {
        
        $errors = $this->session->recover($this->request['username'],$this->request['email']);
        if(empty($errors)) {
            return ["feedback" =>["success" => "An email has been sent to " . $this->request['email']]];
        }
        //die(sprintf('<pre>%s</pre>',print_r($errors,1)));
        throw new \Exception(current($errors),406);
    }

    /**
     * 
     * @return type
     * @throws \Exception
     */
    public function signup() {
        
        if($this->session->checkCaptcha($this->request['captcha']))
        {
            $code = $this->session->signup($this->request);
        }else{
            throw new \Exception("Wrong captcha text. Please try again!", 400);
        }
        return ["result" =>['code' => $code]];
    }
    
    public function captcha() {
        
        $result = $this->session->getCaptcha();
        return ["result" =>$result];
    }
 }
?>
