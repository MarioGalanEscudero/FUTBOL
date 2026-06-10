<?php
// config/conexion.php

// Configuración de los parámetros de la base de datos en XAMPP
$host     = 'localhost';
$db       = 'gestion_equipo_futbol';
$user     = 'root';          // Usuario por defecto en XAMPP
$password = '';              // Por defecto en XAMPP la contraseña está vacía
$charset  = 'utf8mb4';

// Configuración de las opciones de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Activa el reporte de errores graves
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve los datos en formato de array asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Utiliza preparaciones reales para mayor seguridad
];

// Cadena de conexión (Data Source Name)
// Cadena de conexión (Data Source Name) modificada para servidores en la nube
$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=3306";

try {
    // Creamos la instancia de la conexión
    $pdo = new PDO($dsn, $user, $password, $options);
    
    // NOTA: Dejamos el archivo listo. Si queréis comprobar que funciona en el navegador,
    // podéis descomentar la siguiente línea temporalmente:
    // echo "¡Conexión guardada con éxito al equipo!";
    
} catch (\PDOException $e) {
    // Si hay un error, detiene la aplicación y muestra qué ha fallado
    die("Error crítico en la base de datos: " . $e->getMessage());
}
