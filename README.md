# IFTS 29 - Tecnicatura en Desarrollo de Software

## Seminario de actualización DevOps - 3° D

**Práctica formativa obligatorio 2**
**Alumnos:** Damián Andrés Clausi, Descosido Cristian, Gill César Antonio  
**Profesor:** Javier Blanco

## 1) Objetivo

* Levantar un stack **Web + Base de Datos** con Docker.
* Crear una **página de inicio** que lea datos desde MySQL.
* Generar **imagen propia** de la web y **publicarla** en Docker Hub.
* Subir el proyecto a **GitHub**, documentando comandos y evidencias.

---

## 2) Arquitectura

```text
Navegador → http://localhost:8080 → [ web (PHP+Apache) ] --(bridge)--> [ db (MySQL 8) ]
```

* **web:** PHP 8.2 + Apache, conecta a MySQL vía PDO/MySQL.
* **db:** MySQL 8, datos persistidos en volumen Docker.

---

## 3) Requisitos

* Docker Desktop/Engine (con daemon corriendo).
* (Opcional) MySQL Workbench para visualización/SQL GUI.
* Cuenta en **Docker Hub** y **GitHub**.

---

## 4) Estructura del repositorio

```text
./
├─ Dockerfile
├─ docker-compose.yml              # Usa la imagen publicada en Docker Hub
├─ docker-compose.yml.local        # Variante para build local (documentación y pruebas)
├─ .env.example                    # Variables de entorno (sin secretos)
├─ .gitignore                      # Evita subir .env real
├─ src/
│  └─ index.php                    # Home que crea tabla y lista registros
└─ screenshots/
   ├─ dockerhub1.png               # Vista general del repo en Docker Hub
   ├─ dockerhub2.png               # Vista de tags en Docker Hub
   └─ localhost.png                # Home en http://localhost:8080
```

---

## 5) Variables de entorno (`.env`)

Crear un archivo **`.env`** (no se sube al repo). Parámetros usados en la práctica:

```dotenv
MYSQL_ROOT_PASSWORD=secret
MYSQL_DATABASE=demo
MYSQL_USER=demo
MYSQL_PASSWORD=demo
```

> En el repo incluimos **`.env.example`** con valores de ejemplo/placeholder. **No subir** `.env` real.

---

## 6) Ejecución

### 6.1 Usando la imagen publicada en Docker Hub (recomendado para la entrega)

```bash
docker-compose up -d
# Web: http://localhost:8080
```

### 6.2 Forzar pull desde Docker Hub (validación)

```bash
docker-compose down
docker rmi pfo2-web:latest || true
docker rmi damian2k/pfo2-web:1.0 || true
docker-compose up -d
```

### 6.3 Construcción local (variante `docker-compose.yml.local`)

```bash
docker-compose -f docker-compose.yml.local up -d --build
```

---

## 7) Comandos ejecutados (con explicación)

> **Nota:** los siguientes comandos se usaron a lo largo de la práctica y/o como guía para cumplir cada ítem de la PFO.

### 7.1 Imágenes, contenedores y logs

```bash
# Ver servicios en ejecución
docker-compose ps

# Ver imágenes asociadas a los servicios
docker-compose images

# Logs de cada servicio
docker-compose logs db  --tail 50
docker-compose logs web --tail 50
```

### 7.2 Build/Tag/Push de la imagen propia

```bash
# Construir imagen local (cuando se usa build)
docker build -t pfo2-web:latest .

# Etiquetar con nuestro repositorio en Docker Hub
docker tag pfo2-web:latest damian2k/pfo2-web:1.0

docker tag pfo2-web:latest damian2k/pfo2-web:latest

# Publicar en Docker Hub
docker push damian2k/pfo2-web:1.0
docker push damian2k/pfo2-web:latest
```

### 7.3 SQL de inicialización (si la home no autogenera datos)

```sql
CREATE TABLE IF NOT EXISTS personas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL
);
INSERT INTO personas (nombre) VALUES ('Ada Lovelace'), ('Alan Turing');
```

> También puede ejecutarse por CLI:

```bash
docker-compose exec -T db mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" <<'SQL'
CREATE TABLE IF NOT EXISTS personas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL
);
INSERT INTO personas (nombre) VALUES ('Ada Lovelace'), ('Alan Turing');
SQL
```

