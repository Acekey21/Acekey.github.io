<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$correo_usuario = $_SESSION['correo'] ?? 'Invitado';

require '../includes/conexion.php'; // Asegúrate que la ruta sea correcta
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Usuario</title>
    <link rel="stylesheet" href="../Estilos/usuarios.css">
    
</head>
<body>

<div class="top-nav">
    Bienvenido, <?php echo htmlspecialchars($correo_usuario); ?> |
    <a href="mis_postulaciones.php">Mis Postulaciones</a> |
    <a href="../logout.php">Cerrar sesión</a>
</div>

<h1>Vacantes Disponibles</h1>

<div class="contenedor">
    <?php
    // Prepara y ejecuta la consulta
    $sql = "SELECT * FROM vacantes ORDER BY fecha_publicacion DESC";
    $result = $conn->query($sql);

    if ($result) {
        while ($vacante = $result->fetch_assoc()) {
            echo '<div class="vacante">';
            echo '<h2>' . htmlspecialchars($vacante['titulo']) . '</h2>';
            echo '<p><strong>Ubicación:</strong> ' . htmlspecialchars($vacante['ubicacion']) . '</p>';
            echo '<p><strong>Descripción:</strong> ' . nl2br(htmlspecialchars($vacante['descripcion'])) . '</p>';
            echo '<p><strong>Salario:</strong> ' . htmlspecialchars($vacante['salario']) . '</p>';
            echo '<p><strong>Jornada:</strong> ' . htmlspecialchars($vacante['tipo_jornada']) . '</p>';
            echo '<a class="btn-postular" href="postular.php?vacante_id=' . $vacante['id'] . '">Postularse</a>';
            echo '</div>';
        }
        $result->free();
    } else {
        echo "Error en la consulta: " . $conn->error;
    }
    ?>
</div>

</body>
</html>
