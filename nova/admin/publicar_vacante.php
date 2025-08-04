<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $empresa = $_POST['empresa'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $sueldo = $_POST['sueldo'] ?? '';
    $jornada = $_POST['jornada'] ?? '';

    if ($titulo && $empresa && $descripcion && $sueldo && $jornada) {
        $stmt = $conn->prepare("INSERT INTO vacantes (titulo, empresa, descripcion, sueldo, jornada) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $titulo, $empresa, $descripcion, $sueldo, $jornada);

        if ($stmt->execute()) {
            $mensaje = "Vacante publicada correctamente.";
        } else {
            $mensaje = "Error al publicar la vacante: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $mensaje = "Por favor completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicar Vacante</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffeef7;
            color: #333;
            padding: 20px;
        }

        h2 {
            color: #cb0cab;
        }

        form {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 20px;
            background-color: #cb0cab;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #a0088c;
        }

        .mensaje {
            margin-top: 15px;
            color: green;
        }

        .volver {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 16px;
            background-color: #cb0cab;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .volver:hover {
            background-color: #a0088c;
        }
    </style>
</head>
<body>
    <h2>Publicar Nueva Vacante</h2>

    <form method="post" action="">
        <label for="titulo">Título de la Vacante:</label>
        <input type="text" name="titulo" id="titulo" required>

        <label for="empresa">Empresa:</label>
        <input type="text" name="empresa" id="empresa" required>

        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" id="descripcion" rows="4" required></textarea>

        <label for="sueldo">Sueldo:</label>
        <input type="text" name="sueldo" id="sueldo" required>

        <label for="jornada">Jornada:</label>
        <select name="jornada" id="jornada" required>
            <option value="">Selecciona una opción</option>
            <option value="Tiempo completo">Tiempo completo</option>
            <option value="Medio tiempo">Medio tiempo</option>
            <option value="Prácticas">Prácticas</option>
        </select>

        <button type="submit">Publicar Vacante</button>
    </form>

    <?php if ($mensaje): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <a href="panel.php" class="volver">← Volver al Panel</a>
</body>
</html>
