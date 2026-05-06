<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.html");
    exit();
}
include("conexion.php");

// FIX: intval() para evitar SQL injection en el GET
$id = intval($_GET['id'] ?? 0);

if($id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// FIX: prepared statement en lugar de query directa
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if(!$row) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .edit-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
            width: 500px;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        h2 { text-align: center; color: #333; margin-bottom: 30px; font-size: 28px; }

        label { display: block; margin: 10px 0 4px; color: #555; font-size: 14px; font-weight: 600; }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102,126,234,0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
            margin-top: 15px;
        }

        button:hover { transform: scale(1.02); }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }

        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2>✏️ Editar producto</h2>
        <form action="actualizar.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

            <label for="nombre">Nombre del producto</label>
            <input type="text" id="nombre" name="nombre"
                   value="<?php echo htmlspecialchars($row['nombre']); ?>" required>

            <label for="precio">Precio</label>
            <input type="number" id="precio" name="precio" step="0.01"
                   value="<?php echo $row['precio']; ?>" required>

            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock"
                   value="<?php echo $row['stock']; ?>" required>

            <button type="submit">💾 Actualizar producto</button>
        </form>
        <a href="dashboard.php" class="back-link">← Volver al dashboard</a>
    </div>
</body>
</html>
