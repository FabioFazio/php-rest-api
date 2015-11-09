<?php
/**
 *	this class will act as a wrapper for all of the custom endpoints that our API will be using.
 *	To that extent, it must be able to take in our request, grab the endpoint from the URI string,
 *	detect the HTTP method (GET, POST, PUT, DELETE) and assemble any additional data provided in the header or in the URI.
 * 	Once that's done, the abstract class will pass the request information on to a method in the concrete class to actually perform the work.
 *	We then return to the abstract class which will handle forming a HTTP response back to the client.
 */
namespace API\Core;

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

    static protected $_documentation = Null;

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
        case 'DELETE':
        case 'POST':
            $this->request = $this->_cleanInputs($_POST);
            break;
        case 'GET':
            $this->request = $this->_cleanInputs($_GET);
            break;
        case 'PUT':
            $this->request = $this->_cleanInputs($_GET);
            $this->file = file_get_contents("php://input");
            break;
        default:
            $this->_response('Invalid Method', 405);
            break;
        }
    }

    /**
     * Evaluate input calls detecting method to use for them.
     * When no methods are available return a 404
     */
    public function processAPI() {
        if (method_exists($this, $this->endpoint)) {
            return $this->_response($this->{$this->endpoint}($this->args));
        }
	if ($this->endpoint) {
	    return $this->_response(["error"=>"No Endpoint: $this->endpoint"], 404);
	} else {
	    return $this->documentation();
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
            400 => 'Bad Request',   
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
	    503 => 'Service unavailable'
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
           $msgBadFormat = "Wrong format for input fields '" . implode("', '", $required) . "'";
       }
       $description = $msgRequired? $msgRequired : $msgBadFormat;
       $message = "Bad Request";
       $message .= $description?": $description":"";
       throw new \Exception($message, 400);
   }

   /**
    * Send an error response
    * @param type $message
    * @param type $code
    * @return type
    */
   static public function error ($message, $code = 0) {
       if ($code)
           return self::_response(["error"=>$message], $code);
       else
           return self::_response(["error"=>"Internal Server Error: '$message'"], 500);
   }

   static public function serviceUnavaiable ($service) {
       $message = $service?"Service '$service' not implemented":"Service not implemented";
       return self::_response(["error"=>$message], 503);
   }

   static public function documentation () {
	return self::_response(self::$_documentation);
   }
   
   abstract function defaultResponce();
}
?>
