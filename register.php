<?php
$db = new SQLite3('social_network.db');

// Criar a tabela de usuários se não existir
$query = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            password TEXT,
            email TEXT UNIQUE)";
$db->exec($query);

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare e vincule os parâmetros
    $stmt = $db->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
    $stmt->bindValue(':username', $_POST['username'], SQLITE3_TEXT);
    $stmt->bindValue(':password', password_hash($_POST['password'], PASSWORD_DEFAULT), SQLITE3_TEXT);
    $stmt->bindValue(':email', $_POST['email'], SQLITE3_TEXT);

    // Tente executar a instrução preparada
    if ($stmt->execute()) {
        header('Location: login.php');
        exit();
    } else {
        $error = "Erro ao criar a conta.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="formloginregister.css">
</head>
<body>
    <div class="container">
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post" action="register.php">
            <input type="text" class="form-input" name="username" placeholder="Username" required><br>
            <input type="email" class="form-input" name="email" placeholder="Email" required><br>
            <input type="password" class="form-input" name="password" placeholder="Password" required><br>
            <input type="submit" class="submit-btn" value="Register">
        </form>
    </div>
</body>
</html>
