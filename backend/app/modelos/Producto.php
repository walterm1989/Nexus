<?php

class Producto {
    private $conn;
    private $tabla = "productos";

    public $id;
    public $nombre;
    public $descripcion;
    public $precio;
    public $stock;
    public $categoria_id;
    public $imagen_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos($params) {
        $query = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.imagen_url, c.nombre as categoria_nombre, p.fecha_creacion FROM " . $this->tabla . " p JOIN categorias c ON p.categoria_id = c.id";

        // Búsqueda y filtrado
        $condiciones = [];
        $filtros = [];

        if (isset($params['buscar']) && !empty($params['buscar'])) {
            $condiciones[] = "(p.nombre LIKE :buscar OR p.descripcion LIKE :buscar)";
            $filtros[':buscar'] = '%' . htmlspecialchars(strip_tags($params['buscar'])) . '%';
        }

        if (isset($params['categoria_id']) && !empty($params['categoria_id'])) {
            $condiciones[] = "p.categoria_id = :categoria_id";
            $filtros[':categoria_id'] = $params['categoria_id'];
        }

        if (!empty($condiciones)) {
            $query .= " WHERE " . implode(" AND ", $condiciones);
        }

        // Ordenamiento - VULNERABILIDAD CORREGIDA
        $ordenar_por = $params['ordenar_por'] ?? 'p.id';
        $orden_dir = ($params['orden_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $columnas_permitidas = ['p.id', 'p.nombre', 'p.precio', 'p.stock', 'c.nombre', 'p.fecha_creacion'];
        if (!in_array($ordenar_por, $columnas_permitidas)) {
            $ordenar_por = 'p.id';
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
            throw new Exception("Error al obtener productos: " . $e->getMessage());
        }
    }

    public function obtenerPorId() {
        $query = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.imagen_url, c.nombre as categoria_nombre FROM " . $this->tabla . " p JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ? LIMIT 0,1";

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
            throw new Exception("Error al obtener producto por ID: " . $e->getMessage());
        }
    }

    public function obtenerProductosBajoStock() {
        $query = "SELECT p.id, p.nombre, p.stock FROM " . $this->tabla . " p WHERE p.stock < 10 ORDER BY p.stock ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener productos con bajo stock: " . $e->getMessage());
        }
    }

    public function crear() {
        $query = "INSERT INTO " . $this->tabla . " (nombre, descripcion, precio, stock, categoria_id, imagen_url) VALUES (:nombre, :descripcion, :precio, :stock, :categoria_id, :imagen_url)";

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->categoria_id = htmlspecialchars(strip_tags($this->categoria_id));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':precio', $this->precio);
            $stmt->bindParam(':stock', $this->stock);
            $stmt->bindParam(':categoria_id', $this->categoria_id);
            $stmt->bindParam(':imagen_url', $this->imagen_url);

            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al crear producto: " . $e->getMessage());
        }
    }

    public function actualizar() {
        $query = "UPDATE " . $this->tabla . " SET nombre = :nombre, descripcion = :descripcion, precio = :precio, stock = :stock, categoria_id = :categoria_id, imagen_url = :imagen_url WHERE id = :id";

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->categoria_id = htmlspecialchars(strip_tags($this->categoria_id));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        $this->id = htmlspecialchars(strip_tags($this->id));

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':precio', $this->precio);
            $stmt->bindParam(':stock', $this->stock);
            $stmt->bindParam(':categoria_id', $this->categoria_id);
            $stmt->bindParam(':imagen_url', $this->imagen_url);
            $stmt->bindParam(':id', $this->id);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar producto: " . $e->getMessage());
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
            throw new Exception("Error al eliminar producto: " . $e->getMessage());
        }
    }
}
