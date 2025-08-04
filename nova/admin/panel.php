<?php
session_start();
include '../includes/conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $salario = $_POST['salario'] ?? '';
    $tipo_jornada = $_POST['tipo_jornada'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $id_admin = $_SESSION['id'];

    if ($titulo && $descripcion && $salario && $tipo_jornada && $ubicacion) {
        $sql = "INSERT INTO vacantes (titulo, descripcion, salario, tipo_jornada, ubicacion, id_admin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssssi", $titulo, $descripcion, $salario, $tipo_jornada, $ubicacion, $id_admin);
            if ($stmt->execute()) {
                $mensaje = "Vacante publicada correctamente.";
            } else {
                $mensaje = "⚠ Error al publicar la vacante.";
            }
            $stmt->close();
        } else {
            $mensaje = "⚠ Error en la consulta a la base de datos.";
        }
    } else {
        $mensaje = "⚠ Por favor completa todos los campos.";
    }
}

// Obtener vacantes
$sql = "SELECT * FROM vacantes ORDER BY fecha_publicacion DESC";
$vacantes = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="../Estilos/admin.css">
</head>
<body>
<div class="wrapper">
    <div class="main-card">
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="../logout.php" class="logout-button">Cerrar sesión</a>
        </div>

        <h1>Panel de Administración</h1>
        <?php if (!empty($mensaje)): ?>
            <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form class="formulario" method="POST" action="panel.php">
            <input type="text" name="titulo" placeholder="Título" required>
            <textarea name="descripcion" placeholder="Descripción" rows="4" required></textarea>
            <input type="text" name="salario" placeholder="Salario" required>
            <input type="text" name="tipo_jornada" placeholder="Tipo de jornada" required>
            <input type="text" name="ubicacion" placeholder="Ubicación" required>
            <button type="submit">Publicar</button>
        </form>

        <div class="vacante">
            <h2>Vacantes Publicadas</h2>
            <ul>
                <?php if ($vacantes && $vacantes->num_rows > 0): ?>
                    <?php while ($v = $vacantes->fetch_assoc()): ?>
                        <li>
                            <strong><?= htmlspecialchars($v['titulo']) ?></strong><br>
                            <em><?= htmlspecialchars($v['descripcion']) ?></em><br>
                            <a href="editar.php?id=<?= $v['id'] ?>">Editar</a> |
                            <a href="eliminar.php?id=<?= $v['id'] ?>" onclick="return confirm('¿Eliminar esta vacante?');">Eliminar</a>

                            <?php
                            $sqlPost = "SELECT u.nombre, u.correo, p.fecha_postulacion, p.archivo
                                        FROM postulaciones p
                                        JOIN usuarios u ON p.id_usuario = u.id
                                        WHERE p.id_vacante = ?";
                            $stmtPost = $conn->prepare($sqlPost);
                            $stmtPost->bind_param("i", $v['id']);
                            $stmtPost->execute();
                            $resultPost = $stmtPost->get_result();
                            ?>

                            <?php if ($resultPost->num_rows > 0): ?>
                                <div class="postulaciones">
                                    <h4>Postulaciones:</h4>
                                    <?php while ($p = $resultPost->fetch_assoc()): ?>
                                        <div class="postulante">
                                            <p><strong>Nombre:</strong> <?= htmlspecialchars($p['nombre']) ?></p>
                                            <p><strong>Correo:</strong> <?= htmlspecialchars($p['correo']) ?></p>
                                            <p><strong>Fecha:</strong> <?= $p['fecha_postulacion'] ?></p>
                                            <?php if (!empty($p['archivo'])): ?>
                                                <p><strong>Archivo:</strong>
                                                    <a href="../usuario/uploads/<?= htmlspecialchars($p['archivo']) ?>" target="_blank">Ver/Descargar</a>
                                                </p>
                                            <?php else: ?>
                                                <p><em>Sin archivo adjunto</em></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p style="margin-top: 10px; color: #666;">No hay postulaciones aún.</p>
                            <?php endif; ?>
                            <?php $stmtPost->close(); ?>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No hay vacantes aún.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
