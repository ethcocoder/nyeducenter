<?php 

class Admin{
   protected $conn;
   protected $table_name = "admins";
   protected $admin_id;
   protected $username;
   protected $password;
   protected $email;
   protected $first_name;
   protected $last_name;
   protected $created_at;

   function __construct($db_conn){
     $this->conn = $db_conn;
   }

   // Getters
   public function getAdminId() {
       return $this->admin_id;
   }

   public function getUsername() {
       return $this->username;
   }

   public function getEmail() {
       return $this->email;
   }

   public function getFirstName() {
       return $this->first_name;
   }

   public function getLastName() {
       return $this->last_name;
   }

   public function getCreatedAt() {
       return $this->created_at;
   }

   // Setters
   public function setAdminId($id) {
       $this->admin_id = $id;
   }

   public function setUsername($username) {
       $this->username = $username;
   }

   public function setPassword($password) {
       $this->password = password_hash($password, PASSWORD_ARGON2ID, [
           'memory_cost' => 65536,
           'time_cost' => 4,
           'threads' => 3
       ]);
   }

   public function setEmail($email) {
       $this->email = $email;
   }

   public function setFirstName($first_name) {
       $this->first_name = $first_name;
   }

   public function setLastName($last_name) {
       $this->last_name = $last_name;
   }

   function init($admin_id){
       try {
          $sql = 'SELECT * FROM '. $this->table_name.' WHERE admin_id=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$admin_id]);
          if($stmt->rowCount() == 1) 
            {
                $user = $stmt->fetch();
                $this->username =  $user['username'];
                $this->admin_id =  $user['admin_id'];
                $this->email =  $user['email'];
                $this->first_name =  $user['first_name'];
                $this->last_name =  $user['last_name'];
                $this->created_at =  $user['created_at'];
                return 1;
            }
          else return 0;
       }catch(PDOException $e){
          return 0;
       }
   }
   

   function authenticate($input_username, $input_password){
       try {
          $sql = 'SELECT * FROM '. $this->table_name.' WHERE username=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$input_username]);
          if($stmt->rowCount() == 1) {
            $admin = $stmt->fetch();
            $_username = $admin["username"];
            $_password = $admin["password"];


            if($_username === $input_username ){
               if (password_verify($input_password, $_password)) {
                  $this->username =  $_username;
                  $this->admin_id =  $admin["admin_id"];
                  $this->email =  $admin["email"];
                  $this->first_name =  $admin["first_name"];
                  $this->last_name =  $admin["last_name"];
                  $this->created_at =  $admin["created_at"];
                  return 1;
               }else return 0;
            }else return 0;
          }else return 0;
       }catch(PDOException $e){
           return 0;
       }
   }

   function authenticateByEmail($input_email, $input_password){
       try {
          $sql = 'SELECT * FROM '. $this->table_name.' WHERE email=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$input_email]);
          if($stmt->rowCount() == 1) {
            $admin = $stmt->fetch();
            $_email = $admin["email"];
            $_password = $admin["password"];

            if($_email === $input_email ){
               if (password_verify($input_password, $_password)) {
                  $this->username =  $admin["username"];
                  $this->admin_id =  $admin["admin_id"];
                  $this->email =  $admin["email"];
                  $this->first_name =  $admin["first_name"];
                  $this->last_name =  $admin["last_name"];
                  $this->created_at =  $admin["created_at"];
                  return 1;
               }else return 0;
            }else return 0;
          }else return 0;
       }catch(PDOException $e){
           return 0;
       }
   }

   function get(){
      $data = array('admin_id' => $this->admin_id,
                    'username' => $this->username,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'created_at' => $this->created_at);
      return $data;
   }

   public function getAdminByUsername($username) {
       try {
           $sql = 'SELECT * FROM ' . $this->table_name . ' WHERE username = ?';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$username]);
           return $stmt->fetch();
       } catch(PDOException $e) {
           error_log("Admin getAdminByUsername error: " . $e->getMessage());
           throw new Exception("Failed to retrieve admin: " . $e->getMessage());
       }
   }

   public function getAdminById($id) {
       try {
           $sql = 'SELECT * FROM ' . $this->table_name . ' WHERE admin_id = ?';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$id]);
           $result = $stmt->fetch();
           if (!$result) {
               throw new Exception("Admin not found");
           }
           return $result;
       } catch(PDOException $e) {
           error_log("Admin getAdminById error: " . $e->getMessage());
           throw new Exception("Failed to retrieve admin: " . $e->getMessage());
       }
   }

   public function updateProfile($admin_id, $data) {
       try {
           $sql = 'UPDATE ' . $this->table_name . ' 
                   SET first_name = ?, last_name = ?, email = ? 
                   WHERE admin_id = ?';
           $stmt = $this->conn->prepare($sql);
           return $stmt->execute([
               $data['first_name'],
               $data['last_name'],
               $data['email'],
               $admin_id
           ]);
       } catch(PDOException $e) {
           error_log("Admin updateProfile error: " . $e->getMessage());
           throw new Exception("Failed to update admin profile: " . $e->getMessage());
       }
   }

   public function changePassword($admin_id, $current_password, $new_password) {
       try {
           // Get current password hash
           $sql = 'SELECT password FROM ' . $this->table_name . ' WHERE admin_id = ?';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$admin_id]);
           $result = $stmt->fetch();

           if (!$result || !password_verify($current_password, $result['password'])) {
               throw new Exception("Current password is incorrect");
           }

           // Update password
           $sql = 'UPDATE ' . $this->table_name . ' 
                   SET password = ? 
                   WHERE admin_id = ?';
           $stmt = $this->conn->prepare($sql);
           return $stmt->execute([
               password_hash($new_password, PASSWORD_ARGON2ID, [
                   'memory_cost' => 65536,
                   'time_cost' => 4,
                   'threads' => 3
               ]),
               $admin_id
           ]);
       } catch(PDOException $e) {
           error_log("Admin changePassword error: " . $e->getMessage());
           throw new Exception("Failed to change password: " . $e->getMessage());
       }
   }
}