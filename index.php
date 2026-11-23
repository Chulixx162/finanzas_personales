<?php
// index.php (raíz del proyecto)
// La página de inicio vive en public/index.php. Quien entre a la raíz del sitio
// (http://localhost:8080/) llega allí, sin importar si la app está en la raíz del
// dominio o en una subcarpeta.

require_once __DIR__ . '/config/app.php';

redirigir('public/index.php');