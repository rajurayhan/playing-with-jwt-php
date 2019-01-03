<?php 
    
    require_once('constant.php');
    class Rest {
        protected $request;
        protected $serviceName; 
        protected $parameter;
        public function __construct(){
            if($_SERVER['REQUEST_METHOD'] !== 'POST'){
                $this->throwError(METHOD_NOT_ALLOWED, 'Method Not Allowed.');
                exit;
            }
            $handler = fopen('php://input', 'r');
            $this->request = stream_get_contents($handler);
            $this->validateRequest();
            //echo $request;
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
                    # code...
                    break;
            }

            return $value;
        }

        public function throwError($code, $message){
            header("content-type: application/json");
            $errorMessage = json_encode(['error' => ['status' => $code, 'message' => $message]]);
            echo $errorMessage;
        }

        public function returnResponse(){
            
        }
    }
?>