<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('social_network.db');

// Buscar informações do perfil do usuário logado
$profileQuery = $db->prepare("SELECT * FROM users WHERE id = :id");
$profileQuery->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
$profileResult = $profileQuery->execute();
$profile = $profileResult->fetchArray(SQLITE3_ASSOC);

// Buscar postagens do usuário
$userPostsQuery = $db->prepare("SELECT * FROM posts WHERE user_id = :user_id ORDER BY timestamp DESC");
$userPostsQuery->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
$userPostsResult = $userPostsQuery->execute();
$userPosts = [];
while ($row = $userPostsResult->fetchArray(SQLITE3_ASSOC)) {
    $userPosts[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($profile['username']); ?>'s Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($profile['username']); ?>'s Profile</h1>
        <a href="dashboard.php">Back to Dashboard</a> | <a href="logout.php">Logout</a>
        <hr>
        <h2>Profile Details</h2>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($profile['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
        <!-- Adicione mais detalhes do perfil aqui -->
        <hr>
        <h2>My Posts</h2>
        <?php foreach ($userPosts as $post): ?>
            <div class="post">
                <p><?php echo htmlspecialchars($post['content']); ?></p>
                <small>Posted on: <?php echo $post['timestamp']; ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
