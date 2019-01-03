<?php 
    class DbConnect{
        private $host = 'localhost';
        private $dbName = 'jwt-api';
        private $user   = 'root';
        private $pass   = '';
        
        public function connect(){
            try {
                $conn = new PDO('mysql:host=' .$this->host .'; dbname=' .$this->dbName, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //echo "Connected!";
            return $conn;
            } catch (\Exception $e) {
                echo "Database Error: " .$e->getMessage();
            }
        }
    }
    // $db = new DbConnect;
    // $db->connect();
 ?>