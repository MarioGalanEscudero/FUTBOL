<?php
// 1. Incluimos la conexión a la base de datos
require_once 'config/conexion.php';

$mensaje = "";

// 2. LÓGICA PARA GUARDAR EL EJERCICIO (Si se envía el formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $nombre = trim($_POST['nombre_ejercicio']);
    $tipo = $_POST['tipo'];
    $descripcion = trim($_POST['descripcion']);
    
    // Tratamiento de la imagen
    $nombre_imagen = null;
    $ruta_destino = null;

    if (isset($_FILES['imagen_ejercicio']) && $_FILES['imagen_ejercicio']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['imagen_ejercicio']['tmp_name'];
        $file_name = $_FILES['imagen_ejercicio']['name'];
        
        // Extraemos la extensión del archivo para renombrarlo y evitar duplicados
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $formatos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array(strtolower($ext), $formatos_permitidos)) {
            // Renombramos el archivo de forma única: ej. ejer_171738291.jpg
            $nuevo_nombre_img = "ejer_" . time() . "." . $ext;
            $ruta_destino = "img/ejercicios/" . $nuevo_nombre_img;

            // Movemos el archivo temporal a nuestra carpeta definitiva
            if (!move_uploaded_file($file_tmp, $ruta_destino)) {
                $mensaje = "<p class='alerta-error'>Error al mover la imagen a la carpeta del servidor.</p>";
            }
        } else {
            $mensaje = "<p class='alerta-error'>Formato de imagen no válido (Solo JPG, PNG, WEBP o GIF).</p>";
        }
    }

    // Si no ha habido errores de imagen previos, guardamos en la base de datos
    if (empty($mensaje) && !empty($nombre) && !empty($tipo) && !empty($descripcion)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ejercicios (nombre_ejercicio, tipo, descripcion, ruta_imagen) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $tipo, $descripcion, $ruta_destino]);
            $mensaje = "<p class='alerta-exito'>¡Ejercicio guardado con éxito en la biblioteca!</p>";
        } catch (\PDOException $e) {
            $mensaje = "<p class='alerta-error'>Error en la base de datos: " . $e->getMessage() . "</p>";
        }
    } elseif (empty($mensaje)) {
        $mensaje = "<p class='alerta-error'>Por favor, rellena todos los campos obligatorios.</p>";
    }
}

