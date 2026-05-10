<?php
require 'config/Database.php';
$conn = Database::getInstance();
$cols = $conn->query('DESCRIBE utilisateurs')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' ' . $c['Type'] . ' ' . $c['Null'] . ' ' . ($c['Default'] === null ? 'NULL' : $c['Default']) . "\n";
}
?>