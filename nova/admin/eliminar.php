<?php
session_start();
include '../includes/conexion.php';

// Solo permitir acceso a administradores
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if ($id) {
    $sql = "DELETE FROM vacantes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

header('Location: panel.php');
exit;
