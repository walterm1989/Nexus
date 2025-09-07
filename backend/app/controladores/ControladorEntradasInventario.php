<?php
require_once __DIR__ . '/../nucleo/DB.php';
require_once __DIR__ . '/../modelos/EntradaInventario.php';

class ControladorEntradasInventario {
    private $db;
    private $entrada_inventario;

    public function __construct() {
        $this->db = new DB();
        $this->entrada_inventario = new EntradaInventario($this->db->conectar());
    }

    public function obtenerTodas($params) {
        try {
            $entradas = $this->entrada_inventario->obtenerTodas($params);
            echo json_encode(['estado' => 'exito', 'data' => $entradas]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $this->entrada_inventario->producto_id = $data['producto_id'] ?? 0;
        $this->entrada_inventario->cantidad = $data['cantidad'] ?? 0;

        if (empty($this->entrada_inventario->producto_id) || empty($this->entrada_inventario->cantidad)) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID de producto y cantidad son requeridos']);
            return;
        }

        try {
            if ($this->entrada_inventario->crear()) {
                http_response_code(201);
                echo json_encode(['estado' => 'exito', 'mensaje' => 'Entrada de inventario creada']);
            } else {
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'mensaje' => 'No se pudo crear la entrada de inventario']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }
}
