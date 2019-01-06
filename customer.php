<?php 
    class Customer {
        private $id;
        private $name;
        private $email;
        private $address;
        private $mobile;
        private $updated_by;
        private $updated_at;
        private $created_by;
        private $created_at;
        private $tableName = 'customers';
        private $dbConn;

        function setId($id) { $this->id = $id; }
        function getId() { return $this->id; }
        function setName($name) { $this->name = $name; }
        function getName() { return $this->name; }
        function setEmail($email) { $this->email = $email; }
        function getEmail() { return $this->email; }
        function setAddress($address) { $this->address = $address; }
        function getAddress() { return $this->address; }
        function setMobile($mobile) { $this->mobile = $mobile; }
        function getMobile() { return $this->mobile; }
        function setUpdatedBy($updated_by) { $this->updated_by = $updated_by; }
        function getUpdatedBy() { return $this->updated_by; }
        function setUpdatedOn($updated_at) { $this->updated_at = $updated_at; }
        function getUpdatedOn() { return $this->updated_at; }
        function setCreatedBy($created_by) { $this->created_by = $created_by; }
        function getCreatedBy() { return $this->created_by; }
        function setCreatedAt($created_at) { $this->created_at = $created_at; }
        function getCreatedAt() { return $this->created_at; }

        public function __construct() {
            $db = new DbConnect();
            $this->dbConn = $db->connect();
        }

        public function getAllCustomers() {
            $stmt = $this->dbConn->prepare("SELECT * FROM " . $this->tableName);
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $customers;
        }

        public function getCustomerDetailsById() {

            $sql = "SELECT 
                        c.*, 
                        u.name as created_user,
                        u1.name as updated_user
                    FROM customers c 
                        JOIN users u ON (c.created_by = u.id) 
                        LEFT JOIN users u1 ON (c.updated_by = u1.id) 
                    WHERE 
                        c.id = :customerId";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindParam(':customerId', $this->id);
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            return $customer;
        }
        

        public function insert() {
            
            $sql = 'INSERT INTO ' . $this->tableName . '(id, name, email, address, mobile, created_by, created_at) VALUES(null, :name, :email, :address, :mobile, :created_by, :created_at)';

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':mobile', $this->mobile);
            $stmt->bindParam(':created_by', $this->created_by);
            $stmt->bindParam(':created_at', $this->created_at);
            
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function update() {
            
            $sql = "UPDATE $this->tableName SET";
            if( null != $this->getName()) {
                $sql .= " name = '" . $this->getName() . "',";
            }

            if( null != $this->getAddress()) {
                $sql .= " address = '" . $this->getAddress() . "',";
            }

            if( null != $this->getMobile()) {
                $sql .= " mobile = " . $this->getMobile() . ",";
            }

            $sql .= " updated_by = :updated_by, 
                      updated_at = :updated_at
                    WHERE 
                        id = :userId";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindParam(':userId', $this->id);
            $stmt->bindParam(':updated_by', $this->updated_by);
            $stmt->bindParam(':updated_at', $this->updated_at);
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function delete() {
            $stmt = $this->dbConn->prepare('DELETE FROM ' . $this->tableName . ' WHERE id = :userId');
            $stmt->bindParam(':userId', $this->id);
            
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }
    }
 ?>