<?php
// backend/app/controladores/ControladorCategorias.php

require_once __DIR__ . '/../modelos/Categoria.php';
require_once __DIR__ . '/../configuracion/base_de_datos.php';

class ControladorCategorias {
    private $categoria_modelo;

    public function __construct() {
        $db = BaseDeDatos::obtenerInstancia()->obtenerConexion();
        $this->categoria_modelo = new Categoria($db);
    }

    // --- Función para enviar respuesta JSON uniforme ---
    private function responder($codigo_http, $mensaje, $datos = null) {
        http_response_code($codigo_http);
        $respuesta = [
            'estado' => $codigo_http >= 400 ? 'error' : 'exito',
            'mensaje' => $mensaje
        ];
        if ($datos !== null) $respuesta['datos'] = $datos;
        echo json_encode($respuesta);
    }

    // --- Verifica rol de usuario ---
    private function verificarRol($params, $rol_requerido = 1) {
        $rol_usuario = $params['jwt_payload']['data']['rol_id'] ?? null;
        if ($rol_usuario === null || $rol_usuario > $rol_requerido) {
            $this->responder(403, 'Acceso denegado. No tienes permisos para esta acción.');
            return false;
        }
        return true;
    }

    // --- Convierte PDOStatement a array ---
    private function fetchAll(PDOStatement $stmt) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Obtener todas las categorías ---
    public function obtenerTodos($params) {
        if (!isset($params['jwt_payload'])) {
            $this->responder(401, 'No autenticado.');
            return;
        }

        $limite = (int)($_GET['limite'] ?? 10);
        $pagina = (int)($_GET['pagina'] ?? 1);
        $offset = ($pagina - 1) * $limite;
        $ordenar_por = $_GET['ordenar_por'] ?? 'nombre';
        $direccion = strtoupper($_GET['direccion'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $parametros = [
            'limite'=>$limite,
            'offset'=>$offset,
            'ordenar_por'=>$ordenar_por,
            'direccion'=>$direccion,
            'filtro_nombre'=>$_GET['filtro_nombre'] ?? null,
            'filtro_descripcion'=>$_GET['filtro_descripcion'] ?? null
        ];

        try {
            $resultado = $this->categoria_modelo->obtenerTodos($parametros);
            $registros = $this->fetchAll($resultado['stmt']);
            $total = $resultado['total_registros'];

            if (count($registros) > 0) {
                $this->responder(200, 'Categorías encontradas.', [
                    'registros'=>$registros,
                    'paginacion'=>[
                        'total_registros'=>$total,
                        'limite_por_pagina'=>$limite,
                        'pagina_actual'=>$pagina,
                        'total_paginas'=>ceil($total/$limite)
                    ]
                ]);
            } else {
                $this->responder(404, 'No se encontraron categorías.');
            }
        } catch (Exception $e) {
            $this->responder(500, 'Error al obtener categorías: '.$e->getMessage());
        }
    }

    // --- Obtener una categoría por ID ---
    public function obtenerUno($params) {
        if (!isset($params['id']) || !is_numeric($params['id'])) {
            $this->responder(400, 'ID de categoría inválido.');
            return;
        }

        if ($this->categoria_modelo->obtenerPorId($params['id'])) {
            $this->responder(200, 'Categoría encontrada.', [
                'id'=>$this->categoria_modelo->id,
                'nombre'=>$this->categoria_modelo->nombre,
                'descripcion'=>$this->categoria_modelo->descripcion
            ]);
        } else {
            $this->responder(404, 'Categoría no encontrada.');
        }
    }

    // --- Crear categoría ---
    public function crear($params) {
        if (!$this->verificarRol($params,1)) return;

        $datos = json_decode(file_get_contents("php://input"));
        if (empty($datos->nombre)) {
            $this->responder(400, 'El nombre de la categoría es obligatorio.');
            return;
        }

        $this->categoria_modelo->nombre = $datos->nombre;
        $this->categoria_modelo->descripcion = $datos->descripcion ?? '';

        $this->categoria_modelo->crear() ?
            $this->responder(201, 'Categoría creada exitosamente.') :
            $this->responder(503, 'No se pudo crear la categoría.');
    }

    // --- Actualizar categoría ---
    public function actualizar($params) {
        if (!$this->verificarRol($params,1)) return;
        if (!isset($params['id']) || !is_numeric($params['id'])) {
            $this->responder(400, 'ID de categoría inválido.');
            return;
        }

        $datos = json_decode(file_get_contents("php://input"));
        if (empty($datos->nombre)) {
            $this->responder(400, 'El nombre de la categoría es obligatorio.');
            return;
        }

        $this->categoria_modelo->id = $params['id'];
        $this->categoria_modelo->nombre = $datos->nombre;
        $this->categoria_modelo->descripcion = $datos->descripcion ?? '';

        $this->categoria_modelo->actualizar() ?
            $this->responder(200, 'Categoría actualizada exitosamente.') :
            $this->responder(503, 'No se pudo actualizar la categoría.');
    }

    // --- Eliminar categoría ---
    public function eliminar($params) {
        if (!$this->verificarRol($params,1)) return;
        if (!isset($params['id']) || !is_numeric($params['id'])) {
            $this->responder(400, 'ID de categoría inválido.');
            return;
        }

        $this->categoria_modelo->id = $params['id'];
        $this->categoria_modelo->eliminar() ?
            $this->responder(200, 'Categoría eliminada.') :
            $this->responder(503, 'No se pudo eliminar la categoría.');
    }
}
