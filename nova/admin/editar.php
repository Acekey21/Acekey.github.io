<?php
session_start();
include '../includes/conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    exit('ID no válido');
}

$sql = "SELECT * FROM vacantes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$vacante = $result->fetch_assoc();
$stmt->close();

if (!$vacante) {
    exit('Vacante no encontrada');
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $salario = $_POST['salario'] ?? '';
    $tipo_jornada = $_POST['tipo_jornada'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';

    if ($titulo && $descripcion && $salario && $tipo_jornada && $ubicacion) {
        $sql_update = "UPDATE vacantes SET titulo = ?, descripcion = ?, salario = ?, tipo_jornada = ?, ubicacion = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        if ($stmt) {
            $stmt->bind_param("sssssi", $titulo, $descripcion, $salario, $tipo_jornada, $ubicacion, $id);
            if ($stmt->execute()) {
                $mensaje = "Vacante actualizada correctamente.";
                // Actualizar datos para mostrar en el formulario después de guardar
                $vacante['titulo'] = $titulo;
                $vacante['descripcion'] = $descripcion;
                $vacante['salario'] = $salario;
                $vacante['tipo_jornada'] = $tipo_jornada;
                $vacante['ubicacion'] = $ubicacion;
            } else {
                $mensaje = "Error al actualizar la vacante.";
            }
            $stmt->close();
        } else {
            $mensaje = "Error en la consulta de actualización.";
        }
    } else {
        $mensaje = "Por favor completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Vacante</title>
    <link rel="stylesheet" href="../Estilos/editar.css">
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <h1>Editar Vacante</h1>

            <?php if ($mensaje): ?>
                <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
            <?php endif; ?>

            <form method="POST" class="formulario">
                <input type="text" name="titulo" value="<?= htmlspecialchars($vacante['titulo']) ?>" placeholder="Título" required>
                <textarea name="descripcion" rows="4" placeholder="Descripción" required><?= htmlspecialchars($vacante['descripcion']) ?></textarea>
                <input type="text" name="salario" value="<?= htmlspecialchars($vacante['salario']) ?>" placeholder="Salario" required>
                <input type="text" name="tipo_jornada" value="<?= htmlspecialchars($vacante['tipo_jornada']) ?>" placeholder="Tipo de jornada" required>
                <input type="text" name="ubicacion" value="<?= htmlspecialchars($vacante['ubicacion']) ?>" placeholder="Ubicación" required>
                <button type="submit">Guardar Cambios</button>
            </form>

            <div class="volver">
                <a href="panel.php">Regresar</a>
            </div>
        </div>
    </div>
</body>
</html>
