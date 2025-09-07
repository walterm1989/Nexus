<?php
require_once __DIR__ . '/../nucleo/DB.php';
require_once __DIR__ . '/../modelos/Producto.php';

class ControladorProductos {
    private $db;
    private $producto;

    public function __construct() {
        $this->db = new DB();
        $this->producto = new Producto($this->db->conectar());
    }

    public function obtenerTodos($params) {
        try {
            $productos = $this->producto->obtenerTodos($params);
            echo json_encode(['estado' => 'exito', 'data' => $productos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    public function obtenerUno($params) {
        $this->producto->id = $params['id'] ?? null;
        if (!$this->producto->id) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID no proporcionado']);
            return;
        }

        try {
            $producto_encontrado = $this->producto->obtenerPorId();
            if ($producto_encontrado) {
                echo json_encode(['estado' => 'exito', 'data' => $producto_encontrado]);
            } else {
                http_response_code(404);
                echo json_encode(['estado' => 'error', 'mensaje' => 'Producto no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    public function obtenerProductosBajoStock() {
        try {
            $productos = $this->producto->obtenerProductosBajoStock();
            echo json_encode(['estado' => 'exito', 'data' => $productos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $this->producto->nombre = $data['nombre'] ?? '';
        $this->producto->descripcion = $data['descripcion'] ?? '';
        $this->producto->precio = $data['precio'] ?? 0;
        $this->producto->stock = $data['stock'] ?? 0;
        $this->producto->categoria_id = $data['categoria_id'] ?? 0;
        $this->producto->imagen_url = $data['imagen_url'] ?? '';

        if (empty($this->producto->nombre) || empty($this->producto->categoria_id)) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Nombre y categoria_id son requeridos']);
            return;
        }

        try {
            if ($this->producto->crear()) {
                http_response_code(201);
                echo json_encode(['estado' => 'exito', 'mensaje' => 'Producto creado', 'id' => $this->producto->id]);
            } else {
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'mensaje' => 'No se pudo crear el producto']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    public function actualizar($params) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $this->producto->id = $params['id'] ?? null;
        if (!$this->producto->id) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID no proporcionado']);
            return;
        }

        $this->producto->nombre = $data['nombre'] ?? '';
        $this->producto->descripcion = $data['descripcion'] ?? '';
        $this->producto->precio = $data['precio'] ?? 0;
        $this->producto->stock = $data['stock'] ?? 0;
        $this->producto->categoria_id = $data['categoria_id'] ?? 0;
        $this->producto->imagen_url = $data['imagen_url'] ?? '';

        try {
            if ($this->producto->actualizar()) {
                echo json_encode(['estado' => 'exito', 'mensaje' => 'Producto actualizado']);
            } else {
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'mensaje' => 'No se pudo actualizar el producto']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    public function eliminar($params) {
        $this->producto->id = $params['id'] ?? null;
        if (!$this->producto->id) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID no proporcionado']);
            return;
        }

        try {
            if ($this->producto->eliminar()) {
                echo json_encode(['estado' => 'exito', 'mensaje' => 'Producto eliminado']);
            } else {
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'mensaje' => 'No se pudo eliminar el producto']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }
}
