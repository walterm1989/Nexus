<?php
// backend/index.php
//
// Archivo auxiliar: no es punto de entrada.
// El servidor embebido o Apache deben apuntar a backend/publico/index.php

// Evitar includes rotos y no despachar desde aquí.
require_once __DIR__ . '/vendor/autoload.php';

// Manejar solicitudes preflight de CORS sin cuerpo adicional.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}
