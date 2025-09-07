<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/ayudantes/jwt_helper.php';

// Generar token de ejemplo
$datos = ["usuario_id" => 1, "rol" => "admin"];
$token = generarTokenJWT($datos);

echo "<h3>TOKEN GENERADO:</h3>";
echo "<textarea cols='100' rows='5'>$token</textarea><br><br>";

// Verificar token
$verificado = verificarTokenJWT($token);

echo "<h3>DATOS VERIFICADOS:</h3>";
echo "<pre>";
print_r($verificado);
echo "</pre>";
