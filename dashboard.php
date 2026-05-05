<?php
session_start();

// Verificar cookie de autenticación
if (!isset($_SESSION['usuario']) && !isset($_COOKIE['user_logged'])) {
    header("Location: index.html");
    exit();
}

// Si la cookie existe pero no la sesión, restaurar sesión
if (!isset($_SESSION['usuario']) && isset($_COOKIE['user_logged'])) {
    $_SESSION['usuario'] = $_COOKIE['user_email'];
}

include("conexion.php");

// Mostrar bienvenida con cookie
$bienvenida = "Bienvenido " . ($_COOKIE['user_name'] ?? $_SESSION['usuario']);

$resultado = $conexion->query("SELECT * FROM productos ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tiendita</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar h2 {
            font-size: 24px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .navbar a:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Tarjeta de agregar */
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card h3 {
            color: #333;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
            padding-left: 15px;
        }

        .form-inline {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .form-inline input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-inline button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .form-inline button:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Tabla */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f9f9f9;
        }

        /* Botones de acción */
        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            margin-right: 5px;
            font-size: 12px;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        /* Mensajes */
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        /* Info de cookies */
        .cookie-info {
            background: #e7f3ff;
            color: #004085;
            padding: 8px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2> Tiendita - Inventario</h2>
        <a href="logout.php"> Cerrar sesión</a>
    </div>

    <div class="container">
        <!-- Mostrar info de cookies -->
        <div class="cookie-info">
             Cookies activas: 
            <?php 
                if(isset($_COOKIE['user_email'])) echo "Email: " . $_COOKIE['user_email'] . " | ";
                if(isset($_COOKIE['user_logged'])) echo "Sesión activa por 7 días";
            ?>
        </div>

        <!-- Mostrar mensajes -->
        <?php if(isset($_GET['mensaje'])): ?>
            <div class="success">
                 Producto <?php echo $_GET['mensaje'] == 'agregado' ? 'agregado' : 'actualizado'; ?> correctamente
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="error">
                 Error: 
                <?php 
                    if($_GET['error'] == 'nombre_vacio') echo "El nombre no puede estar vacío";
                    if($_GET['error'] == 'precio_invalido') echo "El precio debe ser mayor a 0";
                    if($_GET['error'] == 'stock_invalido') echo "El stock no puede ser negativo";
                ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para agregar -->
        <div class="card">
            <h3> Agregar nuevo producto</h3>
            <form action="agregar.php" method="POST" class="form-inline">
                <input name="nombre" placeholder=" Nombre del producto" required>
                <input name="precio" type="number" step="0.01" placeholder=" Precio" required>
                <input name="stock" type="number" placeholder=" Stock" required>
                <button type="submit">Agregar producto</button>
            </form>
        </div>

        <!-- Lista de productos -->
        <div class="table-container">
            <h3 style="margin-bottom: 20px;"> Lista de productos</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td>$<?php echo number_format($row['precio'], 2); ?></td>
                        <td>
                            <?php echo $row['stock']; ?>
                            <?php if($row['stock'] <= 5 && $row['stock'] > 0): ?>
                                <span style="color: orange;"></span>
                            <?php elseif($row['stock'] == 0): ?>
                                <span style="color: red;"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn-edit">✏️ Editar</a>
                            <a href="eliminar.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('¿Seguro que quieres eliminar este producto?')">🗑️ Eliminar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
