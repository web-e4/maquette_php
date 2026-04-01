<?php
// tests/Controllers/AbstractControllerTest.php

namespace Equipe4\Gigastage\Tests\Controllers;

use PHPUnit\Framework\TestCase;
use Equipe4\Gigastage\Core\AccessControl;
use Equipe4\Gigastage\Core\Role;

/**
 * On crée une classe concrète pour tester AbstractController,
 * car il est abstract et ne peut pas être instancié directement.
 * On expose les méthodes protected en public pour pouvoir les tester.
 */
class ConcreteController extends \Equipe4\Gigastage\Controllers\AbstractController
{
    public function exposedIsLoggedIn(): bool         { return $this->isLoggedIn(); }
    public function exposedGetUser(): ?array           { return $this->getUser(); }
    public function exposedGetUserRole(): string       { return $this->getUserRole(); }
    public function exposedUserCan(string $p): bool   { return $this->userCan($p); }
}

class AbstractControllerTest extends TestCase
{
    private ConcreteController $controller;

    protected function setUp(): void
    {
        // Démarre une session PHP propre avant chaque test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        // On crée un mock de Twig pour ne pas dépendre du vrai moteur de templates
        $twig = $this->createMock(\Twig\Environment::class);
        $this->controller = new ConcreteController($twig);
    }

    protected function tearDown(): void
    {
        // Nettoie la session après chaque test
        $_SESSION = [];
    }

    // -----------------------------------------------------------------------
    // isLoggedIn()
    // -----------------------------------------------------------------------

    public function testIsLoggedInReturnsFalseWhenNoSession(): void
    {
        $this->assertFalse($this->controller->exposedIsLoggedIn());
    }

    public function testIsLoggedInReturnsTrueWhenUserInSession(): void
    {
        $_SESSION['user'] = ['id' => 1, 'role' => Role::ADMIN];

        $this->assertTrue($this->controller->exposedIsLoggedIn());
    }

    // -----------------------------------------------------------------------
    // getUser()
    // -----------------------------------------------------------------------

    public function testGetUserReturnsNullWhenNotLoggedIn(): void
    {
        $this->assertNull($this->controller->exposedGetUser());
    }

    public function testGetUserReturnsSessionDataWhenLoggedIn(): void
    {
        $userData = ['id' => 42, 'role' => Role::ADMIN, 'name' => 'Alice'];
        $_SESSION['user'] = $userData;

        $this->assertSame($userData, $this->controller->exposedGetUser());
    }

    // -----------------------------------------------------------------------
    // getUserRole()
    // -----------------------------------------------------------------------

    public function testGetUserRoleReturnsAnonymousWhenNotLoggedIn(): void
    {
        $this->assertSame(Role::ANONYMOUS, $this->controller->exposedGetUserRole());
    }

    public function testGetUserRoleReturnsCorrectRole(): void
    {
        $_SESSION['user'] = ['role' => Role::ADMIN];

        $this->assertSame(Role::ADMIN, $this->controller->exposedGetUserRole());
    }

    // -----------------------------------------------------------------------
    // userCan()
    // -----------------------------------------------------------------------

    public function testUserCanReturnsFalseForAnonymousOnRestrictedPermission(): void
    {
        // Pas de session → rôle ANONYMOUS
        // On suppose qu'ANONYMOUS n'a pas la permission 'manage_users'
        $this->assertFalse($this->controller->exposedUserCan('manage_users'));
    }

    public function testUserCanReturnsTrueForAdminOnRestrictedPermission(): void
    {
        $_SESSION['user'] = ['role' => Role::ADMIN];

        // On suppose qu'ADMIN a la permission 'manage_users'
        $this->assertTrue($this->controller->exposedUserCan('manage_users'));
    }
}