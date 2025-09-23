# DevOps PFO 2 ComD Grupo El Quinto Elemento

> **Materia:** Seminario de Actualización DevOps (IFTS 29)
> **Entrega:** PFO Nº2 – Contenedores, imágenes y publicación
> **Autor/es:** Clausi Damian – Descosido Cristian – Gill Cesar Antonio (Grupo “El Quinto Elemento”)

---

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

-- Agregar más registros para pruebas
INSERT INTO personas (nombre) VALUES 
('Grace Hopper'),
('Margaret Hamilton'),
('Katherine Johnson');

-- Verificar los nuevos datos
SELECT id, nombre FROM personas ORDER BY id;
```

### 9.3 Evidencias

Las capturas `mysql1.png`, `mysql2.png` y `mysql3.png` muestran:

* Conexión exitosa desde MySQL Workbench
* Visualización de la base de datos y tabla
* Ejecución de consultas SQL y resultados

---

## 10) Problemas comunes y solución

* **`port is already allocated`** → Cambiar puertos en `docker-compose.yml` (ej.: `8081:80`, `3307:3306`) y volver a levantar.
* **La web no conecta a MySQL al inicio** → Esperar unos segundos a que `db` quede `healthy`; revisar `docker-compose logs db`.
* **`pull access denied / manifest unknown`** → Verificar que la **tag** publicada exista (ej.: `1.0`) y que el repo no sea privado o con credenciales inválidas.
* **WSL sin docker CLI** → Usar el docker de Windows vía alias o integrar WSL con Docker Desktop.

---

## 10) Publicación en GitHub

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

    // Crear tabla y datos si no existen
    $pdo->exec("CREATE TABLE IF NOT EXISTS personas (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL)");
    $count = (int)$pdo->query("SELECT COUNT(*) FROM personas")->fetchColumn();
    if ($count === 0) {
        $pdo->exec("INSERT INTO personas (nombre) VALUES ('Ada Lovelace'), ('Alan Turing')");
    }

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
