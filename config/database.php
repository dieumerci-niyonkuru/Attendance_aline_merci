<?php
// config/database.php - Database connection

class Database {
    private $host = "localhost";
    private $db_name = "attendance_system";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $exception) {
            // Don't show errors to users in production
            if (defined('DEBUG') && DEBUG) {
                echo "<div style='background:#f8d7da; color:#721c24; padding:20px; margin:20px; border-radius:5px;'>";
                echo "<h3>Database Connection Error</h3>";
                echo "<p>Error: " . $exception->getMessage() . "</p>";
                echo "<p><strong>Solution:</strong> Make sure:</p>";
                echo "<ol>";
                echo "<li>MySQL is running</li>";
                echo "<li>Database '{$this->db_name}' exists</li>";
                echo "<li>Username: '{$this->username}'</li>";
                echo "<li>For XAMPP: password is empty</li>";
                echo "</ol>";
                echo "</div>";
            }
            die();
        }
        
        return $this->conn;
    }
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Define DEBUG mode (set to false in production)
define('DEBUG', true);
?>