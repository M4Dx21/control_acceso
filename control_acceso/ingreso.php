<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ingresar"])) {
    if (!isset($_POST["rut"]) || empty($_POST["rut"])) {
        die("Error: El campo RUT es obligatorio.");
    }

    $rut = $_POST["rut"];
    $nombre = $_POST["nombre"];
    $empresa = $_POST["empresa"];
    $motivo = $_POST["motivo"];

    // Usamos consultas preparadas para evitar inyecciones SQL
    if ($stmt = $conn->prepare("INSERT INTO registros (rut, nombre, empresa, motivo_ingreso, fecha_ingreso) VALUES (?, ?, ?, ?, NOW())")) {
        $stmt->bind_param("ssss", $rut, $nombre, $empresa, $motivo);
        if ($stmt->execute()) {
            $mensaje = "<div class='msg success'><span class='icon'>&#10004;</span> Ingreso registrado correctamente.</div>";
        } else {
            $mensaje = "<div class='msg error'><span class='icon'>&#10060;</span> Error: " . $stmt->error . "</div>";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["salida"])) {
    $id = $_POST["id"];

    // Usamos consultas preparadas para evitar inyecciones SQL
    if ($stmt = $conn->prepare("UPDATE registros SET fecha_salida = NOW() WHERE id = ?")) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $mensaje = "<div class='msg success'><span class='icon'>&#10004;</span> Salida registrada correctamente.</div>";
        } else {
            $mensaje = "<div class='msg error'><span class='icon'>&#10060;</span> Error al registrar salida: " . $stmt->error . "</div>";
        }
    }
}

$sql_check = "SELECT id, rut, nombre, empresa, motivo_ingreso, fecha_ingreso, fecha_salida FROM registros WHERE fecha_salida IS NULL";
$result = $conn->query($sql_check);
$personas_dentro = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $personas_dentro[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Acceso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: rgb(232, 242, 247);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 950px; 
            margin: 10px;
        }

        input, textarea, button {
            width: 90%;
            padding: 10px;
            margin: 2px 0;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 13px;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .salida-btn {
            background: #dc3545;
        }

        .salida-btn:hover {
            background: #a71d2a;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: separate;
            overflow-x: auto;
        }

        table, th, td {
            border: 1px solid #ccc;
            word-wrap: break-word;
        }

        th, td {
            padding: 10px;
            text-align: left;
            max-width: 200px;
        }

        .salida-btn-table {
            background: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
        }

        .salida-btn-table:hover {
            background: #a71d2a;
        }

        .msg {
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transition: opacity 0.5s ease-out;
            display: none;
        }

        .msg.success {
            background-color: #007bff;
            color: white;
        }

        .msg.error {
            background-color: #dc3545;
            color: white;
        }

        .icon {
            margin-right: 10px;
            font-size: 24px;
        }

        @media (max-width: 700px) {
            .container {
                padding: 20px;
            }

            table {
                display: block;
                width: 100%;
                overflow-x: auto;
                white-space: nowrap;
            }

            th, td {
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registro de Ingreso</h2>
        <div id="mensaje-container">
            <?php if (isset($mensaje)) echo $mensaje; ?>
        </div>

        <form method="POST">
            <input type="text" name="rut" placeholder="RUT" required>
            <input type="text" name="nombre" placeholder="Nombre Completo" required>
            <input type="text" name="empresa" placeholder="Empresa" required>
            <textarea name="motivo" placeholder="Motivo de ingreso (max 300)" required></textarea>
            <button type="submit" name="ingresar">Registrar Ingreso</button>
        </form>
        
        <?php if (!empty($personas_dentro)): ?>
            <h2>Personas dentro de la sala</h2>
            <table>
                <thead>
                    <tr>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Motivo</th>
                        <th>Salida</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personas_dentro as $persona): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($persona['rut']); ?></td>
                            <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($persona['empresa']); ?></td>
                            <td><?php echo htmlspecialchars($persona['motivo_ingreso']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
                                    <button type="submit" name="salida" class="salida-btn-table">Salir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <script>
        window.onload = function() {
            const msg = document.querySelector('.msg');
            if (msg) {
                msg.style.display = 'flex';
                setTimeout(function() {
                    msg.style.opacity = 1; 
                    setTimeout(function() {
                        msg.style.display = 'none'; 
                    }, 3000);  // Tiempo extendido para mostrar el mensaje
                }, 70); 
            }
        };
    </script>
</body>
</html>
