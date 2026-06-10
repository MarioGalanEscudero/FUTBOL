<?php
// index.php
// Incluimos la conexión para poder mostrar un pequeño resumen estadístico en el inicio
require_once 'config/conexion.php';

try {
    // Contamos cuántas jugadoras hay en total
    $totalJugadoras = $pdo->query("SELECT COUNT(*) FROM jugadoras")->fetchColumn();
    
    // Contamos cuántos partidos se han creado
    $totalPartidos = $pdo->query("SELECT COUNT(*) FROM partidos")->fetchColumn();
    
    // Contamos cuántos ejercicios tenéis en la biblioteca
    $totalEjercicios = $pdo->query("SELECT COUNT(*) FROM ejercicios")->fetchColumn();
} catch (\PDOException $e) {
    // Si la base de datos está vacía o no conecta, ponemos los contadores a 0
    $totalJugadoras = 0;
    $totalPartidos = 0;
    $totalEjercicios = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Míster App</title>
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
        body { background-color: var(--gris-fondo); color: var(--gris-texto); }

        /* CABECERA PRINCIPAL */
        header {
            background-color: var(--azul-oscuro);
            color: var(--blanco);
            text-align: center;
            padding: 30px 20px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        header h1 { font-size: 1.6rem; margin-bottom: 5px; }
        header p { font-size: 0.9rem; color: #90CDF4; font-weight: 500; }

        .contenedor { max-width: 500px; margin: 0 auto; padding: 20px; }

        /* RESUMEN RÁPIDO (PROYECTO MÓVIL) */
        .resumen-stats {
            display: flex;
            justify-content: space-between;
            margin-top: -15px;
            margin-bottom: 25px;
            gap: 10px;
        }

        .stat-caja {
            background: var(--blanco);
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .stat-caja span { display: block; font-size: 1.2rem; font-weight: bold; color: var(--azul-medio); }
        .stat-caja label { font-size: 0.75rem; color: #718096; font-weight: 600; }

        /* BOTONES DE MENÚ ESTILO MÓVIL (GRANDES) */
        .menu-navegacion {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn-menu {
            display: flex;
            align-items: center;
            background-color: var(--blanco);
            text-decoration: none;
            color: var(--azul-oscuro);
            padding: 20px;
            border-radius: 12px;
            border-left: 6px solid var(--azul-medio);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.1s, background-color 0.1s;
        }

        .btn-menu:active {
            transform: scale(0.98);
            background-color: var(--azul-claro);
        }

        .icono-menu {
            font-size: 2.2rem;
            margin-right: 20px;
            width: 45px;
            text-align: center;
        }

        .texto-menu h2 { font-size: 1.15rem; margin-bottom: 2px; }
        .texto-menu p { font-size: 0.85rem; color: #718096; }
    </style>
</head>
<body>

    <header>
        <h1>⚽ Panel de Entrenadores</h1>
        <p>Temporada Activa • Fútbol 11</p>
    </header>

    <div class="contenedor">
        
        <div class="resumen-stats">
            <div class="stat-caja">
                <span><?php echo $totalJugadoras; ?></span>
                <label>Jugadoras</label>
            </div>
            <div class="stat-caja">
                <span><?php echo $totalPartidos; ?></span>
                <label>Partidos</label>
            </div>
            <div class="stat-caja">
                <span><?php echo $totalEjercicios; ?></span>
                <label>Ejercicios</label>
            </div>
        </div>

        <nav class="menu-navegacion">
            
            <a href="jugadoras.php" class="btn-menu">
                <div class="icono-menu">🏃‍♀️</div>
                <div class="texto-menu">
                    <h2>Gestionar Plantilla</h2>
                    <p>Inscribir jugadoras, consultar dorsales y posiciones.</p>
                </div>
            </a>

            <a href="entrenamientos.php" class="btn-menu">
                <div class="icono-menu">📋</div>
                <div class="texto-menu">
                    <h2>Biblioteca de Entrenamientos</h2>
                    <p>Física y balón con descripciones y pizarras visuales.</p>
                </div>
            </a>

            <a href="partidos.php" class="btn-menu">
                <div class="icono-menu">⏱️</div>
                <div class="texto-menu">
                    <h2>Partidos y Minutos</h2>
                    <p>Crear jornadas de liga y sumar los minutos jugados.</p>
                </div>
            </a>

        </nav>

    </div>

</body>
</html>