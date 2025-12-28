# Backend PHP - 40 Figuritas

Backend PHP puro (sin dependencias) para la aplicación 40 Figuritas. Compatible con cualquier servidor compartido con PHP 7.2+.

## Requisitos

- PHP 7.2 o superior
- MySQL 5.7 o superior
- Extensión `mysqli` habilitada (casi siempre viene por defecto)

## Instalación

### 1. Subir archivos al servidor

Sube la carpeta `server-php` al root del sitio o a una subcarpeta:
- Si subes a `/public_html/api/`, la URL será `https://40figuritas.unr.edu.ar/api/`
- Si subes a `/public_html/`, será `https://40figuritas.unr.edu.ar/`

### 2. Crear archivo `.env`

Copia `.env.example` a `.env` y actualiza con tus credenciales:

```
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=
JWT_SECRET=
MAIL_FROM=
MAIL_PASSWORD=
ALLOWED_ORIGINS=https://midominio.com,https://app.midominio.com
INTERNAL_API_KEY=cambia-est0-por-un-secreto
```

**Importante:** El archivo `.env` debe estar al mismo nivel que `index.php` y NO debe estar bajo control de versiones.

### 2.1 Seguridad (solo código, sin config de servidor)

- CORS: ajusta `ALLOWED_ORIGINS` con los dominios permitidos. Si falta o no coincide, el request con header `Origin` recibe 403. Las llamadas sin `Origin` (server-to-server) siguen funcionando.
- Clave interna: `INTERNAL_API_KEY` permite proteger endpoints exclusivamente servidor-servidor usando el header `X-Internal-Key`.
- Rate limit: se aplica por IP (60 req/60s) y guarda contadores en `server-php/storage/ratelimit` (la carpeta se crea sola). Asegura que el hosting permita escritura en esa ruta.

### 3. Crear tablas en MySQL

Ejecuta este SQL en tu base de datos (usando phpMyAdmin):

```sql
-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `pass` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de preguntas/figuritas
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `questionNumber` int DEFAULT NULL,
  `userId` varchar(255) DEFAULT NULL,
  `cardAssigned` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_userId` (`userId`),
  CONSTRAINT `fk_userId` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. Verificar permisos

- La carpeta `server-php` debe tener permisos de lectura (`755`)
- El archivo `.env` debe tener permisos `644`

## Endpoints API

### Usuarios

**Registrarse**
```
POST /users
Body: {
  "fullname": "Juan Pérez",
  "email": "juan@example.com",
  "password": "senha123"
}
Response: { "message": "Usuario creado con éxito" }
```

**Login**
```
POST /users/login
Body: {
  "email": "juan@example.com",
  "password": "senha123"
}
Response: { "token": "eyJ0eXAiOiJKV1QiLCJhbGc..." }
```

**Obtener usuario por ID**
```
GET /users/:id
Response: { "id": 1, "fullname": "Juan Pérez", "email": "juan@example.com" }
```

**Listar todos los usuarios**
```
GET /users
Response: [{ "id": 1, "fullname": "...", "email": "..." }, ...]
```

**Actualizar usuario**
```
PUT /users/:id
Body: { "fullname": "Nuevo nombre" }
Response: { "message": "Usuario actualizado" }
```

**Eliminar usuario**
```
DELETE /users/:id
Response: { "message": "Usuario eliminado" }
```

### Preguntas/Figuritas

**Obtener todas las preguntas**
```
GET /questions
Response: [{ "id": 1, "questionNumber": 10, "userId": "1", "cardAssigned": 15 }, ...]
```

**Obtener preguntas de un usuario**
```
GET /questions/:userId
Response: [{ "id": 1, ... }, ...]
```

**Crear pregunta**
```
POST /questions
Body: {
  "questionNumber": 10,
  "userId": "1",
  "cardAssigned": 15
}
Response: { "message": "Pregunta creada" }
```

**Actualizar pregunta**
```
PUT /questions/:id
Body: { "cardAssigned": 20 }
Response: { "message": "Pregunta actualizada" }
```

**Eliminar pregunta por ID**
```
DELETE /questions/:id
Response: { "message": "Pregunta eliminada" }
```

**Eliminar todas las preguntas de un usuario**
```
DELETE /questions/:userId
Response: { "message": "Preguntas eliminadas" }
```

## Cambios en el Frontend

Actualiza la URL base de `axios` en tu React app:

**Antes (Node.js):**
```javascript
axios.defaults.baseURL = 'http://localhost:5000';
```

**Después (PHP):**
```javascript
axios.defaults.baseURL = 'https://40figuritas.unr.edu.ar/server-php';
// O si está en una subcarpeta:
axios.defaults.baseURL = 'https://40figuritas.unr.edu.ar/api';
```

## Seguridad

### Directorio `.htaccess`

El archivo `.htaccess` incluido:
- Redirige todas las requests a `index.php` (routing)
- Habilita CORS (para requests desde el frontend)
- Permite mod_rewrite

Si tu hosting no permite `.htaccess`, contacta al proveedor para habilitar Apache `mod_rewrite`.

### Protección de `.env`

El archivo `.env` está fuera del alcance web, pero puedes añadir en `.htaccess`:

```apache
<Files .env>
    Deny from all
</Files>
```

### Validación de entrada

El código incluye validaciones básicas. Para producción, considera:
- Validar emails con `filter_var($email, FILTER_VALIDATE_EMAIL)`
- Sanitizar inputs si usas en HTML
- Usar `mysqli_real_escape_string()` si no usas prepared statements (ya lo hacemos)

## Troubleshooting

### Error: "Connection failed: Access denied for user..."

**Causa:** Credenciales MySQL incorrectas en `.env`

**Solución:** Verifica:
1. DB_HOST es correcto (generalmente `127.0.0.1` o `localhost`)
2. DB_USER y DB_PASS son correctos
3. DB_NAME existe en MySQL

Prueba conectando manualmente en phpMyAdmin.

### Error: "Call to undefined function getallheaders()"

**Causa:** Función PHP no disponible (raro en hosting compartido)

**Solución:** Comenta la línea en `middleware/auth.php` y usa `$_SERVER['HTTP_AUTHORIZATION']` en su lugar.

### Las rutas no funcionan (404)

**Causa:** `.htaccess` no está habilitado o `mod_rewrite` no funciona

**Solución:**
1. Verifica que `.htaccess` esté subido en la misma carpeta que `index.php`
2. Contacta al hosting para habilitar `mod_rewrite`
3. Alternativamente, accede a rutas explícitamente: `/index.php/users/1`

### Los emails no se envían

**Causa:** Configuración SMTP o función `mail()` no disponible

**Solución:**
- En hosting compartido, `mail()` suele funcionar
- Si no, requiere configuración SMTP avanzada (contacta al hosting)
- Puedes comentar `sendWelcomeEmail()` en `UserController.php` por ahora

## Deployment en el servidor actual

1. Backup de la BD actual (ya lo tienes)
2. Exporta datos del servidor Node actual a SQL
3. Sube esta carpeta `server-php` a `/public_html/api/` (o donde decidas)
4. Crea `.env` con credenciales
5. Ejecuta SQL de creación de tablas
6. Actualiza URL en React frontend
7. Test con postman o curl:

```bash
curl -X GET "https://40figuritas.unr.edu.ar/api/users"
```

¡No hay procesos daemon que mantener! PHP ejecuta en cada request.