### 7.4 Bajar/levantar el stack

```bash
# Apagar
docker-compose down

# Apagar y borrar datos de MySQL (¡destructivo!)
docker-compose down -v

# Levantar (pull o build según archivo usado)
docker-compose up -d
```

---

## 8) Evidencias (salidas reales)

### 8.1 `docker-compose ps`

```text
NAME         IMAGE                   COMMAND                  SERVICE   CREATED         STATUS                   PORTS
pfo2-mysql   mysql:8.0               "docker-entrypoint.s…"   db        6 minutes ago   Up 6 minutes (healthy)   0.0.0.0:3306->3306/tcp, [::]:3306->3306/tcp
pfo2-web     damian2k/pfo2-web:1.0   "docker-php-entrypoi…"   web       6 minutes ago   Up 6 minutes             0.0.0.0:8080->80/tcp, [::]:8080->80/tcp
```

### 8.2 `docker-compose images`

```text
CONTAINER    REPOSITORY          TAG   PLATFORM     IMAGE ID      SIZE   CREATED
pfo2-mysql   mysql               8.0   linux/amd64  d2fdd0af2893  236MB  32 minutes ago
pfo2-web     damian2k/pfo2-web   1.0   linux/amd64  a955c2812b47  176MB   7 minutes ago
```

### 8.3 Capturas incluidas en `./screenshots/`

#### Docker Hub

* `dockerhub1.png`: vista general del repositorio en Docker Hub.
* `dockerhub2.png`: vista de **tags** en Docker Hub.

![Vista del repositorio en Docker Hub](./screenshots/dockerhub1.png)

![Tags disponibles en Docker Hub](./screenshots/dockerhub2.png)

#### Aplicación Web

* `localhost.png`: Home en `http://localhost:8080` con el listado de personas.

![Aplicación web funcionando en localhost:8080](./screenshots/localhost.png)

#### MySQL Workbench

* `mysql1.png`: Conexión desde MySQL Workbench al contenedor MySQL.
* `mysql2.png`: Vista de la base de datos `demo` y tabla `personas` en MySQL Workbench.
* `mysql3.png`: Consulta SELECT mostrando los datos de la tabla `personas`.

![Conexión MySQL Workbench](./screenshots/mysql1.png)

![Base de datos en MySQL Workbench](./screenshots/mysql2.png)

![Datos de la tabla personas](./screenshots/mysql3.png)

> **Aviso Compose:** si aparece `the attribute version is obsolete`, se puede **eliminar** la línea `version:` del compose sin afectar el funcionamiento.

---

## 9) Conexión con MySQL Workbench

Para cumplir con el punto 6 de la PFO2 ("Desde MySQL Workbench conectarse al servidor y crear una base de datos con una tabla"):

### 9.1 Configuración de la conexión

1. **Abrir MySQL Workbench**
2. **Crear nueva conexión** con los siguientes parámetros:
   * **Connection Name**: `PFO2 Local`
   * **Hostname**: `localhost` (o `127.0.0.1`)
   * **Port**: `3306`
   * **Username**: `demo` (valor de `MYSQL_USER` en `.env`)
   * **Password**: `demo` (valor de `MYSQL_PASSWORD` en `.env`)

### 9.2 Comandos SQL ejecutados

```sql
-- Verificar base de datos
SHOW DATABASES;

-- Usar la base de datos demo
USE demo;

-- Verificar tablas existentes
SHOW TABLES;

-- Ver estructura de la tabla personas
DESCRIBE personas;

-- Consultar datos existentes
SELECT * FROM personas;

-- Los datos iniciales ya están insertados automáticamente
-- Opcionalmente se pueden agregar más registros:
-- INSERT INTO personas (nombre) VALUES ('Grace Hopper');

-- Verificar los nuevos datos
SELECT id, nombre FROM personas ORDER BY id;
```

### 9.3 Evidencias

Las capturas `mysql1.png`, `mysql2.png` y `mysql3.png` muestran:

* Conexión exitosa desde MySQL Workbench
* Visualización de la base de datos y tabla
* Ejecución de consultas SQL y resultados

---

## 10) Problemas Experimentados y Soluciones Implementadas

