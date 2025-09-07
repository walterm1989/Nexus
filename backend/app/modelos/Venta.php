<?php
// backend/app/modelos/Venta.php

require_once 'Producto.php';
require_once 'Cliente.php';

class Venta {
    private $conn;
    private $tabla = "ventas";
    private $tabla_items = "venta_items";
    private $tabla_productos = "productos";

    // Propiedades
    public $id;
    public $cliente_id;
    public $total;
    public $fecha_venta;
    public $items;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodas($params) {
        $query = "SELECT v.id, v.total, v.fecha_venta, c.nombre AS cliente_nombre FROM " . $this->tabla . " v JOIN clientes c ON v.cliente_id = c.id";

        $condiciones = [];
        $parametros = [];

        if (!empty($params['q'])) {
            $condiciones[] = "c.nombre LIKE ?";
            $parametros[] = "%" . $params['q'] . "%";
        }

        if (count($condiciones) > 0) {
            $query .= " WHERE " . implode(" AND ", $condiciones);
        }

        $ordenar_por = $params['ordenar_por'] ?? 'v.id';
        $orden = (isset($params['orden']) && strtoupper($params['orden']) === 'DESC') ? 'DESC' : 'ASC';

        $columnas_validas = ['id', 'total', 'fecha_venta'];
        if (in_array($ordenar_por, $columnas_validas)) {
            $query .= " ORDER BY $ordenar_por $orden";
        } else {
            $query .= " ORDER BY v.id DESC";
        }

        $limite = (int)($params['limite'] ?? 10);
        $offset = (int)($params['offset'] ?? 0);
        $query .= " LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);

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
    
    public function crearVenta() {
        $this->conn->beginTransaction();
        
        try {
            // 1. Insertar en la tabla de ventas
            $query_venta = "INSERT INTO " . $this->tabla . " SET cliente_id = :cliente_id, total = :total, fecha_venta = NOW()";
            $stmt_venta = $this->conn->prepare($query_venta);
            
            $this->cliente_id = htmlspecialchars(strip_tags($this->cliente_id));
            $this->total = htmlspecialchars(strip_tags($this->total));
            
            $stmt_venta->bindParam(':cliente_id', $this->cliente_id);
            $stmt_venta->bindParam(':total', $this->total);
            
            if (!$stmt_venta->execute()) {
                throw new Exception("Error al crear la venta.");
            }
            
            $this->id = $this->conn->lastInsertId();
            
            // 2. Insertar los ítems de la venta y actualizar el stock de productos
            $producto_modelo = new Producto($this->conn);
            $query_items = "INSERT INTO " . $this->tabla_items . " SET venta_id = :venta_id, producto_id = :producto_id, cantidad = :cantidad, precio = :precio";
            $stmt_items = $this->conn->prepare($query_items);
            
            foreach ($this->items as $item) {
                // Verificar que el producto existe y tiene stock suficiente
                $producto_modelo->id = $item['producto_id'];
                $producto_existente = $producto_modelo->obtenerPorId();
                if (!$producto_existente || $producto_existente['stock'] < $item['cantidad']) {
                    throw new Exception("Producto con ID " . $item['producto_id'] . " no tiene suficiente stock.");
                }

                // Insertar ítem de la venta
                $stmt_items->bindParam(':venta_id', $this->id);
                $stmt_items->bindParam(':producto_id', $item['producto_id']);
                $stmt_items->bindParam(':cantidad', $item['cantidad']);
                $stmt_items->bindParam(':precio', $item['precio']);
                
                if (!$stmt_items->execute()) {
                    throw new Exception("Error al insertar el ítem de la venta.");
                }

                // Actualizar el stock del producto
                $query_update_stock = "UPDATE " . $this->tabla_productos . " SET stock = stock - ? WHERE id = ?";
                $stmt_update_stock = $this->conn->prepare($query_update_stock);
                $stmt_update_stock->bindParam(1, $item['cantidad']);
                $stmt_update_stock->bindParam(2, $item['producto_id']);
                
                if (!$stmt_update_stock->execute()) {
                    throw new Exception("Error al actualizar el stock del producto.");
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e; // Re-lanza la excepción para que el controlador la maneje
        }
    }

    public function eliminar() {
        $this->conn->beginTransaction();

        try {
            // 1. Obtener los ítems de la venta para revertir el stock
            $query_items = "SELECT producto_id, cantidad FROM " . $this->tabla_items . " WHERE venta_id = ?";
            $stmt_items = $this->conn->prepare($query_items);
            $stmt_items->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt_items->execute();
            $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            // 2. Revertir el stock de cada producto
            $query_update_stock = "UPDATE " . $this->tabla_productos . " SET stock = stock + ? WHERE id = ?";
            $stmt_update_stock = $this->conn->prepare($query_update_stock);

            foreach ($items as $item) {
                $stmt_update_stock->bindParam(1, $item['cantidad'], PDO::PARAM_INT);
                $stmt_update_stock->bindParam(2, $item['producto_id'], PDO::PARAM_INT);
                if (!$stmt_update_stock->execute()) {
                    throw new Exception("Error al revertir el stock del producto con ID " . $item['producto_id']);
                }
            }

            // 3. Eliminar los ítems de la venta
            $query_eliminar_items = "DELETE FROM " . $this->tabla_items . " WHERE venta_id = ?";
            $stmt_eliminar_items = $this->conn->prepare($query_eliminar_items);
            $stmt_eliminar_items->bindParam(1, $this->id, PDO::PARAM_INT);
            if (!$stmt_eliminar_items->execute()) {
                throw new Exception("Error al eliminar los ítems de la venta.");
            }

            // 4. Eliminar la venta
            $query_eliminar_venta = "DELETE FROM " . $this->tabla . " WHERE id = ?";
            $stmt_eliminar_venta = $this->conn->prepare($query_eliminar_venta);
            $stmt_eliminar_venta->bindParam(1, $this->id, PDO::PARAM_INT);
            if (!$stmt_eliminar_venta->execute()) {
                throw new Exception("Error al eliminar la venta.");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
