<?php
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'todolist');
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);

// Connexion à la base de données
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($mysqli->connect_errno) {
    http_response_code(500);
    exit('Erreur de connexion à la base de données.');
}
$mysqli->set_charset('utf8mb4');

// Traitement des actions POST : new, delete, toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'new') {
        // Attendu : champ 'title' envoyé en POST
        $title = trim((string)($_POST['title'] ?? ''));
        if ($title !== '') {
            $stmt = $mysqli->prepare('INSERT INTO todo (title, created_at, done) VALUES (?, NOW(), 0)');
            if ($stmt) {
                $stmt->bind_param('s', $title);
                $stmt->execute();
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        // Attendu : champ 'id' envoyé en POST
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $mysqli->prepare('DELETE FROM todo WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    } elseif ($action === 'toggle') {
        // Attendu : champ 'id' envoyé en POST
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $mysqli->prepare('UPDATE todo SET done = 1 - done WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Eviter le repost : redirection (PRG)
    header('Location: index.php');
    exit;
}

// Lecture des tâches triées du plus récent au plus ancien
$taches = [];
$result = $mysqli->query('SELECT id, title, done, created_at FROM todo ORDER BY created_at DESC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Conserver sous forme d'objets simples
        $taches[] = (object)[
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'done' => (int)$row['done'],
            'created_at' => $row['created_at'],
        ];
    }
    $result->free();
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>
<body>
    <header>
        <h1>ToDoList</h1>
        <button type="submit" name="action" value="new"></button>
    </header>
    <main>

    </main>
</body>
</html>