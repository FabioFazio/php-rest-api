<?php
namespace API\v1;

$default = 'Main';
$action = 'defaultResponce';
require_once $default.'.php';

//var_dump(['_REQUEST'=>$_REQUEST]+['_SERVER'=>$_SERVER]);die();

// Requests from the same server don't have a HTTP_ORIGIN header
// HTTP_ORIGIN is a way to protect against CSRF (Cross Site Request Forgery) requests
// Contructor will check if temporary key is valid for specific origin
// For more info see this link:
// http://stackoverflow.com/questions/4566378/how-secure-is-http-origin
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

$entity = (array_key_exists('entity', $_REQUEST))? ucfirst($_REQUEST['entity']): '';
$class = __NAMESPACE__.'\\';

if ($entity && file_exists($entity.'.php')){
	require_once $entity.'.php';
        $class .= $entity;
} else {
        $class .= $default;
	exit($class::serviceUnavaiable($entity));
}
try {
    // load action from url or keep default
    if (array_key_exists('action', $_REQUEST) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
    }
    // Load API for entity required
    $API = new $class($action, $_SERVER['HTTP_ORIGIN']);
    // Execute action
    exit($API->processAPI());
} catch (\Exception $e) {
    // any exception is an http response error
    exit($class::error($e->getMessage(),$e->getCode()));
    //die(sprintf('<pre>%s</pre>',print_r($var,1)));
}
?>