Durante la realización de esta práctica formativa, se experimentaron diversos problemas técnicos que son comunes en el desarrollo con contenedores. A continuación se documentan los principales desafíos encontrados y las soluciones aplicadas:

### 10.1 Problemas de Configuración de Docker

#### Problema: Docker no iniciaba en WSL/Linux

**Descripción**: Al intentar ejecutar comandos Docker, aparecía el error "Cannot connect to the Docker daemon".

**Causa**: El servicio Docker no estaba ejecutándose o WSL no tenía acceso al Docker Desktop de Windows.

**Solución aplicada**:

```bash
# Verificar estado del servicio Docker
sudo systemctl status docker

# Iniciar Docker si está parado
sudo systemctl start docker

# Para WSL: integrar con Docker Desktop
# Configurar Docker Desktop > Settings > Resources > WSL Integration
```

**Problema encontrado y solución propuesta**: Siempre verificar que Docker esté corriendo antes de ejecutar comandos de contenedores.

### 10.2 Problemas de Conexión entre Contenedores

#### Problema: La aplicación web no conectaba a MySQL

**Descripción**: La página web mostraba "Error de conexión" al intentar acceder a la base de datos.

**Causa Original**: Se usaba `localhost` como host de conexión en lugar del nombre del servicio Docker.

**Solución implementada**:

```php
// ❌ Incorrecto - no funciona entre contenedores
$host = 'localhost';

// ✅ Correcto - usar nombre del servicio
$host = getenv('DB_HOST') ?: 'db';
```

**Configuración en docker-compose.yml**:

```yaml
web:
  environment:
    DB_HOST: db  # Nombre del servicio MySQL
```

#### Problema: Conexión rechazada al inicio

**Descripción**: Aunque se corrigió el host, a veces la web fallaba al iniciar porque MySQL no estaba listo.

**Solución implementada**:

```yaml
web:
  depends_on:
    db:
      condition: service_healthy  # Esperar a que MySQL esté listo

db:
  healthcheck:
    test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${MYSQL_ROOT_PASSWORD}"]
    interval: 5s
    timeout: 3s
    retries: 20
```

### 10.3 Problemas de Variables de Entorno

#### Problema: Credenciales hardcodeadas en el código

**Descripción**: Inicialmente las credenciales estaban escritas directamente en el código PHP.

**Riesgo identificado**: Las credenciales quedarían expuestas en el repositorio público.

**Solución implementada**:

1. **Crear archivo `.env`** para variables sensibles:

```bash
MYSQL_ROOT_PASSWORD=secret
MYSQL_DATABASE=demo
MYSQL_USER=demo
MYSQL_PASSWORD=demo
```

1. **Modificar código PHP** para usar variables de entorno:

```php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'demo';
$user = getenv('DB_USER') ?: 'demo';
$pass = getenv('DB_PASS') ?: 'demo';
```

1. **Configurar `.gitignore`** para proteger secretos:

```text
.env
*.log
```

### 10.4 Problemas con Extensiones PHP

#### Problema: "Class 'PDO' not found"

**Descripción**: Error al intentar conectar a MySQL desde PHP.

**Causa**: La imagen base `php:8.2-apache` no incluye la extensión PDO MySQL.

**Solución en Dockerfile**:

```dockerfile
FROM php:8.2-apache

# ✅ Instalar extensión PDO MySQL
RUN docker-php-ext-install pdo_mysql

COPY src/ /var/www/html/
```

### 10.5 Problemas de Persistencia de Datos

#### Problema: Datos perdidos al reiniciar contenedores

**Descripción**: Cada vez que se reiniciaba el contenedor MySQL, se perdían todos los datos.

**Causa**: No se configuró persistencia de volúmenes.

**Solución implementada**:

```yaml
services:
  db:
    volumes:
      - db_data:/var/lib/mysql  # Persistir datos

volumes:
  db_data:  # Volumen nombrado
```

### 10.6 Problemas de Publicación en Docker Hub

#### Problema: "Access denied" al hacer push

**Descripción**: Error al intentar subir la imagen a Docker Hub.

**Causa**: No se había iniciado sesión en Docker Hub desde la terminal.

**Solución**:

```bash
# Iniciar sesión en Docker Hub
docker login

# Etiquetar correctamente la imagen
docker tag pfo2-web:latest damian2k/pfo2-web:1.0

# Publicar
docker push damian2k/pfo2-web:1.0
```

