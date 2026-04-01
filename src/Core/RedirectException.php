<?php
// src/Core/RedirectException.php

namespace Equipe4\Gigastage\Core;

/**
 * Exception levée lors d'une redirection.
 * Permet de tester les contrôleurs sans que exit() ne tue le processus PHPUnit.
 */
class RedirectException extends \RuntimeException
{
    public function __construct(public readonly string $url)
    {
        parent::__construct('Redirect to: ' . $url);
    }
}
