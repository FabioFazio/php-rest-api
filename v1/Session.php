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
        $file = join('', array_slice(explode('\\', __CLASS__), -1)).'.config.php';
        parent::$_config = require $file;
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
        $session = new Models\Session(
                $this->request['username'],$this->request['password']);
        $this->session = $session;
        
        require_once realpath('/var/www/ms3'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'config.php');
        $config = new \Zend_Config_ini(APPLICATION_PATH.'/configs/application.ini','production');
        \Zend_Registry::set('attachmentsFolder',$config->attachmentsFolder);			
        \Zend_Registry::set('db', $config->database); 
        $login = new \Mainsim_Model_Login();
        $auth_res = $login->authenticate($this->request['username'], $this->request['password']);
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
        require_once realpath('/var/www/ms3'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'config.php');
        $config = new \Zend_Config_ini(APPLICATION_PATH.'/configs/application.ini','production');
        \Zend_Registry::set('attachmentsFolder',$config->attachmentsFolder);			
        \Zend_Registry::set('db', $config->database); 
        
        /**
         * UI data
         */
        $ui = new \Mainsim_Model_UI();
        $lastEdit = $ui->getLastUIEdited();        
        $sessionTs = empty($_SESSION['ui'])?0:$_SESSION['ui']['ts'];
        
        if($sessionTs < $lastEdit){
            $_SESSION['ui']['data']['tabbars'] = $ui->getTabbars();
            $_SESSION['ui']['data']['textfields'] = $ui->getTextfields();
            $_SESSION['ui']['data']['picklists'] = $ui->getPicklists();
            $_SESSION['ui']['data']['checkboxes'] = $ui->getCheckboxes();
            $_SESSION['ui']['data']['menus'] = $ui->getMenus();
            $_SESSION['ui']['data']['buttons'] = $ui->getButtons();
            $_SESSION['ui']['data']['modules'] = $ui->getModules();
            $_SESSION['ui']['data']['layouts'] = $ui->getLayouts();
            $_SESSION['ui']['data']['selects'] = $ui->getSelects();
            $_SESSION['ui']['data']['images'] = $ui->getImages();
            $_SESSION['ui']['ts'] = time();
        }
        
        $data = $_SESSION['ui']['data'];        
        //BASEURL
        $data["global"] = array();
        $data["global"]["delegates"] = array();
        
        $weekStartsOn = 1;
        $data["global"]['weekStartsOn'] = $weekStartsOn;

        /**
         * Login data
         */
        $login = new \Mainsim_Model_Login();        
        $data["global"]['online'] = $login->getOnlineNum();        
        $data["global"]['user'] = $this->getUserData();
