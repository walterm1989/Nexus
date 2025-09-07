<?php

class EntradaInventario {
    private $conn;
    private $tabla = "entradas_inventario";

    public $id;
    public $producto_id;
    public $cantidad;
    public $fecha;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodas($params) {
        $query = "SELECT e.id, e.producto_id, p.nombre as producto_nombre, e.cantidad, e.fecha FROM " . $this->tabla . " e JOIN productos p ON e.producto_id = p.id";

        $condiciones = [];
        $filtros = [];

        if (isset($params['buscar']) && !empty($params['buscar'])) {
            $condiciones[] = "(p.nombre LIKE :buscar)";
            $filtros[':buscar'] = '%' . $params['buscar'] . '%';
        }

        if (!empty($condiciones)) {
            $query .= " WHERE " . implode(" AND ", $condiciones);
        }

        $ordenar_por = $params['ordenar_por'] ?? 'e.fecha';
        $orden_dir = ($params['orden_dir'] ?? 'DESC') === 'DESC' ? 'DESC' : 'ASC';

        $columnas_permitidas = ['e.id', 'p.nombre', 'e.cantidad', 'e.fecha'];
        if (!in_array($ordenar_por, $columnas_permitidas)) {
            $ordenar_por = 'e.fecha';
        }

        $query .= " ORDER BY $ordenar_por $orden_dir";

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
            throw new Exception("Error al obtener entradas de inventario: " . $e->getMessage());
        }
    }

    public function crear() {
        try {
            // Iniciar la transacción
            $this->conn->beginTransaction();

            // Insertar la nueva entrada de inventario
            $queryEntrada = "INSERT INTO " . $this->tabla . " (producto_id, cantidad) VALUES (:producto_id, :cantidad)";
            $stmtEntrada = $this->conn->prepare($queryEntrada);
            $stmtEntrada->bindParam(':producto_id', $this->producto_id);
            $stmtEntrada->bindParam(':cantidad', $this->cantidad);
            $stmtEntrada->execute();

            // Actualizar el stock del producto
            $queryProducto = "UPDATE productos SET stock = stock + :cantidad WHERE id = :producto_id";
            $stmtProducto = $this->conn->prepare($queryProducto);
            $stmtProducto->bindParam(':cantidad', $this->cantidad);
            $stmtProducto->bindParam(':producto_id', $this->producto_id);
            $stmtProducto->execute();

            // Confirmar la transacción
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            // Revertir la transacción en caso de error
            $this->conn->rollBack();
            throw new Exception("Error al crear entrada de inventario: " . $e->getMessage());
        }
    }
}
