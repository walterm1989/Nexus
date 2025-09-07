# Desarrollo (Windows) - Nexus

Backend con servidor embebido PHP (ignora .htaccess):

1) Instala dependencias:
   - PHP 8+
   - Composer (si usas características de vendor)

2) Levanta el backend:
   php -S 127.0.0.1:8000 -t backend/publico

3) Frontend:
   - Abre los archivos HTML desde frontend/ (o sirve estáticamente).
   - El frontend llama a la API en http://127.0.0.1:8000 mediante rutas REST por path.

Notas:
- .htaccess no aplica en php -S.
- Para Apache/XAMPP, asegúrate de apuntar el DocumentRoot a backend/publico/ o de usar el .htaccess incluido en backend/publico/.
- Para cambiar la URL base en desarrollo, edita frontend/js/main.js (const BASE_URL).

Pruebas rápidas con curl:
- curl -i http://127.0.0.1:8000/clientes
- curl -i http://127.0.0.1:8000/clientes/1
- curl -i -X POST http://127.0.0.1:8000/clientes -H "Content-Type: application/json" -d "{}"
- curl -i -X PUT  http://127.0.0.1:8000/clientes/1 -H "Content-Type: application/json" -d "{}"
- curl -i -X DELETE http://127.0.0.1:8000/clientes/1
- curl -i -X OPTIONS http://127.0.0.1:8000/clientes