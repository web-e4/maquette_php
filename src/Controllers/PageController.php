<?php

namespace Equipe4\Gigastage\Controllers;

class PageController {


    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }


    public function cgu() {
        echo $this->twig->render('cgu.html.twig');
    }

    public function contact() {
        echo $this->twig->render('contact.html.twig');
    }

    public function mentionsLegales() {
        echo $this->twig->render('mentions-legales.html.twig');
    }
}
?>