/*
        $data["global"]['baseUrl'] = $this->view->baseUrl();
        $data["global"]['imgDefaultPath'] = IMAGE_DEFAULT_PATH;
        $data["global"]['imgPath'] = IMAGE_PATH;
        $data["global"]['attachPath'] = ATTACHMENT_PATH;
        $data["global"]['libraryPath'] = LIBRARY_PATH;    
*/      
        /**
         * Settings data
         */
        $main = new \Mainsim_Model_Mainpage();
        $settings = $main->getSettings(true);
        foreach($settings as $k => $v) {
            $data["global"][$k] = \Mainsim_Model_Utilities::chg($v);
        }
        
        /**
         * Wo priorities
         */
        $data["global"]["priorities"] = $main->getPriorities();

        /**
         * Easter
         */
        $data["global"]['easters'] = $main->getEasters();
                
        /**
         * Localization
         */
        $userinfo = \Zend_Auth::getInstance()->getIdentity();
        $data["global"]["localization"] = $ui->getLocalization($userinfo->f_language);
        
        /**
         * Reverse Ajax Initialization
         */
        $data["global"]["revajax"] = array("t_workorders" => array(), "t_wares" => array(), "t_selectors" => array(), "t_systems" => array());
        $q = new \Zend_Db_Select(\Zend_Db::factory(\Zend_Registry::get('db')));
        foreach($data["global"]["revajax"] as $k => $v) {
            $q->from($k."_types", array("f_id"));
            $res = $q->query()->fetchAll();
            $q->reset();
            foreach($res as $r) $data["global"]["revajax"][$k][$r["f_id"]] = array();
        }
        
        /**
         * Regular expressions
         */
        $data["regexp"] = array(
            "mail" => "/^(([^<>()[\\]\\\\.,;:\\s@\\\"] (\\.[^<>()[\\]\\\\.,;:\\s@\\\"] )*)|(\\ \". \\\"))@((\\[[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\])|(([a-zA -Z\\-0-9] \\.) [a-zA-Z]{2,}))$/",
            "password" => "/^[a-zA-Z0-9\\@\\#\\-\\_\\.]{3,32}$/",
            "username" => "/^[a-zA-Z0-9\\@\\#\\-\\_\\.]{3,32}$/"
        );        
        
        return [ 'data' => $data ];
     }
    
    /**
     * 
     * @return type
     */
    private function getUserData() {
        require_once realpath('/var/www/ms3'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'config.php');
        $config = new \Zend_Config_ini(APPLICATION_PATH.'/configs/application.ini','production');
        \Zend_Registry::set('attachmentsFolder',$config->attachmentsFolder);			
        \Zend_Registry::set('db', $config->database); 
        
        $userinfo = \Zend_Auth::getInstance()->getIdentity();
        $sc = new \Mainsim_Model_StartCenter();
        return array(
            "code" => $userinfo->f_id,
            "fullname" => \Mainsim_Model_Utilities::chg($userinfo->f_displayedname),
            "firstname" => \Mainsim_Model_Utilities::chg($userinfo->fc_usr_firstname),
            "lastname" => \Mainsim_Model_Utilities::chg($userinfo->fc_usr_lastname),
            "gender" => $userinfo->f_gender == 0 ? "M" : "F",
            "avatar" => $userinfo->fc_usr_avatar,
            "level" => $userinfo->f_level,
            "level_text" => $userinfo->fc_usr_level_text,
            "groupLevel" => $userinfo->f_group_level,
            "status" => $userinfo->f_status,
            "address" => $userinfo->fc_usr_address,
            "phone" => $userinfo->fc_usr_phone,
            "username" => $userinfo->fc_usr_usn,
            "defaultPassword" => ($userinfo->fc_usr_pwd == '313ed0558152d04dc20036dbd850dc9bd'),
            //"defaultProject" => $userinfo->fc_usr_default_project,          /* MULTI PROJECT */
            "accountExpiration" => $userinfo->fc_usr_usn_expiration,
            "pwdExpiration" => $userinfo->fc_usr_pwd_expiration,
            "accountCreation" => $userinfo->fc_usr_usn_registration,
            "email" => $userinfo->fc_usr_mail,
            "language" => $userinfo->f_language,
            "bulletins" => count($sc->getBulletins()),
            "fc_usr_mobile_autoswitch_desktop" => $userinfo->fc_usr_mobile_autoswitch_desktop
        );
    }
    
    /**
     * 
     */
    public function signout() {
        require_once realpath('/var/www/ms3'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'config.php');
        $config = new \Zend_Config_ini(APPLICATION_PATH.'/configs/application.ini','production');
        \Zend_Registry::set('attachmentsFolder',$config->attachmentsFolder);			
        \Zend_Registry::set('db', $config->database); 
        
        if(\Zend_Auth::getInstance()->hasIdentity()) {
            $userinfo = \Zend_Auth::getInstance()->getIdentity();
            $logout = new \Mainsim_Model_Login();
            $logout->logout($userinfo->f_id);
            \Zend_Auth::getInstance()->clearIdentity();            
        }
        return ["feedback"=>"Bye Bye!"];
    }
    
    /**
     * 
     * @return type
     * @throws \Exception
     */
    public function recover() {
        require_once realpath('/var/www/ms3'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'config.php');
        $config = new \Zend_Config_ini(APPLICATION_PATH.'/configs/application.ini','production');
        \Zend_Registry::set('attachmentsFolder',$config->attachmentsFolder);			
        \Zend_Registry::set('db', $config->database); 
        $login = new \Mainsim_Model_Login();
        
        $errors = $login->confirmRecovery($this->request['username'],$this->request['email']);
        if(empty($errors)) {
            return ["feedback" => "An email has been sent to " . $this->request['email']];
        }
        //die(sprintf('<pre>%s</pre>',print_r($errors,1)));
        throw new \Exception(current($errors),406);
    }
 }
?>
