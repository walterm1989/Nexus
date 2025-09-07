<?php
// backend/app/controladores/ControladorAutenticacion.php

require_once __DIR__ . '/../modelos/Usuario.php';
require_once __DIR__ . '/../ayudantes/jwt_helper.php';
require_once __DIR__ . '/../configuracion/base_de_datos.php';

class ControladorAutenticacion {
    private $usuario_modelo;

    public function __construct() {
        $db = BaseDeDatos::obtenerInstancia()->obtenerConexion();
        $this->usuario_modelo = new Usuario($db);
    }

    /** Envía respuesta JSON unificada */
    private function responder($codigo_http, $mensaje, $datos = null) {
        http_response_code($codigo_http);
        $respuesta = [
            'estado' => $codigo_http >= 400 ? 'error' : 'exito',
            'mensaje' => $mensaje
        ];
        if ($datos !== null) $respuesta['datos'] = $datos;
        echo json_encode($respuesta);
    }

    /** Login y generación de JWT */
    public function iniciarSesion() {
        $datos = json_decode(file_get_contents("php://input"));
        if (empty($datos->email) || empty($datos->password)) {
            $this->responder(400, "Email y contraseña son obligatorios.");
            return;
        }

        $usuario = $this->usuario_modelo->buscarPorNombreUsuario($datos->email);

        if ($usuario && $this->usuario_modelo->verificarContrasena($datos->password, $usuario['password_hash'])) {
            $payload = [
                "id" => $usuario['id'],
                "nombre" => $usuario['nombre'],
                "email" => $usuario['email'],
                "rol_id" => $usuario['rol_id']
            ];

            $token = generarTokenJWT($payload);

            $this->responder(200, "Inicio de sesión exitoso.", [
                "usuario" => $payload,
                "token" => $token
            ]);
        } else {
            $this->responder(401, "Email o contraseña incorrectos.");
        }
    }

    /** Registro de nuevo usuario */
    public function registrarUsuario() {
        $datos = json_decode(file_get_contents("php://input"));
        if (empty($datos->nombre) || empty($datos->email) || empty($datos->password) || !isset($datos->rol_id)) {
            $this->responder(400, "Todos los campos son obligatorios (nombre, email, contraseña, rol_id).");
            return;
        }

        if ($this->usuario_modelo->buscarPorNombreUsuario($datos->email)) {
            $this->responder(409, "El email ya está registrado.");
            return;
        }

        $password_hasheada = password_hash($datos->password, PASSWORD_DEFAULT);

        $nuevo_usuario_datos = [
            "nombre" => $datos->nombre,
            "email" => $datos->email,
            "password_hash" => $password_hasheada,
            "rol_id" => $datos->rol_id
        ];

        $this->usuario_modelo->crearUsuario($nuevo_usuario_datos) ?
            $this->responder(201, "Usuario registrado exitosamente.") :
            $this->responder(500, "No se pudo registrar el usuario.");
    }
}
