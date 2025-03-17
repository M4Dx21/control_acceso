<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rut = $_POST['rut'];
    $nombre_completo = $_POST['nombre_completo'];
    $empresa = $_POST['empresa'];
    $motivo_ingreso = $_POST['motivo_ingreso'];

    if (!preg_match("/^[0-9]{8,9}$/", $rut)) {
        echo "El RUT debe ser de 8 o 9 dígitos.";
    } else {
        $fecha_hora_ingreso = date("Y-m-d H:i:s");

        
        $registro = "RUT: $rut, Nombre: $nombre_completo, Fecha de Ingreso: $fecha_hora_ingreso, Empresa: $empresa, Motivo: $motivo_ingreso\n";
        file_put_contents("registros.txt", $registro, FILE_APPEND);

        echo "Registro de ingreso exitoso.";
    }
}

if (isset($_GET['salida'])) {
    $id = $_GET['salida'];
    $fecha_hora_salida = date("Y-m-d H:i:s");

    
    echo "Registro de salida actualizado para el ID: $id a las $fecha_hora_salida.";
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Ingreso</title>
</head>
<body>
    <h1>Formulario de Ingreso</h1>
    <form method="POST">
        <label for="rut">RUT:</label>
        <input type="text" id="rut" name="rut" required pattern="^\d{8,9}$"><br><br>

        <label for="nombre_completo">Nombre Completo:</label>
        <input type="text" id="nombre_completo" name="nombre_completo" required><br><br>

        <label for="empresa">Empresa:</label>
        <select id="empresa" name="empresa">
            <option value="Empresa 1">TNS</option>
            <option value="Empresa 2">TI</option>
            <option value="Empresa 3">Funcionario</option>
            <option value="Empresa 3">Acompañante</option>
        </select><br><br>

        <label for="motivo_ingreso">Motivo de Ingreso:</label>
        <textarea id="motivo_ingreso" name="motivo_ingreso" required></textarea><br><br>
        <button type="check">Acompañante</button>

        <button type="submit">Registrar Ingreso</button>
    </form>

    <h2>Registrar Salida</h2>
    <form method="GET">
        <label for="salida">ID de Registro de Ingreso:</label>
        <input type="number" id="salida" name="salida" required><br><br>
        <button type="submit">Registrar Salida</button>
    </form>
</body>
</html>
