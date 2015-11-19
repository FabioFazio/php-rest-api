<?php
/**
 *	this class will act as a wrapper for all of the custom endpoints that our API will be using.
 *	To that extent, it must be able to take in our request, grab the endpoint from the URI string,
 *	detect the HTTP method (GET, POST, PUT, DELETE) and assemble any additional data provided in the header or in the URI.
 * 	Once that's done, the abstract class will pass the request information on to a method in the concrete class to actually perform the work.
 *	We then return to the abstract class which will handle forming a HTTP response back to the client.
 */
namespace API\Core;

define('CONFIG_PATH','config/');

abstract class API
{
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';
    /**
     * Property: endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $endpoint = '';
    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = '';
    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>
     */
    protected $args = Array();
    /**
     * Property: file
     * Stores the input of the PUT request
     */
    protected $file = Null;

    static protected $_config = Null;

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    public function __construct($request) {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->args = explode('/', rtrim($request, '/'));
        $this->endpoint = array_shift($this->args);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new \Exception("Unexpected Header");
            }
        }

        switch($this->method) {
        case 'GET':
            $this->request = $this->_cleanInputs($_GET);
            break;
        case 'POST':
            $this->request = $this->_cleanInputs($_POST);
            $this->get = $this->_cleanInputs($_GET);
            break;
        case 'PUT':
        case 'DELETE':
            $this->file = file_get_contents("php://input");
            parse_str($this->file, $this->request);
            $this->get = $this->_cleanInputs($_GET);
            break;
        default:
            $this->_response(["feedback"=>["error"=>"Invalid Method"]], 405);
            break;
        }
    }

    /**
     * Evaluate input calls detecting method to use for them.
     * When no methods are available return a 404
     */
    public function processAPI() {
        if (method_exists($this, $this->endpoint)) {
            // run all checks defined in config
            if ($config = self::$_config) {
                $name = $this->endpoint;
                $options = !(array_key_exists('service', $config) && array_key_exists('actions', $config['service'])) ?
                        false :  current ( array_filter ($config['service']['actions'],
                                    function ($elem) use ($name) {return $elem['name']==$name;}));
                if ($options) {
                    if (array_key_exists('method', $options) &&
                            strtoupper($options['method']) != strtoupper($this->method)) {
                        throw new \Exception("Only accepts ".strtoupper($options['method'])." requests", 405);
                    }
                    $required = []; $badFormat = [];
                    foreach ($options['input'] as $name => $filters) {
                         if (array_key_exists('required', $filters) && $filters['required'] &&
                                !array_key_exists($name, $this->request)){
                            $required[] = $name;
                        }
                         if (array_key_exists('regexp', $filters) && $filters['regexp'] &&
                                array_key_exists($name, $this->request) &&
                                !preg_match($filters['regexp'], $this->request[$name])){
                            $badFormat[] = $name;
                        }
                    }
                    if (!empty($required) || !empty($badFormat)) {
                        $this->badRequest($required, $badFormat);
                    }
                }
            }
            return $this->_response($this->{$this->endpoint}($this->args));
        }
	if ($this->endpoint) {
	    return $this->_response(["feedback"=>["error"=>"No Endpoint: $this->endpoint"]], 404);
	} else {
	    return $this->_response($this->documentation());
	}
    }

    /**
     * Generate responce with data and http code
     */
    private static function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . self::_requestStatus($status));
	return json_encode($data);
    }

    /**
     * Translate input in a clean data: leaves trimmed and sptripped
     */
    private function _cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    /**
     * Describe status in header from a config array
     */
    private static function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
            201 => 'Created',
            406 => 'Not acceptable', // a feedback explain unexpected result
            400 => 'Bad Request', // Input wrong
            401 => 'Unauthorized', // Session expired or malicious requests
            404 => 'Not Found',  // (actually not used)
            405 => 'Method Not Allowed', // Protocol Method unused
            500 => 'Internal Server Error', // Generic Error
	    503 => 'Service unavailable' // Unavailable or not implemented
        ); 
        return ($status[$code])?$status[$code]:$status[500];
    }

   protected function badRequest ($required=[], $badFormat=[]) {
       $msgRequired = "";
       $msgBadFormat = "";
       if(!empty($required)){
           $msgRequired = "Input fields required are '" . implode("', '", $required) . "'";
       }
       if(!empty($badFormat)){
           $msgBadFormat = "Wrong format for input fields '" . implode("', '", $badFormat) . "'";
       }
       $description = $msgRequired? $msgRequired : $msgBadFormat;
       $message = "Bad Request";
       $message .= $description?": $description":"";
       throw new \Exception($message, 400);
   }

   /**
    * Send response without a result but with a description
    * @param type $message
    * @param type $code
    * @return type
    */
   static public function error ($message = '', $code = 0) {
       if (!$message)
           return self::_response([], 500);
       switch ($code) {
                case 0:
                    return self::_response(["feedback"=>["error" => "Internal Server Error: '$message'"]], 500);                    
                case 406:
                    return self::_response(["feedback"=>["warning" => $message]], $code);
                default:
                    return self::_response(["feedback"=>["error" => "Internal Server Error: '$message'"]], 500);
       }
   }

   static public function serviceUnavaiable ($service) {
       $message = $service?"Service '$service' not implemented":"Service not implemented";
       return self::_response(["feedback"=>["error"=>$message]], 503);
   }

   static protected function documentation () {
	return ['documentation'=>self::$_config];
   }
   
   abstract function defaultResponce();
}
?>
