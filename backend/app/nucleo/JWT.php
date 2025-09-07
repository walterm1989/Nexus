<?php
// backend/app/nucleo/JWT.php

// Asegúrate de que esta clave se defina como una variable de entorno
// en tu servidor de producción (por ejemplo, con un archivo .env)
// Si no está definida, se usa una por defecto SOLO para desarrollo.
$secret_key = getenv('JWT_SECRET_KEY') ?: 'clave_secreta_para_desarrollo_insegura_por_favor_cambia_esto';

class JWT {
    private $secret_key;

    public function __construct() {
        global $secret_key;
        $this->secret_key = $secret_key;
    }

    public function generarTokenJWT($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public function verificarTokenJWT($token) {
        $partes = explode('.', $token);
        if (count($partes) !== 3) {
            return false;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $partes;
        
        $firma_verificacion = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
        $base64UrlFirmaVerificacion = $this->base64UrlEncode($firma_verificacion);
        
        if ($base64UrlSignature !== $base64UrlFirmaVerificacion) {
            return false;
        }
        
        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);
        if ($payload && isset($payload['exp']) && $payload['exp'] > time()) {
            return $payload;
        }
        
        return false;
    }

    private function base64UrlEncode($text) {
        $base64 = base64_encode($text);
        $base64Url = strtr($base64, '+/', '-_');
        return rtrim($base64Url, '=');
    }
    
    private function base64UrlDecode($base64Url) {
        $base64 = strtr($base64Url, '-_', '+/');
        return base64_decode($base64);
    }
}

