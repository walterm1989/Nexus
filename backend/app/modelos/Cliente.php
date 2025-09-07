<?php
// backend/app/modelos/Cliente.php

require_once __DIR__ . '/../nucleo/BaseDeDatos.php';

class Cliente {
    private $db;
    public $id;
    public $nombre;
    public $correo;
    public $telefono;
    public $direccion;

    public function __construct() {
        $this->db = BaseDeDatos::obtenerInstancia()->obtenerConexion();
    }

    // Obtener todos los clientes
    public function obtenerTodos() {
        try {
            // Lista blanca para ordenar. Se recomienda siempre validar los campos de ordenación.
            $columnas_permitidas = ['id', 'nombre', 'correo', 'telefono'];
            $ordenar_por = $_GET['ordenar_por'] ?? 'id';
            if (!in_array($ordenar_por, $columnas_permitidas)) {
                $ordenar_por = 'id';
            }

            $sql = "SELECT * FROM clientes ORDER BY {$ordenar_por}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // Obtener un solo cliente por su ID
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM clientes WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // Crear un nuevo cliente
    public function crear($data) {
        try {
            $sql = "INSERT INTO clientes (nombre, correo, telefono, direccion) VALUES (:nombre, :correo, :telefono, :direccion)";
            $stmt = $this->db->prepare($sql);
            
            // Sanitizar los datos para evitar XSS
            $nombre = htmlspecialchars(strip_tags($data['nombre']));
            $correo = htmlspecialchars(strip_tags($data['correo']));
            $telefono = htmlspecialchars(strip_tags($data['telefono']));
            $direccion = htmlspecialchars(strip_tags($data['direccion']));

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':direccion', $direccion);

            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al crear cliente: " . $e->getMessage());
        }
    }

    // Actualizar un cliente
    public function actualizar($id, $data) {
        try {
            $sql = "UPDATE clientes SET nombre = :nombre, correo = :correo, telefono = :telefono, direccion = :direccion WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            // Sanitizar los datos
            $nombre = htmlspecialchars(strip_tags($data['nombre']));
            $correo = htmlspecialchars(strip_tags($data['correo']));
            $telefono = htmlspecialchars(strip_tags($data['telefono']));
            $direccion = htmlspecialchars(strip_tags($data['direccion']));

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':direccion', $direccion);

            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar cliente: " . $e->getMessage());
        }
    }

    // Eliminar un cliente
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM clientes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar cliente: " . $e->getMessage());
        }
    }
}
