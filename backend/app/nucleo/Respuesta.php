<?php
// backend/app/nucleo/Respuesta.php

class Respuesta {
    public static function enviar($codigo_estado, $exito, $mensaje, $datos = []) {
        http_response_code($codigo_estado);
        header('Content-Type: application/json');
        echo json_encode([
            'exito' => $exito,
            'mensaje' => $mensaje,
            'datos' => $datos
        ]);
        exit;
    }
}
