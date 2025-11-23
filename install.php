<?php
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'todolist');
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);

header('Content-Type: text/html; charset=utf-8');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
if ($mysqli->connect_errno) {
    echo '<h2>Erreur de connexion au serveur MySQL :</h2><pre>' . htmlspecialchars($mysqli->connect_error) . '</pre>';
    exit;
}

// 1) Créer la base si nécessaire
$sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$mysqli->query($sql)) {
    echo '<h2>Impossible de créer la base :</h2><pre>' . htmlspecialchars($mysqli->error) . '</pre>';
    $mysqli->close();
    exit;
}

// Sélectionner la base
if (!$mysqli->select_db(DB_NAME)) {
    echo '<h2>Impossible de sélectionner la base ' . htmlspecialchars(DB_NAME) . '</h2>';
    $mysqli->close();
    exit;
}

// 2) Créer la table todo si nécessaire
$createTable = "
CREATE TABLE IF NOT EXISTS `todo` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `done` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!$mysqli->query($createTable)) {
    echo '<h2>Impossible de créer la table todo :</h2><pre>' . htmlspecialchars($mysqli->error) . '</pre>';
    $mysqli->close();
    exit;
}

// 3) Optionnel : insérer une tâche d'exemple si la table est vide
$res = $mysqli->query("SELECT COUNT(*) AS cnt FROM `todo`");
$cnt = ($res) ? (int)$res->fetch_assoc()['cnt'] : 0;
if ($cnt === 0) {
    $stmt = $mysqli->prepare("INSERT INTO `todo` (title, created_at, done) VALUES (?, NOW(), 0)");
    if ($stmt) {
        $sample = 'Bienvenue — tâche d\'exemple';
        $stmt->bind_param('s', $sample);
        $stmt->execute();
        $stmt->close();
    }
}

$mysqli->close();

echo '<h2>Installation terminée</h2>';
echo '<p>Base <code>' . htmlspecialchars(DB_NAME) . '</code> et table <code>todo</code> créées avec succès.</p>';
echo '<p><a href="index.php">Voir l\'application</a></p>';
echo '<p><strong>Important :</strong> supprimez <code>install.php</code> du serveur après utilisation.</p>';
?>