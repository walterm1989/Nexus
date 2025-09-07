<?php
// backend/app/configuracion/base_de_datos.php

require __DIR__ . '/../../../vendor/autoload.php';

use Dotenv\Dotenv;

class BaseDeDatos {
    private static $instancia = null;
    private $conexion;

    private function __construct() {
        // Cargar las variables de entorno del archivo .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $db_host    = $_ENV['DB_HOST'];
        $db_nombre = $_ENV['DB_NOMBRE'];
        $db_usuario = $_ENV['DB_USUARIO'];
        $db_clave  = $_ENV['DB_CLAVE'];
        
        try {
            $this->conexion = new PDO(
                "mysql:host={$db_host};dbname={$db_nombre};charset=utf8",
                $db_usuario,
                $db_clave
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Manejo de errores de conexión
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Error de conexión: ' . $e->getMessage()]);
            exit;
        }
    }

    public static function obtenerInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new BaseDeDatos();
        }
        return self::$instancia;
    }

    public function obtenerConexion() {
        return $this->conexion;
    }
}
