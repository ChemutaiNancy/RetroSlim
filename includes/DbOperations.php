<?php

    class DbOperations{
        private $con;

        function __construct(){
            require_once dirname(__FILE__).'/DbConnect.php';

            $db = new DbConnect;
            $this->con = $db->connect();

        }

        public function createUser($email, $password, $name, $school){
           
            
            if (!$this->isEmailExists($email)) {
                $stmt = $this->con->prepare("INSERT INTO users (email, password, name, school) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $email, $password, $name, $school);
                if ($stmt->execute()) {
                    return USER_CREATED;
                } else {
                    return USER_FAILURE;
                }
                
            } else {
                return USER_EXISTS;
            }
            
        }
    
        public function isEmailExists($email){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }

        public function userLogin($email, $password){
            if ($this->isEmailExists($email)){
                $hashed_password = getUserPasswordByEmail($email);
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                } else {
                    return USER_PASSWORD_DO_NOT_MATCH;
                }
            } else {
               return USER_NOT_FOUND;
            }
        }

        private function getUserPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($password);
            $stmt->fetch();
            return $password;
        }

        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id, email, name, school FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($id, $email, $name, $school);
            $stmt->fetch();
            $user = array();
            $user['id'] = $id;
            $user['email'] = $email;
            $user['name'] = $name;
            $user['school'] = $school;
            return $user;
        }
    }