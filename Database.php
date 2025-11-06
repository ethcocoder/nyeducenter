<?php  
require_once "config/database.php";

if (!class_exists('Database')) {
class Database {
	private $conn;
	
	public function getConnection() {
		$this->conn = null;

		try {
			$this->conn = new PDO(
				"mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
				DB_USER,
				DB_PASS,
				DB_OPTIONS
			);
			
			// Test the connection
			$this->conn->query('SELECT 1');
			
			return $this->conn;
		} catch(PDOException $e) {
			error_log("Database Connection Error: " . $e->getMessage());
			throw new Exception("Database connection failed. Please check your configuration.");
		}
	}

	public function testConnection() {
		try {
			$conn = $this->getConnection();
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}
}