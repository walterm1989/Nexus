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
        $metodo = $_SERVER['REQUEST_METHOD'];

        // Resolver ruta: compatibilidad con ?ruta= y con path basado en SCRIPT_NAME
        $ruta = $this->resolverRuta();

        $peticionDatos = $this->obtenerDatosDePeticion($metodo);

        // Match exacto primero
        if (isset($this->rutas[$metodo][$ruta])) {
            $callback = $this->rutas[$metodo][$ruta];
            call_user_func($callback, $peticionDatos);
            return;
        }

        // Match por patrón {param}
        if (!empty($this->rutas[$metodo])) {
            foreach ($this->rutas[$metodo] as $patron => $callback) {
                $regex = $this->patronARedex($patron, $paramNames);
                if ($regex && preg_match($regex, $ruta, $m)) {
                    array_shift($m); // quitar match completo
                    $params = [];
                    if (!empty($paramNames)) {
                        foreach ($paramNames as $i => $name) {
                            $params[$name] = $m[$i] ?? null;
                        }
                    } else {
                        $params = $m;
                    }
                    // fusionar params de path con datos de petición
                    $datos = array_merge($peticionDatos, $params);
                    call_user_func($callback, $datos);
                    return;
                }
            }
        }

        http_response_code(404);
        echo json_encode(['estado' => 'error', 'mensaje' => 'Ruta no encontrada']);
    }

    private function resolverRuta(): string {
        if (isset($_GET['ruta'])) {
            $ruta = '/' . ltrim(trim($_GET['ruta'], '/'), '/');
        } else {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $ruta = '/' . ltrim(substr($path, strlen($base)), '/');
        }
        // Normalizar dobles barras y quitar trailing slash (excepto '/')
        $ruta = preg_replace('#/+#', '/', $ruta);
        if ($ruta !== '/' && substr($ruta, -1) === '/') {
            $ruta = rtrim($ruta, '/');
        }
        return $ruta;
    }

    private function patronARedex(string $patron, ?array &$paramNames = []) {
        $paramNames = [];
        if (strpos($patron, '{') === false) {
            return null;
        }
        $regex = preg_replace_callback('#\{([^/}]+)\}#', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $patron);
        return '#^' . $regex . '$#';
    }

    private function obtenerDatosDePeticion(string $metodo): array {
        $data = [];
        if ($metodo === 'GET') {
            parse_str($_SERVER['QUERY_STRING'] ?? '', $data);
            unset($data['ruta']);
        } else {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true) ?? [];
            parse_str($_SERVER['QUERY_STRING'] ?? '', $dataFromUrl);
            unset($dataFromUrl['ruta']);
            $data = array_merge($data, $dataFromUrl);
        }
        return $data;
    }
}