<?php
// 1. Incluimos la conexión a la base de datos
require_once 'config/conexion.php';

// 2. LÓGICA PARA INSERTAR JUGADORA (Si se envía el formulario)
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dorsal = intval($_POST['dorsal']);
    $posicion = trim($_POST['posicion_principal']);

    if (!empty($nombre) && !empty($apellido) && $dorsal > 0 && !empty($posicion)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO jugadoras (nombre, apellido, dorsal, posicion_principal) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $dorsal, $posicion]);
            $mensaje = "<p class='alerta-exito'>¡Jugadora añadida correctamente a la plantilla!</p>";
        } catch (\PDOException $e) {
            $mensaje = "<p class='alerta-error'>Error al guardar: " . $e->getMessage() . "</p>";
        }
    } else {
        $mensaje = "<p class='alerta-error'>Por favor, rellena todos los campos correctamente.</p>";
    }
}

// 3. LÓGICA PARA OBTENER LAS JUGADORAS ACTUALES
try {
    $stmt = $pdo->query("SELECT * FROM jugadoras ORDER BY dorsal ASC");
    $jugadoras = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Error al consultar las jugadoras: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantilla - App Fútbol</title>
    <style>
        /* CONFIGURACIÓN Y COLORES (Azules y Blanco) */
        :root {
            --azul-oscuro: #1A365D;
            --azul-medio: #3182CE;
            --azul-claro: #EBF8FF;
            --blanco: #FFFFFF;
            --gris-fondo: #F4F7F6;
            --gris-texto: #2D3748;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--gris-fondo);
            color: var(--gris-texto);
            padding-bottom: 40px;
        }

        /* CABECERA DE LA APP */
        header {
            background-color: var(--azul-oscuro);
            color: var(--blanco);
            text-align: center;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        header h1 {
            font-size: 1.5rem;
        }

        .contenedor {
            max-width: 500px; /* Tamaño ideal para simular/ver en móvil */
            margin: 0 auto;
            padding: 15px;
        }

        /* SECCIONES / TARJETAS */
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

        /* FORMULARIOS ESTILO MÓVIL */
        .grupo-form {
            margin-bottom: 15px;
        }

        .grupo-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .grupo-form input, .grupo-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #CBD5E0;
            border-radius: 8px;
            font-size: 1rem;
            background-color: var(--blanco);
        }

        .grupo-form input:focus, .grupo-form select:focus {
            outline: none;
            border-color: var(--azul-medio);
            box-shadow: 0 0 0 3px var(--azul-claro);
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
            transition: background 0.2s;
        }

        .btn-principal:active {
            background-color: var(--azul-oscuro);
        }

        /* ALERTAS */
        .alerta-exito { color: #38A169; font-weight: bold; margin-bottom: 15px; text-align: center; }
        .alerta-error { color: #E53E3E; font-weight: bold; margin-bottom: 15px; text-align: center; }

        /* LISTADO DE JUGADORAS (VISTA MÓVIL) */
        .lista-jugadoras {
            list-style: none;
        }

        .item-jugadora {
            display: flex;
            align-items: center;
            background: var(--blanco);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 5px solid var(--azul-medio);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .dorsal-badge {
            background-color: var(--azul-oscuro);
            color: var(--blanco);
            font-weight: bold;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .info-jugadora {
            flex-grow: 1;
        }

        .info-jugadora h3 {
            font-size: 1rem;
            color: var(--gris-texto);
        }

        .info-jugadora p {
            font-size: 0.85rem;
            color: #718096;
        }

        .contador {
            text-align: right;
            font-size: 0.85rem;
            color: var(--azul-medio);
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <header>
        <h1>⚽ Gestión de Plantilla</h1>
    </header>

    <div class="contenedor">
        
        <?php echo $mensaje; ?>

        <div class="tarjeta">
            <h2>Añadir Nueva Jugadora</h2>
            <form action="jugadoras.php" method="POST">
                <input type="hidden" name="accion" value="guardar">
                
                <div class="grupo-form">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej. Marta" required>
                </div>

                <div class="grupo-form">
                    <label for="apellido">Apellidos</label>
                    <input type="text" id="apellido" name="apellido" placeholder="Ej. García" required>
                </div>

                <div class="grupo-form">
                    <label for="dorsal">Dorsal</label>
                    <input type="number" id="dorsal" name="dorsal" placeholder="Ej. 10" min="1" max="99" required>
                </div>

                <div class="grupo-form">
                    <label for="posicion_principal">Posición Principal</label>
                    <select id="posicion_principal" name="posicion_principal" required>
                        <option value="">-- Selecciona posición --</option>
                        <option value="Portera">Portera</option>
                        <option value="Defensa Central">Defensa Central</option>
                        <option value="Lateral">Lateral</option>
                        <option value="Centrocampista">Centrocampista</option>
                        <option value="Extremo">Extremo</option>
                        <option value="Delantera Centro">Delantera Centro</option>
                    </select>
                </div>

                <button type="submit" class="btn-principal">Inscribir Jugadora</button>
            </form>
        </div>

        <div class="tarjeta">
            <h2>Plantilla Actual</h2>
            <div class="contador">
                Total: <?php echo count($jugadoras); ?> / 25 jugadoras
            </div>
            
            <?php if (count($jugadoras) === 0): ?>
                <p style="text-align: center; color: #A0AEC0;">Aún no hay jugadoras registradas.</p>
            <?php else: ?>
                <ul class="lista-jugadoras">
                    <?php foreach ($jugadoras as $jugadora): ?>
                        <li class="item-jugadora">
                            <div class="dorsal-badge"><?php echo htmlspecialchars($jugadora['dorsal']); ?></div>
                            <div class="info-jugadora">
                                <h3><?php echo htmlspecialchars($jugadora['nombre'] . ' ' . $jugadora['apellido']); ?></h3>
                                <p><?php echo htmlspecialchars($jugadora['posicion_principal']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>