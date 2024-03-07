<?php

require_once 'controladores/controlador.php';
$controlador = new controlador();

// Verificamos si se proporcionó una acción a través de GET
if ($_GET && isset($_GET["accion"])) {
  $accion = filter_input(INPUT_GET, "accion", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

  // Verificamos si el método de la acción existe en el controlador
  if (method_exists($controlador, $accion)) {
      $controlador->$accion();
  } else {
      $controlador->index();
  }
} else {
  // Si no se proporciona ninguna acción, llamamos al método index por defecto
  $controlador->index();
}
