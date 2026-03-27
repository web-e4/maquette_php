<?php

namespace Equipe4\Gigastage\Controllers;

class AuthController {
	public function __construct() {
		$loader = new \Twig\Loader\FilesystemLoatder(__DIR__ . '/../Views');
		$this->twig = new \Twig\Environment($loader);
	}

    public function loginPage() {
        echo $this->twig->render('connexion.html.twig');
    }

    public function login() {
		$email = $_POST['email'] ?? '';
		$password = $_POST['password'] ?? '';

		if (empty($email) || empty($password)) {
			echo $this->twig->render('connexion.html.twig', [
				'error' => 'Email et mdp requis.'
			]);
			return;
		}

		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
		$stmt->execute([':email' => $email]);
		$user = $stmt->fetch(\PDO::FETCH_ASSOC);

		if(!$user||!password_verify($password, $user['password'])) {
			echo $this->twig->render('connexion.html.twig', [
				'error' => 'Identifiants incorrects.'
			]);
			return;
		}

		session_start();
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['user_email'] = $user['email'];

		//header('Location: ///à compléter);
		exit;
	}

    public function registerPage() {
		$nom	  = trim($_POST['nom'] ?? '');
		$prenom	  = trim($_POST['prenom'] ?? '');
		$email	  = trim($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '');
		$confirm  = $_POST['confirm_password'] ?? '');

		if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm)) {
			echo $this->twig->render('inscription.html.twig', [
				'error' => 'Tous les champs sont requis.'
			]);
			return;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			echo $this->twig->render('inscription.html.twig', [
				'error' => 'Email invalide.'
			]);
			return;
		}

		if ($password !== $confirm) {
			echo $this->twig->render('inscription.html.twig', [
				'error' => 'Les mots de passe ne correspondent pas.'
			]);
			return;
		}

		$stmt = $this->pdo->prepare(
			'SELECT id FROM users WHERE email = :email LIMIT 1'
		);
		$stmt->execute([':email' => $email]);
		if ($stmt->fetch()) {
			echo $this->twig->render('inscription.html.twig', [
				'error' => 'Email utilisé.'
			]);
			return;
		}

		$hash = password_hash($password, PASSWORD_BCRYPT);
		$stmt = $this->pdo->prepare('
			INSERT INTO users (nom, prenom, email, password)
			VALUES (:nom, :prenom, :email, :password)
			');
		$stmt->execute([
			':nom'		=> $nom,
			':prenom'	=> $prenom,
			':email'	=> $email,
			':password'	=> $hash,
		]);

		$userId = $this->pdo->lastInsertId();

		session_start();
		$_SESSION['user_id']	= $userId;
		$_SESSION['user_email']	= $email;

		//header('Location: profil'); à compléter
		exit;
    }

    public function register() {
    	 
    }
}

?>
