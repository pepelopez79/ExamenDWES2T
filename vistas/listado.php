<html>
<head>
    <?php require_once 'cabecera.php'; ?>
</head>
<body class="cuerpo">
    <div class="container centrar">
        <div>
            <a href="index.php">Inicio</a>
            <a href="index.php?accion=cerrarSesion" style="float: right;">Cerrar sesión</a>
        </div>
        <div class="container cuerpo text-center centrar">
            <p><h2>Lista de Tareas</h2></p>
        </div>
        <form action="index.php" method="get">
            <input type="hidden" name="accion" value="listado">
            <input type="text" name="titulo" placeholder="Buscar por título">
            <button type="submit">Buscar</button>
        </form>
        <form action="index.php" method="get">
            <input type="hidden" name="accion" value="listado">
            <label for="fecha_inicio">Fecha Inicial:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio">
            <label for="fecha_fin">Fecha Final:</label>
            <input type="date" id="fecha_fin" name="fecha_fin">
            <button type="submit">Buscar por fecha</button>
        </form>

        <!-- Mostrar las tareas obtenidas -->
        <ul>
            <?php foreach ($tareas as $tarea): ?>
                <li><?= $tarea['titulo'] ?> - <?= $tarea['fecha'] ?></li>
            <?php endforeach; ?>
        </ul>
        <!-- Modal de confirmación para eliminar -->
        <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-labelledby="confirmarEliminarModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        ¿Estás seguro de que deseas eliminar esta tarea?
                        <br>
                        (Pulsa fuera para cancelar)
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="confirmarEliminarBtn" style="cursor: pointer;">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Botón para generar PDF -->
        <div class="text-right">
            <button class="btn btn-secondary" onclick="generarPDF()" style="cursor: pointer; margin-bottom: 20px;">Imprimir</button>
        </div>
        <?php foreach ($parametros["mensajes"] as $mensaje) : ?>
            <!-- Mensajes de alerta -->
            <div class="alert alert-<?= $mensaje["tipo"] ?>"><?= $mensaje["mensaje"] ?></div>
        <?php endforeach; ?>
        <?php if (empty($parametros["datos"])) : ?>
    <div class="alert alert-info">No hay tareas disponibles.</div>
    <?php else : ?>
        <table class="table table-striped" id="pdfTable">
            <tr>
                <th>Nombre Categoría</th>
                <th>Título</th>
                <th>Descripción</th>
                <th>Imagen</th>
                <th>
                    <!-- Ordenar por fecha -->
                    <a href="index.php?accion=listado&orden=<?= ($orden == 'asc') ? 'desc' : 'asc' ?>">
                        Fecha
                        <?= ($orden == 'asc') ? '<span>&#9650;</span>' : '<span>&#9660;</span>' ?>
                    </a>
                </th>
                <th>Lugar</th>
                <th>Prioridad</th>
                <th>Operaciones</th>
            </tr>
            <?php foreach ($parametros["datos"] as $d) : ?>
                <tr>
                    <td><?= $d["Nombre Categoría"] ?></td>
                    <td><?= $d["TITULO"] ?></td>
                    <td><?= $d["DESCRIPCION"] ?></td>
                    <td>
                        <?php if ($d["IMAGEN"] !== NULL) : ?>
                            <img src="images/<?= $d['IMAGEN'] ?>" alt="Imagen" class="avatar-img" style="border-radius: 6%; width: 100px; height: 100px;">
                        <?php else : ?>
                            ----
                        <?php endif; ?>
                    </td>
                    <td><?= date("d/m/Y", strtotime($d["FECHA"])) ?> <br> <?= date("H:i:s", strtotime($d["FECHA"])) ?></td>
                    <td><?= $d["LUGAR"] ?></td>
                    <td><?= $d["PRIORIDAD"] ?></td>
                    <td>
                        <a href="index.php?accion=editarTarea&id=<?= $d['IDTAREA'] ?>">Editar</a><br><br>
                        <a href="index.php?accion=mostrarTarea&id=<?= $d['IDTAREA'] ?>">Detalle</a><br><br>
                        <a href="#" onclick="mostrarModalEliminar(<?= $d['IDTAREA'] ?>)">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    </div>
    <div class="pagination-container" id="pagination-container">
        <div class="arrow" onclick="window.location.href='index.php?accion=listado&pagina=<?php echo max($parametros['pagina'] - 1, 1); ?>&orden=<?php echo $parametros['orden']; ?>&regsxpag=<?php echo $parametros['regsxpag']; ?>'">
            ◂
        </div>
        <div class="text-center">
            <!-- Paginación -->
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $parametros['totalPaginas']; $i++) : ?>
                        <li class="page-item <?php echo ($i == $parametros['pagina']) ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?accion=listado&pagina=<?php echo $i; ?>&orden=<?php echo isset($_GET['orden']) ? $_GET['orden'] : 'asc'; ?>&regsxpag=<?php echo $parametros['regsxpag']; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <div class="arrow" onclick="window.location.href='index.php?accion=listado&pagina=<?php echo min($parametros['pagina'] + 1, $parametros['totalPaginas']); ?>&orden=<?php echo $parametros['orden']; ?>&regsxpag=<?php echo $parametros['regsxpag']; ?>'">
            ▸
        </div>
    </div>
    <script>
        // Función para generar el PDF
        function generarPDF() {
            try {
                var contenido = document.getElementById('pdfTable').cloneNode(true);
                var filas = contenido.querySelectorAll('tr');
                filas.forEach(function(fila) {
                    fila.removeChild(fila.lastElementChild);
                });

                var opciones = {
                    margin: 10,
                    filename: 'ListadoTareas.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                html2pdf().from(contenido).set(opciones).save();
            } catch (error) {
                alert("Error al generar el PDF.");
            }
        }

        var idEliminar = null;

        // Modal de confirmación para eliminar
        function mostrarModalEliminar(id) {
            idEliminar = id;
            $('#confirmarEliminarModal').modal('show');
        }

        // Clic en el botón de confirmación de eliminación
        $('#confirmarEliminarBtn').click(function() {
            if (idEliminar !== null) {
                eliminarTarea(idEliminar);
                idEliminar = null;
            }
        });

        function eliminarTarea(id) {
            window.location.href = 'index.php?accion=eliminarTarea&id=' + id;
        }
    </script>
</body>
</html>
