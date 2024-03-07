<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once 'cabecera.php'; ?>
</head>
<body>
    <div class="container text-center">
        <div class="cuerpo">
            <h2>Iniciar Sesión</h2>
        </div>
        <form action="index.php?accion=iniciarSesion" method="post">
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required value="<?php echo $usuario; ?>">
            </div>
            <div class="form-group">
                <label for="contrasenia">Contraseña:</label>
                <input type="password" class="form-control" id="contrasenia" name="contrasenia" required value="<?php echo $contrasenia; ?>">
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="recuerdo" <?php echo isset($_COOKIE['recuerdo']) ? 'checked' : ''; ?>> Recuérdame</label>
            </div>
            <?php
            // Mostramos un mensaje de error si las credenciales son incorrectas
            if(isset($_GET['error']) && $_GET['error'] == "credenciales") {
                echo '<div class="alert alert-danger" style="margin-top:5px;">' . "Nombre de usuario o contraseña incorrectos." . '</div>';          
            }     
            // Mostramos un mensaje de error si se intenta acceder directamente sin iniciar sesión
            if (isset($_GET['error']) && $_GET['error'] == "fuera") {
                echo '<div class="alert alert-danger" style="margin-top:5px;">' . "No puedes acceder directamente en esta página sin loguearte.<br/>" . '</div>';          
            }
            ?> 
            <button type="submit" class="btn btn-primary" style="margin-top: 20px; cursor: pointer;">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>
