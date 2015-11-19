<?php
namespace API\Models;

define('BACKEND_ROOTPATH','/var/www/ms3');
define('BACKEND_ROOTWEB','/ms3');

abstract class MainsimModel {

	protected static $_config;
        protected static $_app;

	function __construct() {
            if (empty(self::$_config))
            {
                require_once realpath(BACKEND_ROOTPATH.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'config.php');
                self::$_config = new \Zend_Config_ini(APPLICATION_PATH.'/configs/application.ini','production');
                \Zend_Registry::set('attachmentsFolder',self::$_config->attachmentsFolder);
                \Zend_Registry::set('db', self::$_config->database);
            }
        }

        function getApp() {
            if (empty(self::$_app))
            {
                self::$_app = new \Zend_Application(
                    APPLICATION_ENV,
                    APPLICATION_PATH . '/configs/application.ini');
                self::$_app->bootstrap();
            }
            return self::$_app;
        }
        
        /**
         * Uniforming to Mainsim Backend eNotice should be disabled to works 
         * properly. Deprecated
         * @param bool $active if active trigger them, false otherwise
         */
        function triggerNotice ($active = true)
        {
            $error = $active? E_ALL : E_ALL & ~E_NOTICE;
            error_reporting($error);
        }
}
?>
