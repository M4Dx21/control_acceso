<?php
include 'db.php';

function validarRUT($rut) {
    $rut = str_replace(array(".", "-"), "", $rut);
    
    if (!preg_match("/^[0-9]{7,8}[0-9kK]{1}$/", $rut)) {
        return false;
    }

    $rut_numeros = substr($rut, 0, -1);
    $rut_dv = strtoupper(substr($rut, -1));

    $suma = 0;
    $factor = 2;
    for ($i = strlen($rut_numeros) - 1; $i >= 0; $i--) {
        $suma += $rut_numeros[$i] * $factor;
        $factor = ($factor == 7) ? 2 : $factor + 1;
    }

    $dv_calculado = 11 - ($suma % 11);
    if ($dv_calculado == 11) {
        $dv_calculado = '0';
    } elseif ($dv_calculado == 10) {
        $dv_calculado = 'K';
    }

    return $dv_calculado == $rut_dv;
}


function formatearRUT($rut) {
    $rut = str_replace(array(".", "-"), "", $rut);
    $dv = strtoupper(substr($rut, -1));
    $rut = substr($rut, 0, -1);
    $rut = strrev(implode(".", str_split(strrev($rut), 3)));
    return $rut . '-' . $dv;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ingresar"])) {
    $rut = $_POST["rut"];
    
    $rut = str_replace(array(".", "-"), "", $rut);
    
    if (empty($rut) || !validarRUT($rut)) {
        $mensaje = "<div class='msg error'><span class='icon'>&#10060;</span> Error: El RUT es inválido o está mal ingresado.</div>";
    } else {
        $nombre = $_POST["nombre"];
        $empresa = $_POST["empresa"];
        $motivo = $_POST["motivo"];

        if ($stmt = $conn->prepare("INSERT INTO registros (rut, nombre, empresa, motivo_ingreso, fecha_ingreso) VALUES (?, ?, ?, ?, NOW())")) {
            $stmt->bind_param("ssss", $rut, $nombre, $empresa, $motivo);
            if ($stmt->execute()) {
                $mensaje = "<div class='msg success'><span class='icon'>&#10004;</span> Ingreso registrado correctamente.</div>";
            } else {
                $mensaje = "<div class='msg error'><span class='icon'>&#10060;</span> Error: " . $stmt->error . "</div>";
            }
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["salida"])) {
    $id = $_POST["id"];

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
            margin: 0;
            padding-top: 0;
            background-image: url('hospital.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .header {
            display: flex;
            align-items: left;
            justify-content: center;
            background-color:rgba(255, 255, 255, 0.89);
            color: #0056b3;
            padding: 10px 10px;
            position: fixed;
            top: 3px;
            left: 472px;
            width: 100%;
            max-width: 950px;
            text-align: center;
            font-size: 27px;
            z-index: 1000;
            margin-bottom: 0;
            border-radius: 15px;
        }

        .header img {
            height: 40px;
            margin-right: 10px;
        }

        .container {
            background: rgba(255, 255, 255, 0.89); 
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(70, 25, 25, 0.1);
            width: 100%;
            max-width: 950px;
            margin: 10px auto;
            margin-top: 75px;
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
            background: rgb(0, 105, 218);
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
            border-spacing: 0;
            overflow-x: auto;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ccc;
            word-wrap: break-word;
            border-radius: 10px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            max-width: 200px;
            background-color: #f9f9f9;
        }

        th {
            background-color: rgb(0, 105, 218);
            color: white; 
            font-weight: bold;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .salida-btn-table {
            background: rgb(204, 41, 57);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            padding: 8px 12px;
        }

        .salida-btn-table:hover {
            background: rgb(151, 5, 19);
        }

        .msg {
            padding: 20px;
            margin-top: 20px;
            border-radius: 15px;
            font-size: 12px;
            max-width: 500px;
            margin: auto;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transition: opacity 0.7s ease-out;
            display: none;
        }

        .msg.success {
            background-color:rgb(6, 228, 73);
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
        .sub-title{
            font-size: 17px;
            color:#dd7603;
        }

        @media (max-width: 700px) {
            .container {
                padding: 20px;
            }

            .header{
                display: flex;
                justify-content: center;
                padding: 10px 10px;
                position: fixed;
                left: 0px;
                text-align: center;
                font-size: 24px;
                z-index: 1000;
                margin-bottom: 0;
                border-radius: 15px;
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
    <div class="header">
        <img src="logo.png" alt="Logo">
        <div class="header-text">
            <div class="main-title">Control de Acceso Datacenter</div>
            <div class="sub-title">Hospital Clínico Felix Bulnes</div>
        </div>
    </div>

    <div class="container">
        <div id="mensaje-container">
            <?php if (isset($mensaje)) echo $mensaje; ?>
        </div>

        <form method="POST">
            <input type="text" name="rut" placeholder="RUT (sin puntos)" required>
            <input type="text" name="nombre" placeholder="Nombre Completo" required>
            <input type="text" name="empresa" placeholder="Empresa" required>
            <textarea name="motivo" placeholder="Motivo de ingreso (max 300)" required></textarea>
            <button type="submit" name="ingresar">Registrar Ingreso</button>
        </form>
        
        <?php if (!empty($personas_dentro)): ?>
            <h3>Al terminar su visita apretar boton salir donde aparezcan sus datos para confirmar salida</h3>
            <table>
                <thead>
                    <tr>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Motivo</th>
                        <th>Ingreso</th>
                        <th>Salida</th>
                    </tr>
                </thead>
                <tbody>
            
                <?php foreach ($personas_dentro as $persona): ?>
                    <tr>
                        <td><?php echo formatearRUT(htmlspecialchars($persona['rut'])); ?></td>
                        <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($persona['empresa']); ?></td>
                        <td><?php echo htmlspecialchars($persona['motivo_ingreso']); ?></td>
                        <td>
                            <?php 
                            $fecha_ingreso = new DateTime($persona['fecha_ingreso']);
                                echo $fecha_ingreso->format('d/m H:i');
                            ?>
                        </td>
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
                    }, 3000);
                }, 70); 
            }
        };
    </script>
</body>
</html>
