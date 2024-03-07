<!DOCTYPE html>
<html>
<head>
  <?php require_once 'cabecera.php'; ?>
</head>
<body class="cuerpo">
  <div class="centrar">  
    <div class="container centrar">
      <div>
        <!-- Enlace de regreso a la página de inicio -->
        <a href="index.php">Inicio</a>
        <!-- Enlace para cerrar sesión -->
        <a href="index.php?accion=cerrarSesion" style="float: right;">Cerrar sesión</a>
      </div>
      <div class="container cuerpo text-center centrar">   
        <p><h2>Añadir Tarea</h2></p>
        <?php echo $msgResultado; ?>
      </div>
      <?php foreach ($parametros["mensajes"] as $mensaje) : ?> 
        <!-- Mostramos los mensajes de alerta -->
        <div class="alert alert-<?= $mensaje["tipo"] ?>"><?= $mensaje["mensaje"] ?></div>
      <?php endforeach; ?>
      <form action="index.php?accion=anadirTarea" method="post" enctype="multipart/form-data">
        <!-- Campo oculto para almacenar el ID del usuario -->
        <input type="hidden" name="idusuario" value="<?php echo $_COOKIE['id_usuario']; ?>">
        <div class="form-group">
          <label for="titulo">Título:</label>
          <input type="text" class="form-control" name="titulo" required value="<?= $parametros["datos"]["titulo"] ?>">
        </div>
        <div class="form-group">
          <label for="descripcion">Descripción:</label>
          <div style="width: 100%;">
            <textarea id="descripcion" name="descripcion" style="width: 100%; min-height: 100px;"></textarea>
          </div>
        </div>
        <div class="form-group">
          <label for="categoria">Categoría:</label>
          <select name="categoria" class="form-control" required>
            <option value="">Seleccione una categoría</option>
            <?php foreach ($parametros["categorias"] as $categoria) : ?>
              <option value="<?= $categoria["IDCAT"] ?>"><?= $categoria["NOMBRECAT"] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="lugar">Lugar:</label>
          <input type="text" class="form-control" name="lugar" required>
        </div>
        <div class="form-group">
          <label for="prioridad">Prioridad:</label>
          <select name="prioridad" class="form-control" required>
            <option value="">Seleccione la prioridad</option>
            <option value="Alta">Alta</option>
            <option value="Media">Media</option>
            <option value="Baja">Baja</option>
          </select>
        </div>
        <div class="form-group">
          <label for="imagen">Imagen:</label>
          <input type="file" name="imagen" class="form-control" value="<?= $parametros["datos"]["imagen"] ?>">
        </div>
        <input type="submit" value="Guardar" name="submit" class="btn btn-primary" style="cursor: pointer;">
      </form>
    </div>
  </div>
  <script>
    // CKEditor
    ClassicEditor
      .create(document.querySelector('#descripcion'))
      .catch(error => {
          console.error(error);
    });
  </script>
</body>
</html>