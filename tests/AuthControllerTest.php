<?php
// tests/AuthControllerTest.php

namespace Equipe4\Gigastage\Tests;

use PHPUnit\Framework\TestCase;
use Equipe4\Gigastage\Controllers\AuthController;
use Equipe4\Gigastage\Models\ProfileModel;
use Equipe4\Gigastage\Models\ApplicationModel;
use Equipe4\Gigastage\Models\WishlistModel;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Tests unitaires pour AuthController.
 *
 * Le ProfileModel, l'ApplicationModel et le WishlistModel sont mockés,
 * Twig utilise un loader en mémoire — aucune base de données requise.
 */
class AuthControllerTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader([
            'pages/login.html.twig'           => '<html></html>',
            'pages/register.html.twig'        => '<html></html>',
            'pages/profile-student.html.twig' => '<html></html>',
            'pages/profile-pilot.html.twig'   => '<html></html>',
            'pages/profile-admin.html.twig'   => '<html></html>',
        ]));

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $_POST    = [];
        $_SERVER  = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST    = [];
        $_SERVER  = [];
    }

    // -----------------------------------------------------------------------
    // Helper : crée le contrôleur avec les 3 modèles mockés
    // -----------------------------------------------------------------------

    private function makeController(?ProfileModel $profileModel = null): AuthController
    {
        return new AuthController(
            $this->twig,
            $profileModel              ?? $this->createStub(ProfileModel::class),
            $this->createStub(ApplicationModel::class),
            $this->createStub(WishlistModel::class)
        );
    }

    /** Génère un token CSRF valide et l'injecte dans la session. */
    private function generateValidCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /** Appelle une méthode protected/private via Reflection. */
    private function callProtected(object $object, string $method, array $args = []): mixed
    {
        $ref = new \ReflectionMethod($object, $method);
        $ref->setAccessible(true);
        return $ref->invokeArgs($object, $args);
    }

    // -----------------------------------------------------------------------
    // Tests sur la génération / validation du token CSRF
    // -----------------------------------------------------------------------

    public function testCsrfTokenIsGeneratedAndNotEmpty(): void
    {
        $controller = $this->makeController();
        $token = $this->callProtected($controller, 'getCsrfToken');

        $this->assertNotEmpty($token, 'Le token CSRF ne doit pas être vide.');
    }

    public function testCsrfTokenHasCorrectLength(): void
    {
        $controller = $this->makeController();
        $token = $this->callProtected($controller, 'getCsrfToken');

        // bin2hex(random_bytes(32)) produit 64 caractères hexadécimaux
        $this->assertSame(64, strlen($token), 'Le token CSRF doit faire 64 caractères (32 octets en hex).');
    }

    public function testCsrfTokenIsConsistentWithinSameSession(): void
    {
        $controller = $this->makeController();

        $token1 = $this->callProtected($controller, 'getCsrfToken');
        $token2 = $this->callProtected($controller, 'getCsrfToken');

        $this->assertSame($token1, $token2, 'Le token CSRF doit rester identique au sein de la même session.');
    }

    public function testCsrfTokenIsStoredInSession(): void
    {
        $controller = $this->makeController();
        $token = $this->callProtected($controller, 'getCsrfToken');

        $this->assertSame($token, $_SESSION['csrf_token'], 'Le token CSRF doit être sauvegardé en session.');
    }

    // -----------------------------------------------------------------------
    // Tests sur la validation des champs du formulaire de connexion
    // -----------------------------------------------------------------------

    public function testLoginSetsFlashErrorWhenEmailIsEmpty(): void
    {
        $profileModel = $this->createMock(ProfileModel::class);
        $profileModel->expects($this->never())->method('findByEmail');

        $controller = $this->makeController($profileModel);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => $this->generateValidCsrfToken(),
            'email'      => '',
            'password'   => 'secret123',
        ];

        ob_start();
        try { $controller->login(); } catch (\Throwable $e) { /* header/exit ignorés */ }
        ob_end_clean();

        $this->assertArrayHasKey('flash', $_SESSION, 'Un flash d\'erreur doit être défini si l\'email est vide.');
        $this->assertSame('error', $_SESSION['flash']['type']);
    }

    public function testLoginSetsFlashErrorWhenPasswordIsEmpty(): void
    {
        $profileModel = $this->createMock(ProfileModel::class);
        $profileModel->expects($this->never())->method('findByEmail');

        $controller = $this->makeController($profileModel);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => $this->generateValidCsrfToken(),
            'email'      => 'test@exemple.com',
            'password'   => '',
        ];

        ob_start();
        try { $controller->login(); } catch (\Throwable $e) { /* ignoré */ }
        ob_end_clean();

        $this->assertArrayHasKey('flash', $_SESSION);
        $this->assertSame('error', $_SESSION['flash']['type']);
    }

    public function testLoginSetsFlashErrorWhenUserNotFound(): void
    {
        $profileModel = $this->createStub(ProfileModel::class);
        $profileModel->method('findByEmail')->willReturn(null);

        $controller = $this->makeController($profileModel);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => $this->generateValidCsrfToken(),
            'email'      => 'inexistant@exemple.com',
            'password'   => 'wrongpassword',
        ];

        ob_start();
        try { $controller->login(); } catch (\Throwable $e) { /* ignoré */ }
        ob_end_clean();

        $this->assertArrayHasKey('flash', $_SESSION, 'Un flash d\'erreur doit être défini si l\'utilisateur est introuvable.');
        $this->assertSame('error', $_SESSION['flash']['type']);
    }

    public function testLoginSetsFlashErrorOnWrongPassword(): void
    {
        $hashedPassword = password_hash('correctpassword', PASSWORD_BCRYPT);

        $profileModel = $this->createStub(ProfileModel::class);
        $profileModel->method('findByEmail')->willReturn([
            'idUser'    => 1,
            'email'     => 'user@exemple.com',
            'password'  => $hashedPassword,
            'role'      => 'Etudiant',
            'firstName' => 'Jean',
            'surname'   => 'Dupont',
        ]);

        $controller = $this->makeController($profileModel);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => $this->generateValidCsrfToken(),
            'email'      => 'user@exemple.com',
            'password'   => 'wrongpassword',
        ];

        ob_start();
        try { $controller->login(); } catch (\Throwable $e) { /* ignoré */ }
        ob_end_clean();

        $this->assertArrayHasKey('flash', $_SESSION, 'Un flash d\'erreur doit être défini si le mot de passe est incorrect.');
        $this->assertSame('error', $_SESSION['flash']['type']);
    }

    // -----------------------------------------------------------------------
    // Tests sur la validation des champs du formulaire d'inscription
    // -----------------------------------------------------------------------

    public function testRegisterSetsFlashErrorWhenFieldsAreMissing(): void
    {
        $profileModel = $this->createMock(ProfileModel::class);
        $profileModel->expects($this->never())->method('createUser');

        $controller = $this->makeController($profileModel);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => $this->generateValidCsrfToken(),
            'email'      => '',
            'password'   => '',
            'firstName'  => '',
            'surname'    => '',
        ];

        ob_start();
        try { $controller->register(); } catch (\Throwable $e) { /* ignoré */ }
        ob_end_clean();

        $this->assertArrayHasKey('flash', $_SESSION, 'Un flash d\'erreur doit être défini si les champs sont vides.');
        $this->assertSame('error', $_SESSION['flash']['type']);
    }

    public function testRegisterSetsFlashErrorWhenEmailAlreadyExists(): void
    {
        $profileModel = $this->createMock(ProfileModel::class);
        $profileModel->method('findByEmail')->willReturn(['idUser' => 42]);
        $profileModel->expects($this->never())->method('createUser');

        $controller = $this->makeController($profileModel);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => $this->generateValidCsrfToken(),
            'email'      => 'deja@utilise.com',
            'password'   => 'motdepasse123',
            'firstName'  => 'Alice',
            'surname'    => 'Martin',
        ];

        ob_start();
        try { $controller->register(); } catch (\Throwable $e) { /* ignoré */ }
        ob_end_clean();

        $this->assertArrayHasKey('flash', $_SESSION, 'Un flash d\'erreur doit être défini si l\'email est déjà utilisé.');
        $this->assertSame('error', $_SESSION['flash']['type']);
    }

    public function testRegisterCallsCreateUserWithHashedPassword(): void
    {
        $profileModel = $this->createMock(ProfileModel::class);
        $profileModel->method('findByEmail')->willReturn(null); // email libre

        $capturedData = null;
        $profileModel->expects($this->once())
            ->method('createUser')
            ->willReturnCallback(function (array $data) use (&$capturedData) {
                $capturedData = $data;
            });

        $controller = $this->makeController($profileModel);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => $this->generateValidCsrfToken(),
            'email'      => 'nouveau@exemple.com',
            'password'   => 'monMotDePasse',
            'firstName'  => 'Bob',
            'surname'    => 'Dupont',
        ];

        ob_start();
        try { $controller->register(); } catch (\Throwable $e) { /* ignoré */ }
        ob_end_clean();

        $this->assertNotNull($capturedData, 'createUser doit être appelé avec des données.');
        $this->assertNotSame('monMotDePasse', $capturedData['password'], 'Le mot de passe ne doit jamais être stocké en clair.');
        $this->assertTrue(
            password_verify('monMotDePasse', $capturedData['password']),
            'Le hash bcrypt doit correspondre au mot de passe original.'
        );
    }
}
