<?php  
    /**
     * 
     */
    class Customer{
        private $id;
        private $name;
        private $email;
        private $mobile;
        private $address;
        private $created_at;
        private $updated_at;

        private $created_by;
        private $updated_by;

        private $tableName = 'customers';
        private $dbConn;

        function setId($id){
            $this->id = $id;
        }
        function getId(){
            return $this->id;
        }

        function setName($name){
            $this->name = $name;
        }
        function getName(){
            return $this->name;
        }

        function setEmail($email){
            $this->email = $email;
        }
        function getEmail(){
            return $this->email;
        }

        function setAddress($address){
            $this->address = $address;
        }
        function getAddress(){
            return $this->address;
        }

        function setMobile($mobile){
            $this->mobile = $mobile;
        }
        function getMobile(){
            return $this->mobile;
        }

        function setCreatedAt($created_at){
            $this->created_at = $created_at;
        }
        function getCreatedAt(){
            return $this->created_at;
        }

        function setUpdatedAt($updated_at){
            $this->updated_at = $updated_at;
        }
        function getUpdatedAt(){
            return $this->updated_at;
        }

        function setCreatedBy($created_by){
            $this->created_by = $created_by;
        }
        function getCreatedBy(){
            return $this->created_by;
        }

        function setUpdatedBy($updated_by){
            $this->updated_by = $updated_by;
        }
        function getUpdatedBy(){
            return $this->updated_by;
        }

        public function __construct(){
            $db             = new DbConnect();
            $this->dbConn   = $db->connect();
        }

        public function getAllCustomers(){
            $stmt = $this->dbConn->prepare("SELECT * FROM" . $this->tableName);
            $stmt->execute();
            $customers  = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $customers;
        }

        public function insert(){
            $sql    = 'INSERT INTO '. $this->tableName . ' (id, name, email, address, mobile, created_by, created_at) VALUES (null, :name, :email, :address, : mobile, :created_by, :created_at)';
            $stmt   = $this->dbConn->prepare($sql);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':mobile', $this->mobile);
            $stmt->bindParam(':created_by', $this->created_by);
            $stmt->bindParam(':created_at', $this->created_at);

            if($stmt->execute()){
                return true;
            }
            else{
                return false;
            }
        }

        public function update(){

            $sql = "UPDATE $this->tableName SET";
            if(null != $this->getName()){
                $sql .= " name = '" . $this->getName() . "',";
            }

            if(null != $this->getAddress()){
                $sql .= " address = '" . $this->getAddress() . "',";
            }

            if(null != $this->getMobile()){
                $sql .= " mobile = '" . $this->getMobile() . "',";
            }

            $sql .= " updated_by = :updated_by, 
                      updated_at = :updated_at
                    WHERE 
                        id = :userID";
            //echo $sql;
            $stmt   = $this->dbConn->prepare($sql);
            $stmt->bindParam(':userID', $this->id);
            $stmt->bindParam(':updated_by', $this->updated_by);
            $stmt->bindParam(':updated_at', $this->updated_at);

            if($stmt->execute()){
                return true;
            }
            else{
                return false;
            }
        }

        public function delete(){
            $stmt = $this->dbConn->prepare('DELETE FROM ' . $this->tableName . ' WHERE id = :userID');
            $stmt->bindParam(':userID', $this->id);
            if($stmt->execute()){
                return true;
            }
            else{
                return false;
            }
        }

    }

    
?>