<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('social_network.db');

// Criação das tabelas, se não existirem
$db->exec("CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    content TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER,
    user_id INTEGER,
    comment TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS likes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER,
    user_id INTEGER,
    UNIQUE (post_id, user_id)
)");

// Lógica para adicionar um novo post
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_post'])) {
    $stmt = $db->prepare("INSERT INTO posts (user_id, content) VALUES (:user_id, :content)");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':content', $_POST['content'], SQLITE3_TEXT);
    $stmt->execute();
}

// Lógica para adicionar um novo comentário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_comment'])) {
    $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (:post_id, :user_id, :comment)");
    $stmt->bindValue(':post_id', $_POST['post_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':comment', $_POST['comment'], SQLITE3_TEXT);
    $stmt->execute();
    // Redirecionar para evitar reenvio do formulário
    header("Location: dashboard.php");
    exit();
}

// Lógica para adicionar um like
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['like_post'])) {
    $stmt = $db->prepare("INSERT OR IGNORE INTO likes (post_id, user_id) VALUES (:post_id, :user_id)");
    $stmt->bindValue(':post_id', $_POST['post_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $stmt->execute();
    // Redirecionar para evitar reenvio do formulário
    header("Location: dashboard.php");
    exit();
}

// Lógica para buscar todos os posts e seus comentários e curtidas
$postsQuery = $db->query("SELECT posts.*, users.username, 
                                 (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as likes_count
                          FROM posts 
                          JOIN users ON posts.user_id = users.id 
                          ORDER BY timestamp DESC");

$posts = [];
while ($row = $postsQuery->fetchArray(SQLITE3_ASSOC)) {
    $row['comments'] = [];
    // Buscar comentários para o post
    $commentsQuery = $db->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = :post_id");
    $commentsQuery->bindValue(':post_id', $row['id'], SQLITE3_INTEGER);
    $commentsResult = $commentsQuery->execute();
    while ($comment = $commentsResult->fetchArray(SQLITE3_ASSOC)) {
        $row['comments'][] = $comment;
    }
    $posts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="redesocial.css">
</head>
<body>
    <div class="header">
        <div>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div>
            <a href="profile.php">My Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container body-content">
        <div class="post-form">
            <h2>Create a new post</h2>
            <form method="post" action="dashboard.php">
                <textarea name="content" required></textarea>
                <input type="submit" name="new_post" value="Post">
            </form>
        </div>
        <div class="posts">
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <h3><?php echo htmlspecialchars($post['username']); ?></h3>
                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                    <small>Posted on: <?php echo $post['timestamp']; ?></small>
                    <form method="post" action="dashboard.php">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="submit" name="like_post" value="Like">
                        <span><?php echo $post['likes_count']; ?> likes</span>
                    </form>
                    <div class="comments">
                        <?php foreach ($post['comments'] as $comment): ?>
                            <div class="comment">
                                <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                                <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                            </div>
                        <?php endforeach; ?>
                        <form method="post" action="dashboard.php" class="comment-form">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <input type="text" name="comment" placeholder="Write a comment..." required>
                            <input type="submit" name="new_comment" value="Comment">
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
