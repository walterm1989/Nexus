<?php
// backend/app/controladores/ControladorClientes.php

require_once __DIR__ . '/../modelos/Cliente.php';
require_once __DIR__ . '/../nucleo/Respuesta.php';

class ControladorClientes {
    private $modelo;

    public function __construct() {
        $this->modelo = new Cliente();
    }

    public function obtenerTodos() {
        try {
            $clientes = $this->modelo->obtenerTodos();
            Respuesta::enviar(200, true, 'Listado de clientes', $clientes);
        } catch (Exception $e) {
            Respuesta::enviar(500, false, 'Error al obtener clientes: ' . $e->getMessage());
        }
    }

    public function obtenerUno($params) {
        try {
            $id = $params['id'] ?? null;
            if ($id === null) {
                Respuesta::enviar(400, false, 'ID de cliente no proporcionado.');
                return;
            }
            $cliente = $this->modelo->obtenerPorId($id);
            if ($cliente) {
                Respuesta::enviar(200, true, 'Cliente encontrado', $cliente);
            } else {
                Respuesta::enviar(404, false, 'Cliente no encontrado.');
            }
        } catch (Exception $e) {
            Respuesta::enviar(500, false, 'Error al obtener cliente: ' . $e->getMessage());
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            Respuesta::enviar(400, false, 'Datos no válidos.');
            return;
        }

        try {
            $this->modelo->crear($data);
            Respuesta::enviar(201, true, 'Cliente creado con éxito.');
        } catch (Exception $e) {
            Respuesta::enviar(500, false, 'Error al crear cliente: ' . $e->getMessage());
        }
    }

    public function actualizar($params) {
        $id = $params['id'] ?? null;
        if ($id === null) {
            Respuesta::enviar(400, false, 'ID de cliente no proporcionado.');
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            Respuesta::enviar(400, false, 'Datos no válidos.');
            return;
        }

        try {
            $this->modelo->actualizar($id, $data);
            Respuesta::enviar(200, true, 'Cliente actualizado con éxito.');
        } catch (Exception $e) {
            Respuesta::enviar(500, false, 'Error al actualizar cliente: ' . $e->getMessage());
        }
    }

    public function eliminar($params) {
        $id = $params['id'] ?? null;
        if ($id === null) {
            Respuesta::enviar(400, false, 'ID de cliente no proporcionado.');
            return;
        }

        try {
            $this->modelo->eliminar($id);
            Respuesta::enviar(200, true, 'Cliente eliminado con éxito.');
        } catch (Exception $e) {
            Respuesta::enviar(500, false, 'Error al eliminar cliente: ' . $e->getMessage());
        }
    }
}
