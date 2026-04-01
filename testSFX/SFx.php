<?php
/**
 * SFx1 - Authentification et gestion des accès
 * Cette page gère la connexion, la déconnexion et la vérification des permissions
 */

session_start();

require_once '../config/database.php';
require_once '../models/User.php';

// Instancier la connexion DB
$database = new Database();
$db = $database->getConnection();

// Instancier le modèle User
$user = new User($db);

// Gestion de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: SFx1.php");
    exit();
}

// Gestion de la connexion
$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Traitement de la connexion
        $email = htmlspecialchars(strip_tags($_POST['email']));
        $password = $_POST['password'];

        if ($user->login($email, $password)) {
            // Connexion réussie - Stocker les infos en session
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['logged_in'] = true;

            $success_message = "Connexion réussie ! Bienvenue " . $user->email;
            
            // Redirection selon le rôle
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Email ou mot de passe incorrect.";
        }
    }
}

/**
 * Fonction helper pour vérifier si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Fonction helper pour vérifier les permissions
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    $user->role = $_SESSION['user_role'];
    
    return $user->hasPermission($permission);
}

/**
 * Fonction pour protéger une page (require login)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: SFx1.php");
        exit();
    }
}

/**
 * Fonction pour protéger une page avec permission spécifique
 */
function requirePermission($permission) {
    requireLogin();
    
    if (!hasPermission($permission)) {
        die("Accès refusé : vous n'avez pas les permissions nécessaires.");
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SFx1 - Authentification | GigaStage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .error {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .success {
            background-color: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }

        .user-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 5px 0;
            color: #333;
        }

        .user-info strong {
            color: #667eea;
        }

        .btn-logout {
            background: #dc3545;
            margin-top: 15px;
        }

        .btn-logout:hover {
            background: #c82333;
        }

        .links {
            margin-top: 20px;
            text-align: center;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isLoggedIn()): ?>
            <!-- Utilisateur connecté -->
            <h1>Bienvenue sur GigaStage</h1>
            <p class="subtitle">SFx1 - Authentification et gestion des accès</p>

            <div class="user-info">
                <p><strong>Email :</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <p><strong>Rôle :</strong> <?php echo htmlspecialchars($_SESSION['user_role']); ?></p>
                <p><strong>ID :</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
            </div>

            <a href="?action=logout" class="btn btn-logout">Se déconnecter</a>

            <div class="links">
                <a href="dashboard.php">Aller au tableau de bord</a> | 
                <a href="SFx2.php">Rechercher une entreprise</a>
            </div>

        <?php else: ?>
            <!-- Formulaire de connexion -->
            <h1>Connexion</h1>
            <p class="subtitle">SFx1 - Authentification et gestion des accès</p>

            <?php if ($error_message): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn">Se connecter</button>
            </form>

            <div class="links">
                <a href="#">Mot de passe oublié ?</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>


<?php
/**
 * SFx1 - Authentification et gestion des accès
 * Cette page gère la connexion, la déconnexion et la vérification des permissions
 */

session_start();

require_once '../config/database.php';
require_once '../models/User.php';

// Instancier la connexion DB
$database = new Database();
$db = $database->getConnection();

// Instancier le modèle User
$user = new User($db);

// Gestion de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: SFx1.php");
    exit();
}

// Gestion de la connexion
$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Traitement de la connexion
        $email = htmlspecialchars(strip_tags($_POST['email']));
        $password = $_POST['password'];

        if ($user->login($email, $password)) {
            // Connexion réussie - Stocker les infos en session
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['logged_in'] = true;

            $success_message = "Connexion réussie ! Bienvenue " . $user->email;
            
            // Redirection selon le rôle
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Email ou mot de passe incorrect.";
        }
    }
}

/**
 * Fonction helper pour vérifier si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Fonction helper pour vérifier les permissions
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    $user->role = $_SESSION['user_role'];
    
    return $user->hasPermission($permission);
}

/**
 * Fonction pour protéger une page (require login)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: SFx1.php");
        exit();
    }
}

/**
 * Fonction pour protéger une page avec permission spécifique
 */
