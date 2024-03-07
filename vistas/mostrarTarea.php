<!DOCTYPE html>
<html>
<head>
    <?php require_once 'cabecera.php'; ?>
</head>
<body class="cuerpo">
    <div class="container centrar">
        <div>
            <a href="index.php?accion=listado">Volver</a>
            <a href="index.php?accion=cerrarSesion" style="float: right;">Cerrar sesión</a>
        </div>
        <div class="container cuerpo text-center centrar">
            <p><h2>Detalle de Tarea</h2></p>
        </div>

        <?php foreach ($parametros["mensajes"] as $mensaje): ?>
            <div class="alert alert-<?= $mensaje['tipo'] ?>" role="alert">
            <!-- Mostramos los mensajes de alerta -->
            <?= $mensaje['mensaje'] ?> 
            </div>
        <?php endforeach; ?>

        <div class="table-container">
            <table class="table" style="width: 70%; float: left; margin-top: 50px">
                <tr>
                    <th>ID</th>
                    <td><?= $parametros["tarea"]["IDTAREA"]; ?></td>
                </tr>
                <tr>
                    <th>Título</th>
                    <td><?= $parametros["tarea"]["TITULO"]; ?></td>
                </tr>
                <tr>
                    <th>Descripción</th>
                    <td><?= $parametros["tarea"]["DESCRIPCION"]; ?></td>
                </tr>
                <tr>
                    <th>Nombre Categoría</th>
                    <td><?= $parametros["tarea"]["NOMBRECAT"]; ?></td>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <td><?= $parametros["tarea"]["FECHA"]; ?></td>
                </tr>
                <tr>
                    <th>Lugar</th>
                    <td><?= $parametros["tarea"]["LUGAR"]; ?></td>
                </tr>
                <tr>
                    <th>Prioridad</th>
                    <td><?= $parametros["tarea"]["PRIORIDAD"]; ?></td>
                </tr>
            </table>
            <?php if ($parametros["tarea"]["IMAGEN"] !== NULL) : ?>
                <div class="image-container" style="float: right; width: 30%;">
                    <img src="images/<?= $parametros["tarea"]["IMAGEN"] ?>" alt="Imagen" class="avatar-img" style="border-radius: 3%; width: 100%; height: auto; margin-left: 20px; margin-top: 60px">
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
