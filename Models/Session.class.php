<?php
namespace API\Models;

require_once 'MainsimModel.class.php';

class Session extends MainsimModel {

    private $login;
    private $auth;

    function __construct() {
        parent::__construct();
        $this->login = new \Mainsim_Model_Login();
        $this->auth = \Zend_Auth::getInstance();
    }

    function authenticate($username, $password) {
        return $this->login->authenticate($username, $password);
    }

    /**
     * 
     * @return boolean
     */
    function delete() {
        if($this->auth->hasIdentity()) {
            $userinfo = $this->auth->getIdentity();
            $this->login->logout($userinfo->f_id);
            $this->auth->clearIdentity();            
        }
        return true;
    }
    
    function recover($username, $email) {
        return $this->login->confirmRecovery($username, $email);
    }
    
    function get() {
        $this->getApp();
        $data = [];
        if ($this->auth->hasIdentity())
        {
            $userinfo = $this->auth->getIdentity();
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
            $data["global"]['online'] = $this->login->getOnlineNum();        
            $data["global"]['user'] = $this->getUserData($userinfo);

            /**
             *  Constants not used by mainsim4
             */
            //$data["global"]['baseUrl'] = $this->view->baseUrl();
            $data["global"]['imgDefaultPath'] = IMAGE_DEFAULT_PATH;
            $data["global"]['imgPath'] = IMAGE_PATH;
            $data["global"]['attachPath'] = ATTACHMENT_PATH;
            $data["global"]['libraryPath'] = LIBRARY_PATH;    

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
        }
        return $data;
    }

   /**
    * 
    * @return type
    */
    private function getUserData($userinfo) {
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
    
    public function getCaptcha(){
        $register = new \Mainsim_Model_Registration(null, BACKEND_ROOTPATH . DIRECTORY_SEPARATOR);
        $secret = $register->createCaptcha();
        $path = BACKEND_ROOTWEB . '/' . $register->captchaDir;
        return ['captcha' => $secret, 'path' => $path ];
    }
    
    public function checkCaptcha($captcha){
        $register = new \Mainsim_Model_Registration(null, BACKEND_ROOTPATH . DIRECTORY_SEPARATOR);
        return $register->checkCaptcha($captcha);
    }
    
    public function signup($request){
        $register = new \Mainsim_Model_Registration(null, BACKEND_ROOTPATH . DIRECTORY_SEPARATOR);
    
        $params = require CONFIG_PATH . DIRECTORY_SEPARATOR. 'defaultUser.config.php';
        $username = $prefix = $request['name']. '.' .$request['surname'];
        $count = 0;
        $login = new \Mainsim_Model_Login();
        
        while($login->userExists($username)){
            $username = $prefix.'.'.$count++;
        }
        
        $params['fc_usr_firstname'] = $request['name'];
        $params['fc_usr_lastname'] = $request['surname'];
        $params['f_title'] = $request['name'].' '.$request['surname'];
        $params['fc_usr_gender'] = '0';                     // force male
        $params['fc_usr_address'] = '';                     // empty
	$params['fc_usr_phone'] = '';                       // empty
        $params['fc_usr_mail'] = $request['email'];
        $params['fc_usr_usn'] = $username;
        $params['fc_usr_password'] = $request['password'];
        $params['fc_usr_repeat_pwd'] = $request['password'];
        $params['fc_usr_language'] = '2';                   // force italian
        $params['fc_usr_language_str'] = 'Italian';         // force italian
        $params['fc_usr_level'] = 64;                       // default
        $params['fc_usr_level_text'] = 'Supervisor PRO';    // default
	$params['fc_usr_pwd_registration'] = time();        // timestamp

        $wares = new \Mainsim_Model_Wares();
        $result = array();
        $this->triggerNotice(0);
        $result = $wares->newWares($params, [], true, true);
        $this->triggerNotice(1);
        return $result;
    }
}
?>