#### Problema: Imagen muy pesada

**Descripción**: La imagen inicial pesaba más de 500MB.

**Optimización aplicada**:

* Usar imagen base optimizada (`php:8.2-apache` en lugar de ubuntu + apache + php)
* Minimizar archivos copiados
* Resultado: imagen final de ~176MB

### 10.7 Problemas de Documentación

#### Problema: Imágenes no se mostraban en GitHub

**Descripción**: Los screenshots no aparecían en el README de GitHub.

**Causa**: Rutas incorrectas en la sintaxis Markdown.

**Solución**:

```markdown
# ❌ Incorrecto
![Imagen](screenshots/imagen.png)

# ✅ Correcto  
![Imagen](./screenshots/imagen.png)
```

#### Problema: Errores de formato Markdown

**Descripción**: Múltiples errores de linting MD (headings, listas, bloques de código).

**Soluciones aplicadas**:

* Agregar lenguajes a bloques de código: ` ```bash`, ` ```yaml`, ` ```text`
* Espacios alrededor de headings
* Usar asteriscos (*) en lugar de guiones (-) para listas
* Espacios alrededor de listas

### 10.8 Problemas de Compatibilidad de Puertos

#### Problema: "Port 3306 already in use"

**Descripción**: Error al levantar MySQL porque el puerto ya estaba ocupado.

**Causa**: MySQL local o otro contenedor usando el mismo puerto.

**Soluciones disponibles**:

```bash
# Opción 1: Cambiar puerto en docker-compose.yml
ports:
  - "3307:3306"  # Puerto externo diferente

# Opción 2: Parar servicio MySQL local
sudo systemctl stop mysql

