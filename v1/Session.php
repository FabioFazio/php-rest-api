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
        $file = join('', array_slice(explode('\\', __CLASS__), -1)).'.config.php';
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
                    throw new \Exception("Incorrect username or password", 400);
                    break;
                case 'DOUBLE_LOGIN':
                    throw new \Exception("mainsim session already open", 406);
                    break;
                case -100 :
                    throw new \Exception("Application under maintenance. Please retry later", 503);
                    break;
                default:
                    throw new \Exception("Database error", 500);
                    break;
            }
        }
        return ["feedback"=>"Welcome!"];
     }
     
     /**
      * Get all session data
      * @return json array with medatada
      * @throws \Exception if something go wrong
      */
     protected function get() {
        $data = $this->session->get();
        if (empty($data)){
            throw new \Exception("No session available", 406);
        } else {
            return [ 'data' => $data ];
        }
     }
    
    /**
     * 
     */
    public function signout() {

        if ($this->session->delete())
        {
            return ["feedback"=>"Bye Bye!"];
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
            return ["feedback" => "An email has been sent to " . $this->request['email']];
        }
        //die(sprintf('<pre>%s</pre>',print_r($errors,1)));
        throw new \Exception(current($errors),406);
    }

    public function signup() {
        $register = new \Mainsim_Model_Registration(null, BACKEND_ROOTPATH . DIRECTORY_SEPARATOR);
        $result = $register->newUser($this->request);
        if($result['code'] == 'OK'){
            return $result;
        }else{
            $wRegcf = $register->Regcf;
            $postData = $this->request;
            $language = 1;//$register->get_language();
            $dictionary = 1;//$register->get_translations($this->view->language);
            $message = $result['message'];
        }
        throw new \Exception( $message , 406 );
    }
    
    public function catcha() {
        $result = $this->session->getCaptcha();
        return $result;
    }
 }
?>
