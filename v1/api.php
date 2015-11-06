<?php
namespace API\v1;

$default = 'Main';
require_once $default.'.php';

//var_dump($_REQUEST+$_SERVER);die();

//i Requests from the same server don't have a HTTP_ORIGIN header
// HTTP_ORIGIN is a way to protect against CSRF (Cross Site Request Forgery) requests
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

//$entity = (array_key_exists('entity', $_REQUEST))?'':'example';
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
    $action = 'default';
    if (array_key_exists('action', $_REQUEST) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
    }
    $API = new $class($action, $_SERVER['HTTP_ORIGIN']);
    exit($API->processAPI());
} catch (\Exception $e) {
    if ($e->getCode()) {
        exit($class::error($e->getMessage(),$e->getCode()));
    } else {
        exit($class::error($e->getMessage()));
    }
}
?>