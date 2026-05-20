<?php
$mysqli = new mysqli('localhost','root','','homecaredb');
if ($mysqli->connect_error) {
    echo 'ERROR: '.$mysqli->connect_error."\n";
    exit(1);
}
$sql = "CREATE TABLE IF NOT EXISTS tipo_notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(191) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if (!$mysqli->query($sql)) {
    echo 'ERROR creating table: ' . $mysqli->error . "\n";
    exit(1);
}
$defaults = [
    'Colocación de terapia',
    'Cambio 1',
    'Cambio 2',
    'Retiro',
];
foreach ($defaults as $d) {
    $stmt = $mysqli->prepare('SELECT id FROM tipo_notas WHERE nombre = ? LIMIT 1');
    $stmt->bind_param('s', $d);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $ins = $mysqli->prepare('INSERT INTO tipo_notas (nombre, activo) VALUES (?,1)');
        $ins->bind_param('s', $d);
        $ins->execute();
    }
}
echo "DONE\n";
?>