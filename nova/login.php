<?php
session_start();
include 'includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();

        if (password_verify($contrasena, $usuario['contrasena'])) {
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            if ($usuario['rol'] === 'admin') {
                header("Location: admin/panel.php");
            } else {
                header("Location: usuario/panel.php");
            }
            exit();
        } else {
            $error = "⚠ Contraseña incorrecta.";
        }
    } else {
        $error = "⚠ Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="Estilos/login.css">
</head>
<body>
    <h2> Iniciar sesión</h2>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso') echo "<p style='color:green;'> Usuario registrado con éxito. Ahora puedes iniciar sesión.</p>"; ?>

    <form method="POST" action="login.php">
        <label for="correo">Correo:</label><br>
        <input type="email" name="correo" required><br><br>

        <label for="contrasena">Contraseña:</label><br>
        <input type="password" name="contrasena" required><br><br>

        <button type="submit">Entrar</button>
         <br>
    <p>¿No tienes cuenta?<a href="registro.php">Regístrate aquí</a></p>
    </form>

   
</body>
</html>