function requirePermission($permission) {
    requireLogin();
    
    if (!hasPermission($permission)) {
        die("Accès refusé : vous n'avez pas les permissions nécessaires.");
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SFx1 - Authentification | GigaStage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .error {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .success {
            background-color: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }

        .user-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 5px 0;
            color: #333;
        }

        .user-info strong {
            color: #667eea;
        }

        .btn-logout {
            background: #dc3545;
            margin-top: 15px;
        }

        .btn-logout:hover {
            background: #c82333;
        }

        .links {
            margin-top: 20px;
            text-align: center;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isLoggedIn()): ?>
            <!-- Utilisateur connecté -->
            <h1>Bienvenue sur GigaStage</h1>
            <p class="subtitle">SFx1 - Authentification et gestion des accès</p>

            <div class="user-info">
                <p><strong>Email :</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <p><strong>Rôle :</strong> <?php echo htmlspecialchars($_SESSION['user_role']); ?></p>
                <p><strong>ID :</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
            </div>

            <a href="?action=logout" class="btn btn-logout">Se déconnecter</a>

            <div class="links">
                <a href="dashboard.php">Aller au tableau de bord</a> | 
                <a href="SFx2.php">Rechercher une entreprise</a>
            </div>

        <?php else: ?>
            <!-- Formulaire de connexion -->
            <h1>Connexion</h1>
            <p class="subtitle">SFx1 - Authentification et gestion des accès</p>

            <?php if ($error_message): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn">Se connecter</button>
            </form>

            <div class="links">
                <a href="#">Mot de passe oublié ?</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
/**
 * SFx2 - Rechercher et afficher une entreprise
 * Permet de rechercher des entreprises et d'afficher leurs fiches détaillées
 */

session_start();

require_once '../config/database.php';
require_once '../models/Entreprise.php';
require_once '../models/Evaluation.php';

// Connexion DB
$database = new Database();
$db = $database->getConnection();

// Instancier les modèles
$entreprise = new Entreprise($db);
$evaluation = new Evaluation($db);

// Gestion de la recherche
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$entreprise_id = isset($_GET['id']) ? $_GET['id'] : null;

// Affichage d'une entreprise spécifique
$detail_entreprise = null;
$evaluations = [];

if ($entreprise_id) {
    $detail_entreprise = $entreprise->getById($entreprise_id);
    $eval_stmt = $evaluation->getByEntreprise($entreprise_id);
    $evaluations = $eval_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Liste des entreprises (recherche)
$stmt = $entreprise->search($search_term);
$entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SFx2 - Rechercher une entreprise | GigaStage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
        }

        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .entreprises-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .entreprise-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .entreprise-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .entreprise-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .entreprise-card p {
            color: #666;
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.5;
        }

        .stats {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .stat-item {
            flex: 1;
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .rating {
            color: #ffa500;
            font-size: 18px;
        }

        .detail-view {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .detail-header {
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .detail-header h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .evaluations-section {
            margin-top: 30px;
        }

        .evaluation-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }

        .evaluation-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .evaluation-user {
            font-weight: 600;
            color: #333;
        }

        .evaluation-date {
            color: #666;
            font-size: 12px;
        }

        .evaluation-note {
            color: #ffa500;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Rechercher une entreprise</h1>
            <p class="subtitle">SFx2 - Recherche et affichage des entreprises partenaires</p>
        </header>

        <?php if ($detail_entreprise): ?>
            <!-- Vue détaillée d'une entreprise -->
            <a href="SFx2.php" class="back-link">← Retour à la recherche</a>

            <div class="detail-view">
                <div class="detail-header">
                    <h2><?php echo htmlspecialchars($detail_entreprise['nom']); ?></h2>
                    <div class="stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $detail_entreprise['nb_candidatures']; ?></div>
                            <div class="stat-label">Candidatures</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value rating">
                                <?php echo number_format($detail_entreprise['moyenne_evaluations'], 1); ?> ★
                            </div>
                            <div class="stat-label">Évaluation moyenne</div>
                        </div>
                    </div>
                </div>

                <h3 style="margin-bottom: 10px;">Description</h3>
                <p style="margin-bottom: 20px; line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($detail_entreprise['description'])); ?>
                </p>

                <h3 style="margin-bottom: 15px;">Coordonnées</h3>
                <div class="contact-info">
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($detail_entreprise['email']); ?>">
                                <?php echo htmlspecialchars($detail_entreprise['email']); ?>
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($detail_entreprise['telephone']); ?>
                        </div>
                    </div>
                </div>

                <div class="evaluations-section">
                    <h3 style="margin-bottom: 15px;">Évaluations (<?php echo count($evaluations); ?>)</h3>
                    
                    <?php if (count($evaluations) > 0): ?>
                        <?php foreach ($evaluations as $eval): ?>
                            <div class="evaluation-card">
                                <div class="evaluation-header">
                                    <span class="evaluation-user">
                                        <?php echo htmlspecialchars($eval['user_email']); ?>
                                    </span>
                                    <span class="evaluation-date">
                                        <?php echo date('d/m/Y', strtotime($eval['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="evaluation-note">
                                    <?php echo str_repeat('★', $eval['note']); ?>
                                    <?php echo str_repeat('☆', 5 - $eval['note']); ?>
                                </div>
                                <?php if (!empty($eval['commentaire'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($eval['commentaire'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-results">Aucune évaluation pour le moment.</p>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 30px;">
                    <a href="SFx5.php?id=<?php echo $detail_entreprise['id']; ?>" class="btn">
                        Évaluer cette entreprise
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Liste et recherche -->
            <div class="search-box">
                <form method="GET" action="" class="search-form">
                    <input 
                        type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="Rechercher une entreprise par nom..."
                        value="<?php echo htmlspecialchars($search_term); ?>"
                    >
                    <button type="submit" class="btn">Rechercher</button>
                    <?php if ($search_term): ?>
                        <a href="SFx2.php" class="btn btn-secondary">Réinitialiser</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (count($entreprises) > 0): ?>
                <div class="entreprises-grid">
                    <?php foreach ($entreprises as $ent): ?>
                        <div class="entreprise-card">
                            <h3><?php echo htmlspecialchars($ent['nom']); ?></h3>
                            <p>
                                <?php 
                                    $desc = htmlspecialchars($ent['description']);
                                    echo strlen($desc) > 150 ? substr($desc, 0, 150) . '...' : $desc;
                                ?>
                            </p>
                            
                            <div class="stats">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $ent['nb_candidatures']; ?></div>
                                    <div class="stat-label">Candidatures</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value rating">
                                        <?php echo number_format($ent['moyenne_evaluations'], 1); ?> ★
                                    </div>
                                    <div class="stat-label">Moyenne</div>
                                </div>
                            </div>

                            <a href="?id=<?php echo $ent['id']; ?>" class="btn" style="width: 100%; text-align: center; margin-top: 15px;">
                                Voir les détails
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <p>Aucune entreprise trouvée.</p>
                    <?php if ($search_term): ?>
                        <p>Essayez avec d'autres critères de recherche.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="SFx1.php" class="btn btn-secondary">Retour à l'accueil</a>
            <a href="SFx3.php" class="btn">Créer une entreprise</a>
        </div>
    </div>
</body>
</html>