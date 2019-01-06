<?php 
    use \Firebase\JWT\JWT;

    class Api extends Rest{
        //public $dbConnect;
        public function __construct(){
            parent::__construct();
            //$db     = new DbConnect;
            //$this->dbConnect = $db->connect();
        }

        public function generateToken(){
            $email = $this->validateParameter('email', $this->parameter['email'], STRING);
            $pass = $this->validateParameter('pass', $this->parameter['pass'], STRING);

            try {
                $stmt = $this->dbConnect->prepare("SELECT * FROM users WHERE email = :email AND password = :pass");
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":pass", $pass);
                $stmt->execute();

                $user = $stmt->fetch(PDO::FETCH_OBJ);
                if(!is_object($user)){
                    $this->returnResponse(INVALID_USER_PASS, "Invalid Email or Password.");
                }
                if(!$user->active){
                    $this->returnResponse(USER_NOT_ACTIVE, "User is not active. Please contact with Admin");
                }
                $payLoad = [
                    'iat'       => time(),
                    'iss'       => 'localhost',
                    'exp'       => time() + (15*60),
                    'userID'    => $user->id,
                ];
                $token   = JWT::encode($payLoad, SECRET);
                $data = ['token' => $token];
                $this->returnResponse(SUCCESS_RESPONSE, $data);
            } catch (Exception $e) {
                $this->throwError(JWT_PROCESS_ERROR, $e->getMessage());
            }

        }

        public function addCustomer(){
            $email      = $this->validateParameter('email', $this->parameter['email'], STRING, false);
            $name       = $this->validateParameter('name', $this->parameter['name'], STRING, false);
            $address    = $this->validateParameter('address', $this->parameter['address'], STRING, false);
            $mobile     = $this->validateParameter('mobile', $this->parameter['mobile'], STRING, false);

            $cust = new Customer();

            $cust->setName($name);
            $cust->setMobile($mobile);
            $cust->setAddress($address);
            $cust->setEmail($email);

            $cust->setCreatedBy($this->userID);
            $cust->setCreatedAt(date('Y-m-d'));

            if(!$cust->insert()){
                $message = 'Opps! Something Went Wrong!';
            }
            else{
                $message = "Insertion Success";
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }

        public function updateCustomer(){
            $customerID      = $this->validateParameter('customerID', $this->parameter['customerID'], INTEGER);
            $name       = $this->validateParameter('name', $this->parameter['name'], STRING, false);
            $address    = $this->validateParameter('address', $this->parameter['address'], STRING, false);
            $mobile     = $this->validateParameter('mobile', $this->parameter['mobile'], STRING, false);

            $cust = new Customer();

            $cust->setName($name);
            $cust->setMobile($mobile);
            $cust->setAddress($address);
            $cust->setId($customerID);

            $cust->setUpdatedBy($this->userID);
            $cust->setUpdatedAt(date('Y-m-d'));

            if(!$cust->update()){
                $message = 'Opps! Something Went Wrong!';
            }
            else{
                $message = "Update Success";
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }

        public function deleteCustomer(){
            $customerID      = $this->validateParameter('customerID', $this->parameter['customerID'], INTEGER);

            $cust = new Customer();

            $cust->setId($customerID);


            if(!$cust->delete()){
                $message = 'Opps! Something Went Wrong!';
            }
            else{
                $message = "Deleted Success";
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }

        public function getCustomerDetails(){
            $customerId = $this->validateParameter('customerID', $this->parameter['customerID'], INTEGER);
            $cust = new Customer;
            $cust->setId($customerId);
            $customer = $cust->getCustomerDetailsById();
            if(!is_object($customer)) {
                $this->returnResponse(SUCCESS_RESPONSE, ['message' => 'Customer details not found.']);
            }
            $response['customerId']     = $customer->id;
            $response['cutomerName']    = $customer->name;
            $response['email']          = $customer->email;
            $response['mobile']         = $customer->mobile;
            $response['address']        = $customer->address;
            $response['createdBy']      = $customer->created_user;
            $response['lastUpdatedBy']  = $customer->updated_user;
            $this->returnResponse(SUCCESS_RESPONSE, $response);
        }
    }
 ?>