// 3. LÓGICA PARA OBTENER LOS EJERCICIOS REGISTRADOS
try {
    $stmt = $pdo->query("SELECT * FROM ejercicios ORDER BY id_ejercicio DESC");
    $ejercicios = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Error al cargar los ejercicios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenamientos - App Fútbol</title>
    <style>
        :root {
            --azul-oscuro: #1A365D;
            --azul-medio: #3182CE;
            --azul-claro: #EBF8FF;
            --blanco: #FFFFFF;
            --gris-fondo: #F4F7F6;
            --gris-texto: #2D3748;
            --tag-fisica: #ED8936;
            --tag-balon: #38A169;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--gris-fondo); color: var(--gris-texto); padding-bottom: 40px; }
        
        header {
            background-color: var(--azul-oscuro);
            color: var(--blanco);
            text-align: center;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        header h1 { font-size: 1.5rem; }

        .contenedor { max-width: 500px; margin: 0 auto; padding: 15px; }

        .tarjeta {
            background: var(--blanco);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .tarjeta h2 {
            color: var(--azul-oscuro);
            font-size: 1.2rem;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--azul-claro);
            padding-bottom: 5px;
        }

        .grupo-form { margin-bottom: 15px; }
        .grupo-form label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; }
        
        .grupo-form input[type="text"], 
        .grupo-form select, 
        .grupo-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #CBD5E0;
            border-radius: 8px;
            font-size: 1rem;
        }

        .grupo-form textarea { resize: vertical; height: 100px; }
        
        /* Contenedores de opciones tipo Radio grandes para móvil */
        .opciones-tipo {
            display: flex;
            gap: 10px;
        }
        .opciones-tipo label {
            flex: 1;
            text-align: center;
            background: var(--gris-fondo);
            border: 2px solid #CBD5E0;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .opciones-tipo input[type="radio"] { display: none; }
        .opciones-tipo input[type="radio"]:checked + label {
            border-color: var(--azul-medio);
            background-color: var(--azul-claro);
            color: var(--azul-oscuro);
        }

        .btn-principal {
            width: 100%;
            background-color: var(--azul-medio);
            color: var(--blanco);
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-principal:active { background-color: var(--azul-oscuro); }

        .alerta-exito { color: #38A169; font-weight: bold; margin-bottom: 15px; text-align: center; }
        .alerta-error { color: #E53E3E; font-weight: bold; margin-bottom: 15px; text-align: center; }

        /* LISTADO DE EJERCICIOS EXCLUSIVO MÓVIL */
        .card-ejercicio {
            background: var(--blanco);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .img-ejercicio {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background-color: #E2E8F0;
        }

        .contenido-ejercicio { padding: 15px; }
        
        .tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            color: white;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .tag.fisica { background-color: var(--tag-fisica); }
        .tag.balon { background-color: var(--tag-balon); }

        .contenido-ejercicio h3 { font-size: 1.1rem; margin-bottom: 5px; color: var(--azul-oscuro); }
        .contenido-ejercicio p { font-size: 0.9rem; color: #4A5568; line-height: 1.4; }
    </style>
</head>
<body>

    <header>
        <h1>📋 Biblioteca de Ejercicios</h1>
    </header>

    <div class="contenedor">
        
        <?php echo $mensaje; ?>

        <div class="tarjeta">
            <h2>Crear Nuevo Ejercicio</h2>
            <form action="entrenamientos.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="guardar">
                
                <div class="grupo-form">
                    <label for="nombre_ejercicio">Nombre del Ejercicio</label>
                    <input type="text" id="nombre_ejercicio" name="nombre_ejercicio" placeholder="Ej. Rondo 4x4 +3" required>
                </div>

                <div class="grupo-form">
                    <label>Bloque del Entrenamiento</label>
                    <div class="opciones-tipo">
                        <input type="radio" id="tipo_fisica" name="tipo" value="Física" checked>
                        <label Skinner for="tipo_fisica">🏃‍♂️ Parte Física</label>

                        <input type="radio" id="tipo_balon" name="tipo" value="Balón">
                        <label for="tipo_balon">⚽ Con Balón</label>
                    </div>
                </div>

                <div class="grupo-form">
                    <label for="descripcion">Descripción / Instrucciones</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Explica aquí el espacio, las normas del ejercicio y los objetivos..." required></textarea>
                </div>

                <div class="grupo-form">
                    <label for="imagen_ejercicio">Imagen del Ejercicio (Gráfico/Pizarra)</label>
                    <input type="file" id="imagen_ejercicio" name="imagen_ejercicio" accept="image/*">
                </div>

                <button type="submit" class="btn-principal">Guardar Ejercicio</button>
            </form>
        </div>

        <h2>Ejercicios Almacenados</h2>
        <br>
        
        <?php if (count($ejercicios) === 0): ?>
            <p style="text-align: center; color: #A0AEC0;">No hay ejercicios guardados todavía.</p>
        <?php else: ?>
            <?php foreach ($ejercicios as $ejercicio): ?>
                <div class="card-ejercicio">
                    <?php if (!empty($ejercicio['ruta_imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($ejercicio['ruta_imagen']); ?>" class="img-ejercicio" alt="Imagen ejercicio">
                    <?php else: ?>
                        <div class="img-ejercicio" style="display:flex; align-items:center; justify-content:center; color:#A0AEC0; font-size:0.9rem;">
                            Sin gráfico adjunto
                        </div>
                    <?php endif; ?>
                    
                    <div class="contenido-ejercicio">
                        <span class="tag <?php echo ($ejercicio['tipo'] === 'Física') ? 'fisica' : 'balon'; ?>">
                            <?php echo $ejercicio['tipo']; ?>
                        </span>
                        <h3><?php echo htmlspecialchars($ejercicio['nombre_ejercicio']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($ejercicio['descripcion'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</body>
</html>