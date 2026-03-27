<?php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Models\OffreModel;

class OffreController {


    private $offreModel;
    private $twig;


    public function __construct($twig) {
        $this->twig = $twig;
        $this->offreModel = new OffreModel();
    }

    public function home() {
        // Page d'accueil → récupère les dernières offres et les affiche

        
        $offres = $this->offreModel->findXLast(6);

        $stats = [
            "offres"      => $this->offreModel->compteColumn("offres"),
            "entreprises" => $this->offreModel->compteColumn("entreprises"),
            "etudiants"   => $this->offreModel->compteColumn("etudiants"),
        ];


        echo $this->twig->render('accueil.html.twig', [
            'offres' => $offres,
            'stats' => $stats

        ]);


    }

    public function index() {
        $q      = $_GET['q']    ?? '';
        $ville  = $_GET['ville'] ?? '';
        $page   = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 15;

        $result = $this->offreModel->findPaginated($page, $perPage, $q, $ville);

        echo $this->twig->render('annonces.html.twig', [
            'offres'     => $result['offres'],
            'q'          => $q,
            'ville'      => $ville,
            'pagination' => [
                'totalPages'  => $result['totalPages'],
                'currentPage' => $result['currentPage'],
                'total'       => $result['total'],
            ],
        ]);
    }

    public function detail(int $id) {
        // Page détail → récupère UNE offre par son id et l'affiche

        $offre = $this->offreModel->findById($id);


        echo $this->twig->render('detail-offre.html.twig', [
            'offre' => $offre,
        ]);

    }

    public function create() {
        // Affiche le formulaire "publier une offre"

        echo $this->twig->render('publier.html.twig');
    }

    public function apply() {
        // Affiche le formulaire "publier une offre"

        echo $this->twig->render('postuler.html.twig');
    }

    public function store() {
        // Reçoit et sauvegarde en BDD les données du formulaire
    }
}
?>