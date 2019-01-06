<?php 

    use \Firebase\JWT\JWT;
    require_once('constant.php');
    class Rest {
        protected $request;
        protected $serviceName; 
        protected $parameter;
        protected $dbConnect;
        protected $userID;
        public function __construct(){
            $db                 = new DbConnect;
            $this->dbConnect    = $db->connect();

            if($_SERVER['REQUEST_METHOD'] !== 'POST'){
                $this->throwError(METHOD_NOT_ALLOWED, 'Method Not Allowed.');
                exit;
            }
            $handler = fopen('php://input', 'r');
            $this->request = stream_get_contents($handler);
            $this->validateRequest();

            if('generatetoken' != strtolower($this->serviceName)){
                $this->validateToken();
            }

        }

        public function validateToken(){
            try {
                $token   = $this->getBearedToken();
                $payLoad = JWT::decode($token, SECRET, ['HS256']);

                $stmt = $this->dbConnect->prepare("SELECT * FROM users WHERE id = :userID");
                $stmt->bindParam(":userID", $payLoad->userID);
                $stmt->execute();

                $user = $stmt->fetch(PDO::FETCH_OBJ);
                if(!is_object($user)){
                    $this->returnResponse(INVALID_USER_PASS, "User not Found");
                    //exit;
                }
                if(!$user->active){
                    $this->returnResponse(USER_NOT_ACTIVE, "User is not active. Please contact with Admin");
                }

                $this->userID = $payLoad->userID;

            } catch (Exception $e) {
                $this->throwError(ACCESS_TOKEN_ERROR, $e->getMessage());
            }
        }

        public function validateRequest(){
            if($_SERVER["CONTENT_TYPE"] !== 'application/json'){
                $this->throwError(CONTENTTYPE_NOT_ALLOWED, 'Content Type Not Allowed');
                exit;
            }

            $data   = json_decode($this->request, true);
            //print_r($data); exit;
            
            if(!isset($data['name']) || $data['name'] == NULL){
                $this->throwError(API_NAME_REQUIRED, 'API Name Required');
                exit;
            }
            $this->serviceName = $data['name'];

            if(!array_key_exists('param', $data)){
                $this->throwError(API_PARAM_REQUIRED, 'API Parameter Required');
                exit;
            }
            else{
                if(!is_array($data['param'])){
                    $this->throwError(API_PARAM_MUST_BE_ARRY, 'API Parameter Must be an Arry');
                    exit;
                }
                else{
                    $this->parameter = $data['param'];
                }
            }
        }

        public function processAPI(){
            $api = new Api();
            $rMethod = new ReflectionMethod('Api', $this->serviceName);
            if(!method_exists($api, $this->serviceName)){
                $this->throwError(API_DOES_NOT_EXIST, "API Does not Exist");
            }
            $rMethod->invoke($api);
        }

        public function validateParameter($fieldName, $value, $dataType, $required = true){
            if($required == true && empty($value) == true){
                $this->throwError(VALIDATE_PARAMETER_REQUIRED, $fieldName.' is Required.');
            }
            switch ($dataType) {
                case BOOLEAN:
                    if(!is_bool($value)){
                        $this->throwError(VALIDATE_PARAMETER_DATATYPE, 'Data Type should be Boolean for '.$fieldName);
                    }
                    break;

                case INTEGER:
                    if(!is_numeric($value)){
                        $this->throwError(VALIDATE_PARAMETER_DATATYPE, 'Data Type should be Integer for '.$fieldName);
                    }
                    break;
                case STRING:
                    if(!is_string($value)){
                        $this->throwError(VALIDATE_PARAMETER_DATATYPE, 'Data Type should be STRING for '.$fieldName);
                    }
                    break;
                
                default:
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, 'Data Type Not Valid for '.$fieldName);
                    break;
            }

            return $value;
        }

        public function throwError($code, $message){
            header("content-type: application/json");
            $errorMessage = json_encode(['error' => ['status' => $code, 'message' => $message]]);
            echo $errorMessage; 
            exit;
        }

        public function returnResponse($code , $data){
            header("content-type: application/json");
            $response   = json_encode(['response' => ['status' => $code, 'result' => $data]]);
            echo $response; 
            exit;
        }

        public function getAuthorizationHeader(){
            $headers = null;
            if(isset($_SERVER['Authorization'])){
                $headers = trim($_SERVER['Authorization']);
            }
            elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
            }
            elseif (function_exists('apache_request_headers')) {
                $requestHeaders = apache_request_headers();
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

                if(isset($requestHeaders['Authorization'])){
                $headers = trim($requestHeaders['Authorization']);
                }
            }
            return $headers;
        }

        public function getBearedToken(){
            $headers = $this->getAuthorizationHeader();
            if(!empty($headers)){
                if(preg_match('/Bearer\s(\S+)/', $headers, $matches)){
                    return $matches[1];
                }
            }
            $this->throwError(AUTHORIZATION_HEADER_NOT_FOUND, 'Access Token Not Found.');
        }
    }
?>