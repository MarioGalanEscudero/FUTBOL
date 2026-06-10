<?php
// 1. Incluimos la conexión
require_once 'config/conexion.php';

$mensaje = "";

// 2. LÓGICA PARA CREAR UN PARTIDO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_partido') {
    $rival = trim($_POST['rival']);
    $fecha = $_POST['fecha'];

    if (!empty($rival) && !empty($fecha)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO partidos (rival, fecha) VALUES (?, ?)");
            $stmt->execute([$rival, $fecha]);
            $mensaje = "<p class='alerta-exito'>¡Partido registrado! Ya puedes asignar los minutos.</p>";
        } catch (\PDOException $e) {
            $mensaje = "<p class='alerta-error'>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        $mensaje = "<p class='alerta-error'>Por favor, rellena todos los campos.</p>";
    }
}

// 3. LÓGICA PARA GUARDAR LOS MINUTOS DE UN PARTIDO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_minutos') {
    $id_partido = intval($_POST['id_partido']);
    $minutos = $_POST['minutos']; // Esto es un array con los minutos de cada jugadora

    try {
        $pdo->beginTransaction(); // Usamos una transacción para guardar todo en bloque de forma segura

        foreach ($minutos as $id_jugadora => $min) {
            $min = intval($min);
            
            // Comprobamos si ya se habían metido minutos para esta jugadora en este partido
            $check = $pdo->prepare("SELECT id_minutos FROM minutos_partido WHERE id_jugadora = ? AND id_partido = ?");
            $check->execute([$id_jugadora, $id_partido]);
            
            if ($check->fetch()) {
                // Si ya existía, lo actualizamos
                $stmt = $pdo->prepare("UPDATE minutos_partido SET minutos_jugados = ? WHERE id_jugadora = ? AND id_partido = ?");
                $stmt->execute([$min, $id_jugadora, $id_partido]);
            } else {
                // Si no existía, lo insertamos nuevo
                $stmt = $pdo->prepare("INSERT INTO minutos_partido (id_jugadora, id_partido, minutos_jugados) VALUES (?, ?, ?)");
                $stmt->execute([$id_jugadora, $id_partido, $min]);
            }
        }

        $pdo->commit();
        $mensaje = "<p class='alerta-exito'>¡Minutos guardados y sumados correctamente!</p>";
    } catch (\Exception $e) {
        $pdo->rollBack();
        $mensaje = "<p class='alerta-error'>Error al guardar los minutos: " . $e->getMessage() . "</p>";
    }
}

// 4. OBTENER DATOS PARA MOSTRAR
try {
    // Listar todos los partidos
    $stmtPartidos = $pdo->query("SELECT * FROM partidos ORDER BY fecha DESC");
    $partidos = $stmtPartidos->fetchAll();

    // Listar todas las jugadoras para el formulario de minutos
    $stmtJugadoras = $pdo->query("SELECT * FROM jugadoras ORDER BY dorsal ASC");
    $jugadoras = $stmtJugadoras->fetchAll();
} catch (\PDOException $e) {
    die("Error de consulta: " . $e->getMessage());
}

