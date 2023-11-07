<?php
session_start();
$db = new SQLite3('social_network.db');

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Preparar a instrução SQL
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindValue(':username', $_POST['username'], SQLITE3_TEXT);
    
    // Executar a consulta
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    // Verificar se o usuário existe e a senha está correta
    if ($user && password_verify($_POST['password'], $user['password'])) {
        // Definir variáveis de sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Redirecionar para o dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Usuário ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="formloginregister.css">
</head>
<body>
    <div class="container">
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <input type="text" class="form-input" name="username" placeholder="Username" required><br>
            <input type="password" class="form-input" name="password" placeholder="Password" required><br>
            <input type="submit" class="submit-btn" value="Login">
        </form>
    </div>
</body>
</html>
