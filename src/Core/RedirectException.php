<?php
// src/Core/RedirectException.php

namespace Equipe4\Gigastage\Core;

/**
 * Exception levée lors d'une redirection.
 * Permet aux tests PHPUnit d'attraper la redirection sans que exit() ne tue le processus.
 */
class RedirectException extends \RuntimeException
{
    public string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
        parent::__construct('Redirect to: ' . $url);
    }
}
