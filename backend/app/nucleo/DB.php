<?php
// backend/app/nucleo/DB.php

class DB {
    private $host = "localhost";
    private $db_name = "inventario_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function conectar() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            die("Error de conexión a la base de datos.");
        }
        
        return $this->conn;
    }
}
