<?php
session_start();
require '../includes/conexion.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$id_usuario = $_SESSION['id'];

// Consultar las postulaciones del usuario
$sql = "
    SELECT v.titulo, v.empresa, v.descripcion, p.fecha_postulacion, p.id AS id_postulacion 
    FROM postulaciones p 
    JOIN vacantes v ON p.id_vacante = v.id 
    WHERE p.id_usuario = ?
    ORDER BY p.fecha_postulacion DESC
";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Mis Postulaciones</title>
    <link rel="stylesheet" href="../Estilos/mis_postulaciones.css">
</head>
<body>
    <div class="volver-panel">
        <a href="../usuario/panel.php" class="btn-volver">Regresar</a>
    </div>
    <h1>Mis Postulaciones</h1>
    <table>
        <tr>
            <th>Vacante</th>
            <th>Empresa</th>
            <th>Descripción</th>
            <th>Fecha de Postulación</th>
            <th>Acciones</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($fila['titulo']) . "</td>";
                echo "<td>" . htmlspecialchars($fila['empresa']) . "</td>";
                echo "<td>" . nl2br(htmlspecialchars($fila['descripcion'])) . "</td>";
                echo "<td>" . $fila['fecha_postulacion'] . "</td>";
                echo "<td><a href='cancelar.php?id=" . $fila['id_postulacion'] . "' onclick=\"return confirm('¿Estás seguro de cancelar esta postulación?');\">Cancelar</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No has realizado postulaciones todavía.</td></tr>";
        }
        ?>
    </table>
        </table>
</body>
</html>

</body>
</html>
