<?php 
    /**
     * 
     */
    class Api extends Rest{
        
        public function __construct(){
            parent::__construct();
        }

        public function generateToken(){
            // print_r($this->parameter);
            $email = $this->validateParameter('email', $this->parameter['email'], STRING);
            $pass = $this->validateParameter('pass', $this->parameter['pass'], STRING);
            echo $pass;
        }
    }
 ?>