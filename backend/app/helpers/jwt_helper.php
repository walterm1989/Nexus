<?php
// backend/app/helpers/jwt_helper.php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Genera un JWT con los datos del usuario.
 */
function generar_jwt($datosUsuario) {
    $claveSecreta = "clave_secreta_super_segura"; // Mejor mover a .env
    $tiempoActual = time();
    $tiempoExpiracion = $tiempoActual + 3600; // 1 hora

    $payload = [
        "iat" => $tiempoActual,
        "exp" => $tiempoExpiracion,
        "data" => $datosUsuario
    ];

    return JWT::encode($payload, $claveSecreta, 'HS256');
}

/**
 * Verifica un JWT y devuelve su payload o null si es inválido.
 */
function verificar_jwt($token) {
    try {
        $claveSecreta = "clave_secreta_super_segura"; // Igual que arriba
        return JWT::decode($token, new Key($claveSecreta, 'HS256'));
    } catch (Exception $e) {
        return null;
    }
}
