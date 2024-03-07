<?php

class Modelo {
    private $conexion;
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db = "bdblog";

    // Constructor para inicializar la conexión a la base de datos
    public function __construct() {
        $this->conectar();
    }

    // Método para establecer la conexión con la base de datos
    private function conectar() {
        try {
            $this->conexion = new PDO("mysql:host=$this->host;dbname=$this->db", $this->user, $this->pass);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return true;
        } catch (PDOException $ex) {
            return $ex->getMessage();
        }
    }

    // Método para iniciar sesión
    public function iniciarSesion($usuario, $contrasenia) {
        try {
            $consulta = $this->conexion->prepare("SELECT * FROM usuarios WHERE NICK = :usuario AND CONTRASENIA = :contrasenia");
            $consulta->bindParam(':usuario', $usuario);
            $consulta->bindParam(':contrasenia', $contrasenia);
            $consulta->execute();

            return $consulta->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return false;
        }
    }

    // Método para buscar tareas por título y ordenarlas por fecha y hora
    public function buscarTareasPorTitulo($titulo) {
        $sql = "SELECT * FROM tareas WHERE titulo LIKE :titulo ORDER BY fecha ASC";
        $query = $this->conexion->prepare($sql);
        $query->execute(['titulo' => "%$titulo%"]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }    

    // Método para buscar tareas por fecha
    public function buscarTareasPorRangoFechas($fecha_inicio, $fecha_fin) {
        $sql = "SELECT * FROM tareas WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin ORDER BY fecha, hora ASC";
        $query = $this->conexion->prepare($sql);
        $query->execute(['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // Método para obtener el listado de tareas
    public function listado($orden) {
        $return = [
            "correcto" => false,
            "datos" => null,
            "error" => null
        ];
    
        try {
            // Consulta SQL para obtener las tareas ordenadas por fecha
            $sql = "SELECT e.*, u.NICK AS 'Nick Usuario', c.NOMBRECAT AS 'Nombre Categoría' 
                FROM tareas e
                INNER JOIN usuarios u ON e.IDUSUARIO = u.IDUSER
                INNER JOIN categorias c ON e.IDCATEGORIA = c.IDCAT
                WHERE DATE(e.FECHA) = CURDATE() -- Filtrado por fecha actual
                ORDER BY e.FECHA $orden";
            $resultsquery = $this->conexion->query($sql);

            if ($resultsquery) {
                $return["correcto"] = true;
                $return["datos"] = $resultsquery->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $ex) {
            $return["error"] = $ex->getMessage();
        }
    
        return $return;
    }

    // Método para obtener el listado de tareas por usuario
    public function listadoPorUsuario($idUsuario, $orden) {
        $return = [
            "correcto" => false,
            "datos" => null,
            "error" => null
        ];
    
        try {
            // Consulta SQL para obtener las tareas de un usuario ordenadas por fecha
            $sql = "SELECT e.*, u.NICK AS 'Nick Usuario', c.NOMBRECAT AS 'Nombre Categoría' 
                    FROM tareas e
                    INNER JOIN usuarios u ON e.IDUSUARIO = u.IDUSER
                    INNER JOIN categorias c ON e.IDCATEGORIA = c.IDCAT
                    WHERE e.IDUSUARIO = :idUsuario
                    WHERE DATE(e.FECHA) = CURDATE()
                    ORDER BY e.FECHA $orden";
            $query = $this->conexion->prepare($sql);
            // Ejecutar la consulta con el id de usuario como parámetro
            $query->execute(['idUsuario' => $idUsuario]);

            if ($query) {
                $return["correcto"] = true;
                $return["datos"] = $query->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $ex) {
            $return["error"] = $ex->getMessage();
        }
    
        return $return;
    }

    // Método para obtener el listado de tareas paginado
    public function listadoPaginado($orden, $regsxpag, $pagina) {
        $return = [
            "correcto" => false,
            "datos" => null,
            "error" => null,
            "totalPaginas" => 0
        ];
    
        try {
            // Consulta SQL para contar el número total de tareas
            $sqlCount = "SELECT COUNT(*) AS total FROM tareas";
            $queryCount = $this->conexion->query($sqlCount);
            $totalRegistros = $queryCount->fetchColumn();
            // Calcular el número total de páginas
            $return["totalPaginas"] = ceil($totalRegistros / $regsxpag);
    
            // Calcular el desplazamiento para la paginación
            $offset = ($pagina - 1) * $regsxpag;
    
            // Consulta SQL para obtener las tareas paginadas
            $sql = "SELECT e.*, u.NICK AS 'Nick Usuario', c.NOMBRECAT AS 'Nombre Categoría' 
                    FROM tareas e
                    INNER JOIN usuarios u ON e.IDUSUARIO = u.IDUSER
                    INNER JOIN categorias c ON e.IDCATEGORIA = c.IDCAT
                    WHERE DATE(e.FECHA) = CURDATE()
                    ORDER BY e.FECHA $orden
                    LIMIT $offset, $regsxpag";
            $query = $this->conexion->query($sql);

            if ($query) {
                $return["correcto"] = true;
                $return["datos"] = $query->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $ex) {
            $return["error"] = $ex->getMessage();
        }
    
        return $return;
    }

    // Método para obtener el listado de tareas por usuario paginado
    public function listadoPorUsuarioPaginado($idUsuario, $orden, $regsxpag, $pagina) {
        $return = [
            "correcto" => false,
            "datos" => null,
            "error" => null,
            "totalPaginas" => 0
        ];
    
        try {
            // Consulta SQL para contar el número total de tareas de un usuario
            $sqlCount = "SELECT COUNT(*) AS total FROM tareas WHERE IDUSUARIO = :idUsuario";
            $queryCount = $this->conexion->prepare($sqlCount);
            $queryCount->execute(['idUsuario' => $idUsuario]);
            $totalRegistros = $queryCount->fetchColumn();
            // Calcular el número total de páginas
            $return["totalPaginas"] = ceil($totalRegistros / $regsxpag);
    
            // Calcular el desplazamiento para la paginación
            $offset = ($pagina - 1) * $regsxpag;
    
            // Consulta SQL para obtener las tareas de un usuario paginadas
            $sql = "SELECT e.*, u.NICK AS 'Nick Usuario', c.NOMBRECAT AS 'Nombre Categoría' 
                    FROM tareas e
                    INNER JOIN usuarios u ON e.IDUSUARIO = u.IDUSER
                    INNER JOIN categorias c ON e.IDCATEGORIA = c.IDCAT
                    WHERE e.IDUSUARIO = :idUsuario
                    WHERE DATE(e.FECHA) = CURDATE()
                    ORDER BY e.FECHA $orden
                    LIMIT $offset, $regsxpag";
            $query = $this->conexion->prepare($sql);
            // Ejecutar la consulta con el id de usuario como parámetro
            $query->execute(['idUsuario' => $idUsuario]);

            if ($query) {
                $return["correcto"] = true;
                $return["datos"] = $query->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $ex) {
            $return["error"] = $ex->getMessage();
        }
    
        return $return;
    }

    // Método para añadir una nueva tarea
    public function anadirTarea($datos) {
        $return = [
            "correcto" => false,
            "error" => null
        ];

        try {
            // Iniciar transacción
            $this->conexion->beginTransaction();

            // Consulta SQL para insertar una nueva tarea en la base de datos
            $sql = "INSERT INTO tareas(IDUSUARIO, IDCATEGORIA, TITULO, IMAGEN, DESCRIPCION, LUGAR, PRIORIDAD)
                    VALUES (:idusuario, :idcategoria, :titulo, :imagen, :descripcion, :lugar, :prioridad)";

            $query = $this->conexion->prepare($sql);

            $query->execute([
                'idusuario' => $datos["idusuario"],
                'idcategoria' => $datos["idcategoria"],
                'titulo' => $datos["titulo"],
                'imagen' => $datos["imagen"],
                'descripcion' => $datos["descripcion"],
                'lugar' => $datos["lugar"],
                'prioridad' => $datos["prioridad"]
            ]); 

            if ($query) {
                // Confirmar transacción
                $this->conexion->commit();
                $return["correcto"] = true;
            }
        } catch (PDOException $ex) {
            // Revertir transacción en caso de error
            $this->conexion->rollback();
            $return["error"] = $ex->getMessage();
        }

        return $return;
    }

    // Método para editar una tarea existente
    public function editarTarea($datos) {
        $return = [
            "correcto" => false,
            "error" => null
        ];

        try {
            // Iniciar transacción
            $this->conexion->beginTransaction();

            // Consulta SQL para actualizar una tarea en la base de datos
            $sql = "UPDATE tareas SET TITULO = :nuevoTitulo, DESCRIPCION = :nuevaDescripcion, IDCATEGORIA = :nuevaCategoria, LUGAR = :nuevoLugar, PRIORIDAD = :nuevaPrioridad, IMAGEN = :nuevaImagen WHERE IDTAREA = :IDTAREA";
            $query = $this->conexion->prepare($sql);

            $query->execute([
                'IDTAREA' => $datos["IDTAREA"],
                'nuevoTitulo' => $datos["nuevoTitulo"],
                'nuevaCategoria' => $datos["nuevaCategoria"],
                'nuevaDescripcion' => $datos["nuevaDescripcion"],
                'nuevoLugar' => $datos["nuevoLugar"],
                'nuevaPrioridad' => $datos["nuevaPrioridad"],
                'nuevaImagen' => $datos["nuevaImagen"]
            ]);

            if ($query) {
                // Confirmar transacción
                $this->conexion->commit();
                $return["correcto"] = true;
            } else {
                // Revertir transacción en caso de error
                $this->conexion->rollback();
                $return["error"] = "No se pudo actualizar la tarea.";
            }
        } catch (PDOException $ex) {
            // Revertir transacción en caso de error
            $this->conexion->rollback();
            $return["error"] = $ex->getMessage();
        }

        return $return;
    }

    // Método para eliminar una tarea
    public function eliminarTarea($id) {
        $return = [
            "correcto" => false,
            "error" => null
        ];
    
        if ($id && is_numeric($id)) {
            try {
                // Iniciar transacción
                $this->conexion->beginTransaction();
                // Consulta SQL para eliminar una tarea de la base de datos
                $sql = "DELETE FROM tareas WHERE IDTAREA = :id";
                $query = $this->conexion->prepare($sql);
                // Ejecutar la consulta con el id de tarea como parámetro
                $query->execute(['id' => $id]);

                if ($query) {
                    // Confirmar transacción
                    $this->conexion->commit();
                    $return["correcto"] = true;
                }
            } catch (PDOException $ex) {
                // Revertir transacción en caso de error
                $this->conexion->rollback();
                $return["error"] = $ex->getMessage();
            }
        }
    
        return $return;
    }   
    
    // Método para mostrar los detalles de una tarea
    public function mostrarTarea($IDTAREA) {
        $return = [
            "correcto" => false,
            "datos" => null,
            "error" => null
        ];
    
        try {
            // Consulta SQL para obtener los detalles de una tarea específica
            $sql = "SELECT e.*, u.NICK AS 'NICK', c.NOMBRECAT AS 'NOMBRECAT'
                    FROM tareas e
                    INNER JOIN usuarios u ON e.IDUSUARIO = u.IDUSER
                    INNER JOIN categorias c ON e.IDCATEGORIA = c.IDCAT
                    WHERE IDTAREA = :IDTAREA";
            $query = $this->conexion->prepare($sql);
            // Ejecutar la consulta con el id de tarea como parámetro
            $query->execute(['IDTAREA' => $IDTAREA]);
    
            if ($query) {
                $return["correcto"] = true;
                $return["datos"] = $query->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $ex) {
            $return["error"] = $ex->getMessage();
        }
    
        return $return;
    }

    // Método para obtener todas las categorías
    public function obtenerCategorias() {
        try {
            if (!$this->conexion) {
                $this->conectar();
            }
    
            // Consulta SQL para obtener todas las categorías
            $sql = "SELECT IDCAT, NOMBRECAT FROM CATEGORIAS";
            $query = $this->conexion->query($sql);
    
            // Obtener y devolver las categorías como un array asociativo
            $categorias = $query->fetchAll(PDO::FETCH_ASSOC);
    
            return $categorias;
        } catch (PDOException $ex) {
            return array();
        }
    }
}
