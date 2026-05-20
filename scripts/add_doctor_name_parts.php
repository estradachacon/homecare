<?php
$mysqli = new mysqli('localhost','root','','homecaredb');
if ($mysqli->connect_error) {
    echo 'ERROR: '.$mysqli->connect_error."\n";
    exit(1);
}
$cols = [
    'nombre1 VARCHAR(120) NULL AFTER nombre',
    'nombre2 VARCHAR(120) NULL AFTER nombre1',
    'apellido1 VARCHAR(120) NULL AFTER nombre2',
    'apellido2 VARCHAR(120) NULL AFTER apellido1',
];
foreach ($cols as $col) {
    $parts = preg_split('/\s+/', $col);
    $name = $parts[0];
    $res = $mysqli->query("SHOW COLUMNS FROM doctores LIKE '$name'");
    if ($res && $res->num_rows > 0) {
        echo "SKIP doctores $name exists\n";
        continue;
    }
    $sql = "ALTER TABLE doctores ADD COLUMN $col";
    if (!$mysqli->query($sql)) {
        echo "ERROR adding $name: " . $mysqli->error . "\n";
    } else {
        echo "ADDED doctores $name\n";
    }
}
?>