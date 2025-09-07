<?php
// backend/app/rutas.php

require_once __DIR__ . '/controladores/ControladorClientes.php';
require_once __DIR__ . '/controladores/ControladorProductos.php';
require_once __DIR__ . '/controladores/ControladorEntradasInventario.php';
require_once __DIR__ . '/controladores/ControladorUsuarios.php';
require_once __DIR__ . '/controladores/ControladorVentas.php';
require_once __DIR__ . '/nucleo/Enrutador.php';

// Crear una instancia del enrutador
$enrutador = new Enrutador();

// Definir las rutas de la API
// Rutas de Clientes
$enrutador->get('/clientes', ['ControladorClientes', 'obtenerTodos']);
$enrutador->get('/clientes/{id}', ['ControladorClientes', 'obtenerUno']);
$enrutador->post('/clientes', ['ControladorClientes', 'crear']);
$enrutador->put('/clientes/{id}', ['ControladorClientes', 'actualizar']);
$enrutador->delete('/clientes/{id}', ['ControladorClientes', 'eliminar']);

// Rutas de Productos
$enrutador->get('/productos', ['ControladorProductos', 'obtenerTodos']);
$enrutador->get('/productos/{id}', ['ControladorProductos', 'obtenerUno']);
$enrutador->get('/productos/stock-bajo', ['ControladorProductos', 'obtenerProductosBajoStock']);
$enrutador->post('/productos', ['ControladorProductos', 'crear']);
$enrutador->put('/productos/{id}', ['ControladorProductos', 'actualizar']);
$enrutador->delete('/productos/{id}', ['ControladorProductos', 'eliminar']);

// Rutas de Entradas de Inventario
$enrutador->get('/entradas', ['ControladorEntradasInventario', 'obtenerTodas']);
$enrutador->post('/entradas', ['ControladorEntradasInventario', 'crear']);

// Rutas de Usuarios
$enrutador->get('/usuarios', ['ControladorUsuarios', 'obtenerTodos']);
$enrutador->post('/usuarios', ['ControladorUsuarios', 'crear']);
$enrutador->post('/usuarios/login', ['ControladorUsuarios', 'login']);

// Rutas de Ventas
$enrutador->get('/ventas', ['ControladorVentas', 'obtenerTodas']);
$enrutador->post('/ventas', ['ControladorVentas', 'crearVenta']);
$enrutador->delete('/ventas/{id}', ['ControladorVentas', 'eliminar']);
