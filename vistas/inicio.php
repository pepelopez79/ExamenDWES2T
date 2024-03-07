<!DOCTYPE html>
<html>
<head>
    <?php require_once 'cabecera.php'; ?>
</head>
<body class="cuerpo">
    <div class="container centrar">
        <div>
            <a href="index.php?accion=cerrarSesion" style="float: right;">Cerrar sesión</a>
        </div>
        <div class="container cuerpo text-center">    
            <p><h2>Gestión de Tareas</h2></p>
        </div>
        <ul>
            <li><a href="index.php?accion=listado">Listar tareas</a></li>
            <li><a href="index.php?accion=anadirTarea">Añadir tarea</a></li>
        </ul>
    </div>
</body>
</html>