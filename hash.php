<?php
$password = 'Hola123*';
$password_encriptada = password_hash($password, PASSWORD_BCRYPT);
echo "Contraseña original: $password\n";
echo "Contraseña encriptada: $password_encriptada\n";
?>