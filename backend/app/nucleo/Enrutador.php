<?php
// backend/app/nucleo/Enrutador.php

namespace NexusInventario\Backend\Nucleo;

class Enrutador {
    private $rutas = [];

    public function get(string $uri, callable $callback) {
        $this->rutas['GET'][$uri] = $callback;
    }

    public function post(string $uri, callable $callback) {
        $this->rutas['POST'][$uri] = $callback;
    }

    public function put(string $uri, callable $callback) {
        $this->rutas['PUT'][$uri] = $callback;
    }

    public function delete(string $uri, callable $callback) {
        $this->rutas['DELETE'][$uri] = $callback;
    }

    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = '/nexus_inventario/backend/publico/';
        $ruta = str_replace($basePath, '', $uri);
        $metodo = $_SERVER['REQUEST_METHOD'];
        
        $peticionDatos = $this->obtenerDatosDePeticion($metodo);

        if (isset($this->rutas[$metodo][$ruta])) {
            $callback = $this->rutas[$metodo][$ruta];
            call_user_func($callback, $peticionDatos);
        } else {
            http_response_code(404);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Ruta no encontrada']);
        }
    }

    private function obtenerDatosDePeticion(string $metodo): array {
        $data = [];
        if ($metodo === 'GET') {
            parse_str($_SERVER['QUERY_STRING'] ?? '', $data);
        } else {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true) ?? [];
            parse_str($_SERVER['QUERY_STRING'] ?? '', $dataFromUrl);
            $data = array_merge($data, $dataFromUrl);
        }
        return $data;
    }
}