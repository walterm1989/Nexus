<?php

class Categoria {
    private $conn;
    private $tabla = "categorias";

    public $id;
    public $nombre;
    public $descripcion;
    public $fecha_creacion;
    public $fecha_actualizacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos($params) {
        $query = "SELECT id, nombre, descripcion, fecha_creacion, fecha_actualizacion FROM " . $this->tabla;

        // Búsqueda y filtrado
        $filtros = [];
        $condiciones = [];

        if (isset($params['buscar']) && !empty($params['buscar'])) {
            $condiciones[] = "(nombre LIKE :buscar OR descripcion LIKE :buscar)";
            $filtros[':buscar'] = '%' . $params['buscar'] . '%';
        }

        if (isset($params['nombre']) && !empty($params['nombre'])) {
            $condiciones[] = "nombre LIKE :nombre";
            $filtros[':nombre'] = '%' . $params['nombre'] . '%';
        }

        if (!empty($condiciones)) {
            $query .= " WHERE " . implode(" AND ", $condiciones);
        }

        // Ordenamiento
        $ordenar_por = $params['ordenar_por'] ?? 'id';
        $orden_dir = ($params['orden_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $columnas_permitidas = ['id', 'nombre', 'fecha_creacion', 'fecha_actualizacion'];
        if (!in_array($ordenar_por, $columnas_permitidas)) {
            $ordenar_por = 'id';
        }

        $query .= " ORDER BY $ordenar_por $orden_dir";

        // Paginación
        $pagina = isset($params['pagina']) && is_numeric($params['pagina']) ? (int)$params['pagina'] : 1;
        $limite = isset($params['limite']) && is_numeric($params['limite']) ? (int)$params['limite'] : 10;
        $offset = ($pagina - 1) * $limite;

        $query .= " LIMIT :limite OFFSET :offset";

        try {
            $stmt = $this->conn->prepare($query);
            foreach ($filtros as $key => &$value) {
                $stmt->bindParam($key, $value);
            }
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categorias: " . $e->getMessage());
        }
    }

    public function obtenerPorId() {
        $query = "SELECT id, nombre, descripcion, fecha_creacion, fecha_actualizacion FROM " . $this->tabla . " WHERE id = ? LIMIT 0,1";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($fila) {
                return $fila;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categoria por ID: " . $e->getMessage());
        }
    }

    public function crear() {
        $query = "INSERT INTO " . $this->tabla . " (nombre, descripcion) VALUES (:nombre, :descripcion)";

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':descripcion', $this->descripcion);

            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al crear categoria: " . $e->getMessage());
        }
    }

    public function actualizar() {
        $query = "UPDATE " . $this->tabla . " SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->id = htmlspecialchars(strip_tags($this->id));

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':id', $this->id);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar categoria: " . $e->getMessage());
        }
    }

    public function eliminar() {
        $query = "DELETE FROM " . $this->tabla . " WHERE id = :id";
        $this->id = htmlspecialchars(strip_tags($this->id));

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar categoria: " . $e->getMessage());
        }
    }
}

