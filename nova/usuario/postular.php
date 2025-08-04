<?php
session_start();
require '../includes/conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario') {
    header('Location: ../login.php');
    exit();
}

$vacante_id = isset($_GET['vacante_id']) ? intval($_GET['vacante_id']) : 0;

if ($vacante_id <= 0) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM vacantes WHERE id = ?");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$result = $stmt->get_result();
$vacante = $result->fetch_assoc();
$stmt->close();

if (!$vacante) {
    header('Location: index.php');
    exit();
}

$usuarioNombre = $_SESSION['nombre'] ?? '';
$usuarioCorreo = $_SESSION['correo'] ?? '';

$mensaje_exito = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si ya se postuló
    $stmt = $conn->prepare("SELECT COUNT(*) FROM postulaciones WHERE id_vacante = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $vacante_id, $_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($yaPostulado);
    $stmt->fetch();
    $stmt->close();

    if ($yaPostulado > 0) {
        $error = "Ya te has postulado a esta vacante.";
    } else {
        // Subir archivo
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $archivo_nombre = null;
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['archivo']['tmp_name'];
            $fileName = $_FILES['archivo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];

            if (in_array($fileExtension, $allowedfileExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $archivo_nombre = $newFileName;
                } else {
                    $error = "Error al subir el archivo.";
                }
            } else {
                $error = "Tipo de archivo no permitido. Solo imágenes, PDFs y Word.";
            }
        }

        if (!$error) {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $mensaje = trim($_POST['mensaje']);

            if (!$nombre || !$email) {
                $error = "Por favor completa nombre y email.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Email no válido.";
            } else {
                // Insertar postulación en BD incluyendo archivo
                $stmt = $conn->prepare("INSERT INTO postulaciones (id_vacante, id_usuario, archivo, fecha_postulacion) VALUES (?, ?, ?, NOW())");

                if ($stmt === false) {
                    $error = "Error al preparar la consulta: " . $conn->error;
                } else {
                    $stmt->bind_param("iis", $vacante_id, $_SESSION['id'], $archivo_nombre);

                    if ($stmt->execute()) {
                        $mensaje_exito = "¡Postulación enviada correctamente!";
                    } else {
                        $error = "Error al guardar la postulación: " . $stmt->error;
                    }

                    $stmt->close();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Postulación</title>
    <link rel="stylesheet" href="../Estilos/postular.css">
</head>
<body>
    <div class="volver-panel">
        <a href="../usuario/panel.php" class="btn-volver">Regresar</a>
    </div>
    <div class="container">
        <div class="card">
            <h2>Postúlate</h2>
            <?php if ($mensaje_exito): ?>
                <p class="mensaje-exito"><?= htmlspecialchars($mensaje_exito) ?></p>
            <?php elseif ($error): ?>
                <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data" novalidate>
                <input type="text" name="nombre" placeholder="Tu nombre" required value="<?= htmlspecialchars($usuarioNombre) ?>">
                <input type="email" name="email" placeholder="Tu email" required value="<?= htmlspecialchars($usuarioCorreo) ?>">
                <textarea name="mensaje" placeholder="Mensaje o CV (opcional)" rows="5"></textarea>
                <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                <button type="submit">Enviar postulación</button>
            </form>
        </div>

        <div class="card info">
            <h2><?= htmlspecialchars($vacante['titulo']) ?></h2>
            <p><strong>Empresa:</strong> <?= htmlspecialchars($vacante['id_admin']) /* Cambia esto si tienes el nombre de la empresa */ ?></p>
            <p><strong>Salario:</strong> <?= htmlspecialchars($vacante['salario']) ?></p>
            <p><strong>Jornada:</strong> <?= htmlspecialchars($vacante['tipo_jornada']) ?></p>
            <p><strong>Ubicación:</strong> <?= htmlspecialchars($vacante['ubicacion']) ?></p>
            <p><strong>Descripción:</strong></p>
            <p><?= nl2br(htmlspecialchars($vacante['descripcion'])) ?></p>
        </div>
    </div>
</body>
</html>
