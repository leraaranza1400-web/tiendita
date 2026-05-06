<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Tiendita</title>
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

        .register-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
            width: 420px;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 26px;
        }

        label {
            display: block;
            margin: 12px 0 4px;
            color: #555;
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 15px;
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
            margin-top: 20px;
        }

        button:hover { transform: scale(1.02); }

        .link {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .link:hover { text-decoration: underline; }

        /* Mensajes de error desde URL */
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 14px;
            border-radius: 5px;
            margin-bottom: 16px;
            border-left: 4px solid #dc3545;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>📝 Crear cuenta</h2>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-msg">
                ⚠️ <?php
                    $e = $_GET['error'];
                    if($e == 'campos_vacios')    echo "Todos los campos son obligatorios.";
                    if($e == 'email_invalido')   echo "El correo electrónico no es válido.";
                    if($e == 'pass_corta')       echo "La contraseña debe tener al menos 6 caracteres.";
                    if($e == 'pass_no_coincide') echo "Las contraseñas no coinciden.";
                    if($e == 'email_existe')     echo "Ese correo ya está registrado.";
                ?>
            </div>
        <?php endif; ?>

        <form action="crear_usuario.php" method="POST">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>

            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" placeholder="correo@ejemplo.com" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required>

            <label for="password2">Confirmar contraseña</label>
            <input type="password" id="password2" name="password2" placeholder="Repite tu contraseña" required>

            <button type="submit">✅ Registrarme</button>
        </form>

        <a href="index.html" class="link">← ¿Ya tienes cuenta? Inicia sesión</a>
    </div>
</body>
</html>
