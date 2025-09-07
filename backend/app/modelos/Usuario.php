<?php
// backend/app/modelos/Usuario.php

class Usuario {
    private $conn;
    private $tabla = "usuarios";

    // Propiedades del objeto
    public $id;
    public $nombre;
    public $correo;
    public $clave;
    public $rol_id;
    public $creado_en;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos($params) {
        $query = "SELECT u.id, u.nombre, u.correo, u.rol_id, r.nombre_rol FROM " . $this->tabla . " u JOIN roles r ON u.rol_id = r.id ";

        $condiciones = [];
        $parametros = [];

        // Filtro por nombre o correo
        if (!empty($params['q'])) {
            $condiciones[] = "u.nombre LIKE ? OR u.correo LIKE ?";
            $parametros[] = "%" . $params['q'] . "%";
            $parametros[] = "%" . $params['q'] . "%";
        }

        if (count($condiciones) > 0) {
            $query .= " WHERE " . implode(" AND ", $condiciones);
        }

        // Ordenamiento
        $ordenar_por = $params['ordenar_por'] ?? 'u.id';
        $orden = (isset($params['orden']) && strtoupper($params['orden']) === 'DESC') ? 'DESC' : 'ASC';

        // Validar columna de ordenamiento para evitar inyección SQL
        $columnas_validas = ['id', 'nombre', 'correo', 'rol_id'];
        if (in_array($ordenar_por, $columnas_validas)) {
            $query .= " ORDER BY $ordenar_por $orden";
        } else {
            $query .= " ORDER BY u.id ASC";
        }

        // Paginación
        $limite = (int)($params['limite'] ?? 10);
        $offset = (int)($params['offset'] ?? 0);
        $query .= " LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);

        // Se enlazan los parámetros para prevenir inyección SQL
        $param_index = 1;
        foreach ($parametros as $param) {
            $stmt->bindValue($param_index, $param, PDO::PARAM_STR);
            $param_index++;
        }
        
        $stmt->bindValue($param_index++, $limite, PDO::PARAM_INT);
        $stmt->bindValue($param_index++, $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function crear() {
        $query = "INSERT INTO " . $this->tabla . " (nombre, correo, clave, rol_id) VALUES (:nombre, :correo, :clave, :rol_id)";
        
        $this->clave = password_hash($this->clave, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":clave", $this->clave);
        $stmt->bindParam(":rol_id", $this->rol_id);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    public function buscarPorCorreo($correo) {
        $query = "SELECT id, nombre, correo, clave, rol_id FROM " . $this->tabla . " WHERE correo = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $correo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
