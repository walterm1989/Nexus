<?php
// backend/app/controladores/ControladorUsuarios.php

require_once __DIR__ . '/../modelos/Usuario.php';
require_once __DIR__ . '/../nucleo/JWT.php';

class ControladorUsuarios {
    private $db;
    private $usuario;

    public function __construct() {
        // Asumiendo que DB.php es un singleton o tiene un método estático
        // para obtener la conexión
        $this->db = new DB();
        $this->usuario = new Usuario($this->db->conectar());
    }

    public function obtenerTodos($params) {
        try {
            // Se asume que el método del modelo es seguro contra inyección SQL
            $usuarios = $this->usuario->obtenerTodos($params);
            http_response_code(200);
            echo json_encode(['estado' => 'exito', 'data' => $usuarios]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $this->usuario->nombre = $data['nombre'] ?? null;
        $this->usuario->correo = $data['correo'] ?? null;
        $this->usuario->clave = $data['clave'] ?? null;
        $this->usuario->rol_id = $data['rol_id'] ?? null;

        if (empty($this->usuario->nombre) || empty($this->usuario->correo) || empty($this->usuario->clave) || empty($this->usuario->rol_id)) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Todos los campos son requeridos']);
            return;
        }

        try {
            // El modelo se encarga de hashear la contraseña de forma segura.
            if ($this->usuario->crear()) {
                http_response_code(201);
                echo json_encode(['estado' => 'exito', 'mensaje' => 'Usuario creado exitosamente', 'id' => $this->usuario->id]);
            } else {
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'mensaje' => 'No se pudo crear el usuario']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        $correo = $data['correo'] ?? '';
        $clave_ingresada = $data['clave'] ?? '';

        try {
            $usuario_encontrado = $this->usuario->buscarPorCorreo($correo);

            // Verificamos si el usuario existe y si la contraseña es correcta
            if ($usuario_encontrado && password_verify($clave_ingresada, $usuario_encontrado['clave'])) {
                // Autenticación exitosa
                // Creamos el payload del JWT con información del usuario
                $payload = [
                    'exp' => time() + (60 * 60 * 24), // Token expira en 24 horas
                    'data' => [
                        'id' => $usuario_encontrado['id'],
                        'nombre' => $usuario_encontrado['nombre'],
                        'correo' => $usuario_encontrado['correo'],
                        'rol_id' => $usuario_encontrado['rol_id']
                    ]
                ];

                $jwt_helper = new JWT();
                $token = $jwt_helper->generarTokenJWT($payload);

                http_response_code(200);
                echo json_encode([
                    'estado' => 'exito',
                    'mensaje' => 'Autenticación exitosa',
                    'data' => [
                        'token' => $token,
                        'usuario' => [
                            'id' => $usuario_encontrado['id'],
                            'nombre' => $usuario_encontrado['nombre'],
                            'correo' => $usuario_encontrado['correo'],
                            'rol_id' => $usuario_encontrado['rol_id']
                        ]
                    ]
                ]);
            } else {
                // Credenciales inválidas
                http_response_code(401);
                echo json_encode(['estado' => 'error', 'mensaje' => 'Credenciales inválidas']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }
}
