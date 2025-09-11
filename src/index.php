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