# Opción 3: Ver qué proceso usa el puerto
sudo netstat -tlnp | grep :3306
```

### 10.9 Problemas de Conexión desde MySQL Workbench

#### Problema: "Connection refused" desde Workbench

**Descripción**: No se podía conectar desde MySQL Workbench al contenedor.

**Diagnóstico y solución**:

1. **Verificar que el contenedor esté corriendo**:

```bash
docker ps | grep mysql
```

1. **Verificar puerto expuesto**:

```bash
docker port pfo2-mysql
```

1. **Configuración correcta en Workbench**:

* Host: `localhost` (no `db`)
* Puerto: `3306`
* Usuario: `demo` (no `root` inicialmente)
* Contraseña: `demo`

### 10.10 Problemas de Consistencia entre Código y Capturas

#### Problema: Inconsistencia entre código y evidencias visuales

**Descripción**: Durante la finalización de la práctica se detectó que el código PHP inicializaba 5 registros en la base de datos, pero las capturas de pantalla existentes mostraban solo 2 registros.

**Causa identificada**: El desarrollo evolucionó y se agregaron más personas al dataset inicial sin actualizar las capturas correspondientes.

**Impacto**: Las evidencias visuales no coincidían con el comportamiento real del código, comprometiendo la integridad de la documentación.

**Solución implementada**:

```php
// Código adaptado para coincidir con las capturas existentes
if ($count === 0) {
    $pdo->exec("
      INSERT INTO personas (nombre)
      VALUES ('Ada Lovelace'), ('Alan Turing')
    ");
}
```

**Archivos modificados**:

* `src/index.php`: Reducido dataset inicial de 5 a 2 personas
* `README.md`: Actualizada documentación SQL y ejemplos de código
* Secciones afectadas: 7.3, 9.2, 11.4

**Beneficios de esta solución**:

* ✅ Consistencia total entre código, documentación y capturas
* ✅ No requiere regenerar screenshots existentes
* ✅ Mantiene integridad de evidencias para entrega académica
* ✅ Documentación precisa y verificable

### 10.11 Problemas Encontrados y Soluciones Propuestas

1. **Siempre usar variables de entorno** para configuraciones sensibles
2. **Implementar health checks** en servicios críticos como bases de datos
3. **Usar volúmenes nombrados** para persistencia de datos importantes
4. **Documentar problemas y soluciones** para futuras referencias
5. **Probar conectividad** entre contenedores antes de desarrollar aplicaciones complejas
6. **Usar .gitignore** apropiado para proteger secretos
7. **Optimizar imágenes Docker** para reducir tamaño y tiempo de descarga
8. **Verificar compatibilidad de puertos** antes de levantar servicios
9. **Mantener consistencia** entre código, documentación y evidencias visuales
10. **Validar capturas** antes de finalizar entregables académicos

---

## 11) Publicación en GitHub

Repositorio (privado):

```text
https://github.com/damianclausi/DevOps_PFO_2_ComD_GrupoElQuintoElemento_Clausi_Descosido_Gill
```

### Pasos (resumen)

```bash
# Proteger secretos
touch .env .env.example
# (Rellenar .env con valores reales y .env.example con placeholders)

# Ignorar .env real
cat > .gitignore << 'EOF'
.env
node_modules/
vendor/
.DS_Store
*.log
EOF

# Commit inicial
git init
git add Dockerfile docker-compose.yml docker-compose.yml.local src/ README.md .gitignore .env.example
git commit -m "PFO2: stack Docker (web+db), imagen publicada y README"

git branch -M main
# Añadir remoto y push (si ya se creó el repo privado desde la web)
git remote add origin "git@github.com:damianclausi/DevOps_PFO_2_ComD_GrupoElQuintoElemento_Clausi_Descosido_Gill.git"
git push -u origin main
```

---

## 11) Archivos principales (referencia)

### 11.1 `Dockerfile`

```dockerfile
FROM php:8.2-apache

# Extensión para conectarse a MySQL
RUN docker-php-ext-install pdo_mysql

# Código de la app
COPY src/ /var/www/html/
```

### 11.2 `docker-compose.yml` (usa imagen publicada)

```yaml
services:
  db:
    image: mysql:8.0
    container_name: pfo2-mysql
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    command: --default-authentication-plugin=mysql_native_password
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${MYSQL_ROOT_PASSWORD}"]
      interval: 5s
      timeout: 3s
      retries: 20

  web:
    image: damian2k/pfo2-web:1.0
    container_name: pfo2-web
    environment:
      DB_HOST: db
      DB_NAME: ${MYSQL_DATABASE}
      DB_USER: ${MYSQL_USER}
      DB_PASS: ${MYSQL_PASSWORD}
    ports:
      - "8080:80"
    depends_on:
      - db

volumes:
  db_data:
```

### 11.3 `docker-compose.yml.local` (build local)

```yaml
services:
  db:
    image: mysql:8.0
    container_name: pfo2-mysql
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    command: --default-authentication-plugin=mysql_native_password
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${MYSQL_ROOT_PASSWORD}"]
      interval: 5s
      timeout: 3s
      retries: 20

  web:
    build: .
    container_name: pfo2-web
    environment:
      DB_HOST: db
      DB_NAME: ${MYSQL_DATABASE}
      DB_USER: ${MYSQL_USER}
      DB_PASS: ${MYSQL_PASSWORD}
    ports:
      - "8080:80"
    depends_on:
      db:
        condition: service_healthy

volumes:
  db_data:
```

### 11.4 `src/index.php`

```php
<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'demo';
$user = getenv('DB_USER') ?: 'demo';
$pass = getenv('DB_PASS') ?: 'demo';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Crear tabla si no existe
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS personas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL
      )
    ");

    // Insertar datos si está vacía
    $count = (int)$pdo->query("SELECT COUNT(*) FROM personas")->fetchColumn();
    if ($count === 0) {
        $pdo->exec("
          INSERT INTO personas (nombre)
          VALUES ('Ada Lovelace'), ('Alan Turing')
        ");
    }

    // Traer filas
    $rows = $pdo->query("SELECT id, nombre FROM personas ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    http_response_code(500);
    die('Error de conexión: ' . htmlspecialchars($e->getMessage()));
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>PFO2 – Personas</title>
</head>
<body>
  <h1>Personas</h1>
  <ul>
    <?php foreach ($rows as $r): ?>
      <li>#<?= htmlspecialchars($r['id']) ?> – <?= htmlspecialchars($r['nombre']) ?></li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
```

---

## 12) Conclusiones

* Se cumplió la PFO: stack Web+DB en Docker, home conectada a MySQL, imagen **publicada** en Docker Hub y documentación completa.
* Se anexan evidencias de ejecución, capturas y archivos necesarios para reproducibilidad.
