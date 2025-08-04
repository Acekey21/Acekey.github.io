<?php
session_start();
include 'includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $confirmar = $_POST['confirmar'];
    $telefono = $_POST['telefono'] ?? null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;

    // Validar que las contraseñas coincidan
    if ($contrasena !== $confirmar) {
        $error = " Las contraseñas no coinciden.";
    } else {
        $contrasena_segura = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, 'usuario')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nombre, $correo, $contrasena_segura);

        if ($stmt->execute()) {
            header("Location: login.php?registro=exitoso");
            exit();
        } else {
            $error = " Error al registrar: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="Estilos/registro.css">
</head>
<body>
    <h2> Registro de Usuario</h2>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Nombre completo:</label><br>
        <input type="text" name="nombre" required><br>

        <label>Correo electrónico:</label><br>
        <input type="email" name="correo" required><br>

        <label>Contraseña:</label><br>
        <input type="password" name="contrasena" required><br>

        <label>Confirmar contraseña:</label><br>
        <input type="password" name="confirmar" required><br>

        

        <button type="submit"> Registrarme</button>
    </form>

    <br><a href="login.php">Volver al login</a>
</body>
</html>