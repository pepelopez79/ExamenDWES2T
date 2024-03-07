<?php

// Incluimos el modelo para interactuar con la base de datos
require_once 'modelos/modelo.php';

class controlador {
    private $modelo;
    private $mensajes;
  
    public function __construct() {
        // Inicializamos el modelo y el array de mensajes
        $this->modelo = new modelo();
        $this->mensajes = [];
    }

    // Método para mostrar la página principal
    public function index() {
        session_start();

        // Verificamos si el usuario ha iniciado sesión
        if (!isset($_SESSION['perfil'])) {
            // Redirige a la página de inicio de sesión si el usuario no ha iniciado sesión
            header('Location: index.php?accion=iniciarSesion');
            exit();
        }

        $parametros = [
            "tituloventana" => "Gestión Tareas"
        ];

        // Incluimos la vista de inicio
        include_once 'vistas/inicio.php';
    }

    // Método para iniciar sesión
    public function iniciarSesion() {
        // Verificamos si se ha enviado un formulario de inicio de sesión
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $usuario = $_POST["usuario"];
            $contrasenia = $_POST["contrasenia"];
    
            // Intentamos iniciar sesión con los datos proporcionados
            $resultado = $this->modelo->iniciarSesion($usuario, $contrasenia);
    
            if ($resultado) {
                // Iniciamos la sesión y establecemos las variables de sesión
                session_start();
                $_SESSION["perfil"] = $resultado["ROL"];
                $_SESSION["usuario"] = $resultado["NICK"];
                setcookie("id_usuario", $resultado["IDUSER"], time() + (86400 * 30), "/");
                
                // Verificamos si se seleccionó la opción de recordar usuario y contraseña
                if (isset($_POST['recuerdo']) && $_POST['recuerdo'] == 'on') {
                    // Guardamos las credenciales en cookies
                    setcookie("usuario", $usuario, time() + (86400 * 30), "/");
                    setcookie("contrasenia", $contrasenia, time() + (86400 * 30), "/");
                    setcookie("recuerdo", true, time() + (86400 * 30), "/");
                } else {
                    // Borramos las cookies de recordar usuario y contraseña
                    setcookie("usuario", "", time() - 3600, "/");
                    setcookie("contrasenia", "", time() - 3600, "/");
                    setcookie("recuerdo", "", time() - 3600, "/");
                }
    
                // Redirigimos a la página principal después de iniciar sesión
                header('Location: index.php');
                exit();
            } else {
                // Redirigimos a la página de inicio de sesión con un mensaje de error si las credenciales son incorrectas
                header('Location: index.php?accion=iniciarSesion&error=credenciales');
                exit();
            }
        } else {
            // Si no se envió un formulario, mostramos la página de inicio de sesión
            $usuario = isset($_COOKIE['usuario']) ? $_COOKIE['usuario'] : '';
            $contrasenia = isset($_COOKIE['contrasenia']) ? $_COOKIE['contrasenia'] : '';

            // Parámetros para la vista de inicio de sesión
            $parametros = [
                "tituloventana" => "Iniciar Sesión",
                "mensajes" => $this->mensajes,
            ];
    
            // Incluimos la vista de inicio de sesión
            include_once 'vistas/iniciarSesion.php';
        }
    }

    // Método para cerrar sesión
    public function cerrarSesion() {
        session_start();
        
        // Borramos todas las variables de sesión
        $_SESSION = array();
    
        // Destruimos la sesión
        session_destroy();
    
        // Redirigimos a la página de inicio de sesión después de cerrar sesión
        header('Location: index.php?accion=iniciarSesion');
        exit();
    }    

    // Método para buscar tareas por título y ordenarlas por fecha y hora
    public function buscarTareas() {
        $titulo = $_GET['titulo'];
        $tareas = $this->modelo->buscarTareasPorTitulo($titulo);
        include_once 'vistas/listado.php';
    }

    //  Método para buscar tareas por fechas
    public function buscarTareasPorRangoFechas() {
        // Obtener fechas enviadas por el usuario
        $fecha_inicio = $_GET['fecha_inicio'];
        $fecha_fin = $_GET['fecha_fin'];
    
        // Llamar al método en el modelo para buscar tareas por rango de fechas
        $tareas = $this->modelo->buscarTareasPorRangoFechas($fecha_inicio, $fecha_fin);
    
        // Incluir la vista para mostrar el listado de tareas encontradas
        include_once 'vistas/listado.php';
    }
    
    // Método para mostrar el listado de tareas
    public function listado() {
        session_start();
    
        // Verificamos si el usuario ha iniciado sesión
        if (!isset($_SESSION['perfil'])) {
            // Redirigimos a la página de inicio de sesión si el usuario no ha iniciado sesión
            header('Location: index.php?accion=iniciarSesion&error=fuera');
            exit();
        }
    
        // Obtenemos los parámetros de orden, página y registros por página
        $orden = isset($_GET['orden']) && in_array($_GET['orden'], ['asc', 'desc']) ? $_GET['orden'] : 'asc';
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $regsxpag = isset($_GET['regsxpag']) ? intval($_GET['regsxpag']) : 5;
    
        // Obtenemos el listado de tareas según el perfil del usuario
        if ($_SESSION['perfil'] == 'admin') {
            $resultModelo = $this->modelo->listadoPaginado($orden, $regsxpag, $pagina);
        } elseif ($_SESSION['perfil'] == 'user') {
            $idUsuario = $_COOKIE['id_usuario'];
            $resultModelo = $this->modelo->listadoPorUsuarioPaginado($idUsuario, $orden, $regsxpag, $pagina);
        }
    
        $parametros = [
            "tituloventana" => "Listar Tareas",
            "datos" => NULL,
            "mensajes" => [],
            "orden" => $orden,
            "pagina" => $pagina,
            "regsxpag" => $regsxpag,
            "totalPaginas" => 0
        ];
    
        // Verificamos si se obtuvo correctamente el listado de tareas
        if ($resultModelo["correcto"]) {
            // Asignamos los datos y el total de páginas obtenidos del modelo a los parámetros
            $parametros["datos"] = $resultModelo["datos"];
            $parametros["totalPaginas"] = $resultModelo["totalPaginas"];

            $this->mensajes[] = [
                "tipo" => "success",
                "mensaje" => "El listado se realizó correctamente"
            ];
        } else {
            // Agrega un mensaje de error al array de mensajes si no se pudo obtener el listado de tareas
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "El listado no pudo realizarse correctamente<br/>({$resultModelo["error"]})"
            ];
        }
    
        // Incluimos la vista de listado de tareas
        include_once 'vistas/listado.php';
    }    
    
    // Método para añadir una nueva tarea
    public function anadirTarea() {
        $errores = array();
        
        // Obtenemos las categorías para el formulario de añadir tarea
        $categorias = $this->modelo->obtenerCategorias();
    
        // Verificamos si se ha enviado un formulario de añadir tarea
        if (isset($_POST) && !empty($_POST) && isset($_POST['submit'])) {
            $idusuario = $_POST['idusuario'];
            $idcategoria = $_POST['categoria'];
            $titulo = $_POST['titulo'];
            $descripcion = $_POST['descripcion'];
            $imagen = NULL;
            $lugar = isset($_POST['lugar']) ? $_POST['lugar'] : null; // Obtener el valor del campo de lugar
            $prioridad = isset($_POST['prioridad']) ? $_POST['prioridad'] : null; // Obtener el valor del campo de prioridad
    
            // Verificamos si se ha adjuntado una imagen
            if (isset($_FILES["imagen"]) && (!empty($_FILES["imagen"]["tmp_name"]))) {
                // Verificamos si existe el directorio de imágenes, si no, lo creamos
                if (!is_dir("images")) {
                    $dir = mkdir("images", 0777, true);
                } else {
                    $dir = true;
                }
                if ($dir) {
                    // Generamos un nombre único para la imagen y la movemos al directorio de imágenes
                    $nombreImagen = time() . "-" . $_FILES["imagen"]["name"];
                    $movimiento = move_uploaded_file($_FILES["imagen"]["tmp_name"], "images/" . $nombreImagen);
                    if ($movimiento) {
                        $imagen = $nombreImagen;
                    } else {
                        $this->mensajes[] = [
                            "tipo" => "danger",
                            "mensaje" => "Error: La imagen no se pudo cargar correctamente."
                        ];
                        $errores["imagen"] = "Error: La imagen no se pudo cargar correctamente.";
                    }
                }
            }
    
            // Verificamos si no hay errores de validación
            if (count($errores) == 0) {
                $resultModelo = $this->modelo->anadirTarea([
                    'idusuario' => $idusuario,
                    'idcategoria' => $idcategoria,
                    'titulo' => $titulo,
                    'imagen' => $imagen,
                    'descripcion' => $descripcion,
                    'lugar' => $lugar,
                    'prioridad' => $prioridad
                ]);
                // Verificamos si se añadió correctamente la tarea
                if ($resultModelo["correcto"]) {
                    $this->mensajes[] = [
                        "tipo" => "success",
                        "mensaje" => "La tarea se ha agregado correctamente."
                    ];
                } else {
                    $this->mensajes[] = [
                        "tipo" => "danger",
                        "mensaje" => "La tarea no se ha podido añadir correctamente." . $resultModelo["error"]
                    ];
                }
            } else {
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "Error en los datos de la tarea."
                ];
            }
        }
    
        $parametros = [
            "tituloventana" => "Agregar Tarea",
            "mensajes" => $this->mensajes,
            "categorias" => $categorias
        ];
    
        // Incluimos la vista de añadir tarea
        include_once 'vistas/anadirTarea.php';
    }    

    // Método para editar una tarea
    public function editarTarea() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Obtenemos el ID de la tarea a editar
            $IDTAREA = $_POST['IDTAREA'];
            
            // Obtenemos los detalles de la tarea a editar
            $resultadoTarea = $this->modelo->mostrarTarea($IDTAREA);
            if (!$resultadoTarea["correcto"]) {
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "No se pudo obtener la tarea existente para actualizarla."
                ];
                // Redirigimos al listado de tareas
                $this->listado();
                return;
            }

            // Obtenemos la imagen actual de la tarea
            $imagen = $resultadoTarea['datos']['IMAGEN'];

            if (isset($_FILES["nuevaImagen"]) && (!empty($_FILES["nuevaImagen"]["tmp_name"]))) {
                if (!is_dir("images")) {
                    $dir = mkdir("images", 0777, true);
                } else {
                    $dir = true;
                }
                if ($dir) {
                    $nombreImagen = time() . "-" . $_FILES["nuevaImagen"]["name"];
                    $movimiento = move_uploaded_file($_FILES["nuevaImagen"]["tmp_name"], "images/" . $nombreImagen);
                    if ($movimiento) {
                        $imagen = $nombreImagen;
                    } else {
                        $this->mensajes[] = [
                            "tipo" => "danger",
                            "mensaje" => "Error: La imagen no se pudo cargar correctamente."
                        ];
                        $errores["nuevaImagen"] = "Error: La imagen no se pudo cargar correctamente.";
                    }
                }
            }       

            $datos = [
                "IDTAREA" => $_POST['IDTAREA'],
                "nuevoTitulo" => $_POST['nuevoTitulo'],
                "nuevaCategoria" => $_POST['nuevaCategoria'],
                "nuevaDescripcion" => $_POST['nuevaDescripcion'],
                "nuevoLugar" => $_POST['nuevoLugar'],
                "nuevaPrioridad" => $_POST['nuevaPrioridad'], // Nuevo campo para prioridad
                "nuevaImagen" => $imagen
            ];

            $resultado = $this->modelo->editarTarea($datos);

            if ($resultado["correcto"]) {
                $this->mensajes[] = [
                    "tipo" => "success",
                    "mensaje" => "Tarea actualizada correctamente."
                ];
            } else {
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => $resultado["error"]
                ];
            }

            // Redirigimos al listado de tareas
            $this->listado();
        } elseif (isset($_GET['id'])) {
            // Si no se envió un formulario pero se proporcionó un ID de tarea, mostramos el formulario de edición
            $IDTAREA = $_GET['id'];
            
            // Obtenemos los detalles de la tarea a editar
            $resultadoTarea = $this->modelo->mostrarTarea($IDTAREA);

            if ($resultadoTarea["correcto"]) {
                $tarea = $resultadoTarea["datos"];
                $categorias = $this->modelo->obtenerCategorias();

                $parametros = [
                    "tituloventana" => "Editar Tarea",
                    "tarea" => $tarea,
                    "categorias" => $categorias,
                    "mensajes" => []
                ];

                // Incluimos la vista de editar tarea
                include_once 'vistas/editarTarea.php';
            } else {
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "Error al obtener los detalles de la tarea."
                ];

                // Redirigimos al listado de tareas
                $this->listado();
            }
        } else {
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "ID de tarea no proporcionado."
            ];

            // Redirigimos al listado de tareas
            $this->listado();
        }
    }

    // Método para eliminar una tarea
    public function eliminarTarea() {
        // Verificamos si se proporcionó un ID de tarea válido
        if (isset($_GET['id']) && (is_numeric($_GET['id']))) {
            // Obtenemos el ID de la tarea a eliminar
            $id = $_GET["id"];
            // Intentamos eliminar la tarea utilizando el modelo
            $resultModelo = $this->modelo->eliminarTarea($id);
            if ($resultModelo["correcto"]) :
                $this->mensajes[] = [
                    "tipo" => "success",
                    "mensaje" => "Se eliminó correctamente la tarea con ID $id"
                ];
            else :
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "La eliminación de la tarea no se realizó correctamente<br/>({$resultModelo["error"]})"
                ];
            endif;
        } else {
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "No se pudo acceder al ID de la tarea a eliminar"
            ];
        }

        // Redirigimos al listado de tareas
        $this->listado();
    }
    
    // Método para mostrar los detalles de una tarea
    public function mostrarTarea() {
        if (isset($_GET['id'])) {
            // Obtenemos el ID de la tarea
            $IDTAREA = $_GET['id'];
            
            // Obtenemos los detalles de la tarea
            $resultado = $this->modelo->mostrarTarea($IDTAREA);
    
            if ($resultado["correcto"]) {
                $tarea = $resultado["datos"];

                $parametros = [
                    "tituloventana" => "Detalle Tarea",
                    "tarea" => $tarea,
                    "mensajes" => []
                ];

                // Incluimos la vista de mostrar tarea
                include_once 'vistas/mostrarTarea.php';
            } else {
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "Error al obtener los detalles de la tarea."
                ];

                // Redirigimos al listado de tareas
                $this->listado();
            }
        } else {
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "ID de tarea no proporcionado."
            ];

            // Redirigimos al listado de tareas
            $this->listado();
        }
    }
}