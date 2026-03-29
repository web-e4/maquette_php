<?php
namespace Equipe4\Gigastage\Tests;

use PHPUnit\Framework\TestCase;

class ExempleTest extends TestCase {
    public function testCalcul() {
        $resultat = 1 + 1;
        $this->assertEquals(2, $resultat);
    }
}