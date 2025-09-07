<?php
// backend/app/controladores/ControladorVentas.php

require_once __DIR__ . '/../modelos/Venta.php';
require_once __DIR__ . '/../configuracion/base_de_datos.php';

class ControladorVentas {
    private $venta_modelo;

    public function __construct() {
        $db = BaseDeDatos::obtenerInstancia()->obtenerConexion();
        $this->venta_modelo = new Venta($db);
    }

    private function responder($codigo_http, $mensaje, $datos = null) {
        http_response_code($codigo_http);
        $respuesta = [
            'estado' => $codigo_http >= 400 ? 'error' : 'exito',
            'mensaje' => $mensaje
        ];
        if ($datos !== null) $respuesta['datos'] = $datos;
        echo json_encode($respuesta);
    }

    private function verificarRol($params, $rol_requerido) {
        $rol_usuario = $params['jwt_payload']['data']['rol_id'] ?? null;
        if ($rol_usuario === null || $rol_usuario > $rol_requerido) {
            $this->responder(403, "Acceso denegado. No tienes permisos para realizar esta acción.");
            return false;
        }
        return true;
    }

    // --- Crear venta ---
    public function crear($params) {
        if (!$this->verificarRol($params,2)) return;

        $datos = json_decode(file_get_contents("php://input"));
        if (empty($datos->cliente_id) || empty($datos->detalles) || !is_array($datos->detalles)) {
            $this->responder(400, "Faltan datos obligatorios (cliente_id, detalles).");
            return;
        }

        $this->venta_modelo->cliente_id = $datos->cliente_id;
        $this->venta_modelo->usuario_id = $params['jwt_payload']['data']['id'];
        $this->venta_modelo->detalles = $datos->detalles;
        $this->venta_modelo->tipo_pago = $datos->tipo_pago ?? 'efectivo';
        $this->venta_modelo->comprobante_pago = $datos->comprobante_pago ?? null;

        try {
            $this->venta_modelo->crear() ?
                $this->responder(201, "Venta creada exitosamente") :
                $this->responder(503, "No se pudo crear la venta");
        } catch (Exception $e) {
            $this->responder(500, "Error al crear la venta: ".$e->getMessage());
        }
    }

    // --- Obtener todas las ventas ---
    public function obtenerTodas($params) {
        if (!$this->verificarRol($params,2)) return;

        $limite = (int)($_GET['limite'] ?? 10);
        $pagina = (int)($_GET['pagina'] ?? 1);
        $offset = ($pagina - 1) * $limite;

        $mapa_columnas = [
            'id'=>'v.id','fecha_venta'=>'v.fecha_venta','total'=>'v.total',
            'estado'=>'v.estado','nombre_cliente'=>'c.nombre','nombre_usuario'=>'u.nombre'
        ];
        $ordenar_por = $mapa_columnas[$_GET['ordenar_por'] ?? 'fecha_venta'] ?? 'v.fecha_venta';
        $direccion = strtoupper($_GET['direccion'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $parametros = [
            'limite'=>$limite,
            'offset'=>$offset,
            'ordenar_por'=>$ordenar_por,
            'direccion'=>$direccion,
            'filtro_cliente_id'=>$_GET['filtro_cliente_id'] ?? null,
            'filtro_usuario_id'=>$_GET['filtro_usuario_id'] ?? null,
            'filtro_estado'=>$_GET['filtro_estado'] ?? null
        ];

        try {
            $resultado = $this->venta_modelo->obtenerTodas($parametros);
            if ($resultado['stmt']->rowCount() === 0) {
                $this->responder(404, "No se encontraron ventas.");
                return;
            }

            $ventas = [
                "registros"=>$resultado['stmt']->fetchAll(PDO::FETCH_ASSOC),
                "paginacion"=>[
                    "total_registros"=>$resultado['total_registros'],
                    "limite_por_pagina"=>$limite,
                    "pagina_actual"=>$pagina,
                    "total_paginas"=>ceil($resultado['total_registros']/$limite)
                ]
            ];
            $this->responder(200, "Ventas obtenidas.", $ventas);
        } catch (Exception $e) {
            $this->responder(500, "Error al obtener ventas: ".$e->getMessage());
        }
    }

    // --- Obtener una venta por ID ---
    public function obtenerUno($params) {
        if (!$this->verificarRol($params,2)) return;
        if (!isset($params['id']) || !is_numeric($params['id'])) {
            $this->responder(400, "ID de venta no válido.");
            return;
        }

        try {
            $venta = $this->venta_modelo->obtenerPorId($params['id']);
            $venta ?
                $this->responder(200, "Venta encontrada.", $venta) :
                $this->responder(404, "Venta no encontrada.");
        } catch (Exception $e) {
            $this->responder(500, "Error al obtener la venta: ".$e->getMessage());
        }
    }

    // --- Actualizar estado de venta ---
    public function actualizarEstado($params) {
        if (!$this->verificarRol($params,1)) return;
        if (!isset($params['id']) || !is_numeric($params['id'])) {
            $this->responder(400, "ID de venta no válido.");
            return;
        }

        $datos = json_decode(file_get_contents("php://input"));
        if (empty($datos->estado)) {
            $this->responder(400, "Debe especificar el estado.");
            return;
        }

        $this->venta_modelo->id = $params['id'];
        $this->venta_modelo->estado = $datos->estado;

        try {
            $this->venta_modelo->actualizarEstado() ?
                $this->responder(200, "Estado de venta actualizado.") :
                $this->responder(503, "No se pudo actualizar el estado.");
        } catch (Exception $e) {
            $this->responder(500, "Error al actualizar estado: ".$e->getMessage());
        }
    }

    // --- Eliminar venta ---
    public function eliminar($params) {
        if (!$this->verificarRol($params,1)) return;
        if (!isset($params['id']) || !is_numeric($params['id'])) {
            $this->responder(400, "ID de venta no válido.");
            return;
        }

        $this->venta_modelo->id = $params['id'];

        try {
            $this->venta_modelo->eliminar() ?
                $this->responder(200, "Venta eliminada.") :
                $this->responder(503, "No se pudo eliminar la venta.");
        } catch (Exception $e) {
            $this->responder(500, "Error al eliminar venta: ".$e->getMessage());
        }
    }
}