// 5. COMPROBAR SI ESTAMOS EDITANDO MINUTOS DE UN PARTIDO EN CONCRETO
$partido_seleccionado = null;
$minutos_actuales = [];
if (isset($_GET['registrar_minutos_partido'])) {
    $id_partido_sel = intval($_GET['registrar_minutos_partido']);
    
    // Traemos los datos del partido
    $stmt = $pdo->prepare("SELECT * FROM partidos WHERE id_partido = ?");
    $stmt->execute([$id_partido_sel]);
    $partido_seleccionado = $stmt->fetch();

    if ($partido_seleccionado) {
        // Traemos los minutos que ya estuvieran guardados (si los hay) para precargarlos en el formulario
        $stmt = $pdo->prepare("SELECT id_jugadora, minutos_jugados FROM minutos_partido WHERE id_partido = ?");
        $stmt->execute([$id_partido_sel]);
        while ($row = $stmt->fetch()) {
            $minutos_actuales[$row['id_jugadora']] = $row['minutos_jugados'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partidos y Minutos - App Fútbol</title>
    <style>
        :root {
            --azul-oscuro: #1A365D;
            --azul-medio: #3182CE;
            --azul-claro: #EBF8FF;
            --blanco: #FFFFFF;
            --gris-fondo: #F4F7F6;
            --gris-texto: #2D3748;
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
        .grupo-form input { width: 100%; padding: 12px; border: 1px solid #CBD5E0; border-radius: 8px; font-size: 1rem; }

        .btn-principal {
            width: 100%; background-color: var(--azul-medio); color: var(--blanco);
            border: none; padding: 14px; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer;
        }
        .btn-secundario {
            display: inline-block; text-decoration: none; text-align: center;
            background-color: var(--azul-claro); color: var(--azul-oscuro);
            padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: bold; margin-top: 10px;
        }

        .alerta-exito { color: #38A169; font-weight: bold; margin-bottom: 15px; text-align: center; }
        .alerta-error { color: #E53E3E; font-weight: bold; margin-bottom: 15px; text-align: center; }

        /* LISTA DE PARTIDOS */
        .item-partido {
            border-bottom: 1px solid #E2E8F0;
            padding: 12px 0;
        }
        .item-partido:last-child { border-bottom: none; }
        .item-partido h3 { font-size: 1rem; color: var(--azul-oscuro); }
        .item-partido p { font-size: 0.85rem; color: #718096; }

        /* TABLA MÓVIL PARA MINUTOS */
        .fila-minutos {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #EDF2F7;
        }
        .info-jug { display: flex; align-items: center; gap: 10px; font-size: 0.95rem; }
        .dorsal-sm {
            background: var(--azul-oscuro); color: white; font-weight: bold;
            width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;
        }
        .input-minutos { width: 70px !important; text-align: center; padding: 6px !important; }
    </style>
</head>
<body>

    <header>
        <h1>⚽ Partidos y Minutos</h1>
    </header>

    <div class="contenedor">
        
        <?php echo $mensaje; ?>

        <?php if ($partido_seleccionado): ?>
            <div class="tarjeta" style="border: 2px solid var(--azul-medio);">
                <h2>Minutos: vs <?php echo htmlspecialchars($partido_seleccionado['rival']); ?></h2>
                <p style="font-size:0.85rem; color:#718096; margin-bottom:15px;">Introduce los minutos jugados por cada jugadora:</p>
                
                <form action="partidos.php" method="POST">
                    <input type="hidden" name="accion" value="guardar_minutos">
                    <input type="hidden" name="id_partido" value="<?php echo $partido_seleccionado['id_partido']; ?>">

                    <?php foreach ($jugadoras as $jugadora): ?>
                        <div class="fila-minutos">
                            <div class="info-jug">
                                <div class="dorsal-sm"><?php echo $jugadora['dorsal']; ?></div>
                                <span><?php echo htmlspecialchars($jugadora['nombre'] . " " . $jugadora['apellido']); ?></span>
                            </div>
                            <div class="grupo-form" style="margin-bottom:0;">
                                <?php $min_jugados = isset($minutos_actuales[$jugadora['id_jugadora']]) ? $minutos_actuales[$jugadora['id_jugadora']] : 0; ?>
                                <input type="number" class="input-minutos" name="minutos[<?php echo $jugadora['id_jugadora']; ?>]" value="<?php echo $min_jugados; ?>" min="0" max="120" required>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn-principal" style="margin-top: 15px;">Guardar Minutos del Partido</button>
                    <a href="partidos.php" class="btn-secundario" style="width:100%; background:#E2E8F0; color:#4A5568;">Volver / Cancelar</a>
                </form>
            </div>
        <?php endif; ?>

        <div class="tarjeta">
            <h2>Registrar Próximo Partido</h2>
            <form action="partidos.php" method="POST">
                <input type="hidden" name="accion" value="crear_partido">
                
                <div class="grupo-form">
                    <label for="rival">Equipo Rival</label>
                    <input type="text" id="rival" name="rival" placeholder="Ej. Rayo Vallecano" required>
                </div>

                <div class="grupo-form">
                    <label for="fecha">Fecha del Partido</label>
                    <input type="date" id="fecha" name="fecha" required>
                </div>

                <button type="submit" class="btn-principal">Crear Partido</button>
            </form>
        </div>

        <div class="tarjeta">
            <h2>Historial de Partidos</h2>
            
            <?php if (count($partidos) === 0): ?>
                <p style="text-align: center; color: #A0AEC0; padding: 10px 0;">No hay partidos registrados aún.</p>
            <?php else: ?>
                <?php foreach ($partidos as $partido): ?>
                    <div class="item-partido">
                        <h3>vs <?php echo htmlspecialchars($partido['rival']); ?></h3>
                        <p>📅 Fecha: <?php echo date("d/m/Y", strtotime($partido['fecha'])); ?></p>
                        <a href="partidos.php?registrar_minutos_partido=<?php echo $partido['id_partido']; ?>" class="btn-secundario">
                            ⏱️ Gestionar Minutos
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>