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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
    <title>ToDoList</title>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">ToDoList</a>
    <div class="ms-auto text-white">
      Etudiant : TOUKO YVAN
    </div>
  </div>
</nav>

<main class="container my-4">
  <div class="row">
    <div class="col-md-8 offset-md-2">

      <!-- Formulaire d'ajout -->
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">Ajouter une tâche</h5>
          <form method="post" class="row g-2">
            <input type="hidden" name="action" value="new">
            <div class="col-9">
              <input type="text" name="title" class="form-control" placeholder="Titre de la tâche" required maxlength="2048">
            </div>
            <div class="col-3 d-grid">
              <button type="submit" class="btn btn-success">Ajouter</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Liste des tâches -->
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Tâches</h5>
          <?php if (empty($taches)): ?>
            <p class="text-muted">Aucune tâche pour le moment.</p>
          <?php else: ?>
            <ul class="list-group">
              <?php foreach ($taches as $tache): ?>
                <?php
                  $classe = $tache->done ? 'list-group-item-success' : 'list-group-item-warning';
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-start <?php echo $classe; ?>">
                  <div class="ms-2 me-auto">
                    <div class="fw-bold"><?php echo htmlspecialchars($tache->title, ENT_QUOTES, 'UTF-8'); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($tache->created_at, ENT_QUOTES, 'UTF-8'); ?></small>
                  </div>

                  <!-- Formulaire toggle/delete pour chaque tâche -->
                  <form method="post" class="ms-3" style="display:flex;gap:.4rem;">
                    <input type="hidden" name="id" value="<?php echo (int)$tache->id; ?>">
                    <button type="submit" name="action" value="toggle" class="btn btn-sm btn-outline-primary" title="Basculer fait/non fait">
                      <?php echo $tache->done ? 'Annuler' : 'Marquer fait'; ?>
                    </button>
                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette tâche ?');">
                      Supprimer
                    </button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</main>

<script src="./bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>