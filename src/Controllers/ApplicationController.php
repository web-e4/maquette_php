<?php
// src/Controllers/ApplicationController.php

namespace Equipe4\Gigastage\Controllers;

use Equipe4\Gigastage\Core\Permission;
use Equipe4\Gigastage\Models\ApplicationModel;
use Equipe4\Gigastage\Models\ProfileModel;

class ApplicationController extends AbstractController
{
    private ApplicationModel $applicationModel;
    private ProfileModel $profileModel;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->applicationModel = new ApplicationModel();
        $this->profileModel = new ProfileModel();
    }

    // id de l'offre pour laquelle l'étudiant veut postuler, récupéré via l'URL (ex: /apply?id=5)
    public function show(int $id): void
    {
        $this->requirePermission(Permission::APPLICATION_APPLY); //verifie le droit de postuler
        // requete SQL pour les infos de l'offre via l'id
        $offer = $this->applicationModel->findOfferById($id);
        // si pas d'offre trouvée, affiche une page 404
        if (!$offer) {
            http_response_code(404);
            echo '404 - Offer not found';
            return;
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $idUser         = $_SESSION['user']['id'];
        $alreadyApplied = $this->applicationModel->hasAlreadyApplied($idUser, $id); // verif si user deja postulé
        $application    = $alreadyApplied ? $this->applicationModel->findApplication($idUser, $id) : null; //recup les infos 
        $profile        = $this->profileModel->findById($idUser); // recup le profil de l'étudiant pour pre-remplir le formulaire (ex: son nom dans la lettre de motivation)

        $publicBase = __DIR__ . '/../../public/';
        $resumeExists = $application && $application['resume']
            && file_exists($publicBase . $application['resume']);

         // affiche la page de candidature avec les données de l'offre, du profil, etc
         $this->render('pages/apply.html.twig', [
            'offer'          => $offer,
            'flash'          => $flash,
            'alreadyApplied' => $alreadyApplied,
            'application' => $application,
            'profile' => $profile,
            'resumeExists' => $resumeExists,
        ]);
    }

    // 
    public function submit(int $id): void
       //verifie le droit de postuler
        $this->requirePermission(Permission::APPLICATION_APPLY);
        // si c'est pas POST redirige
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }

        $this->validateCsrfToken();

        $idUser = $_SESSION['user']['id'];
        // verif si l'étudiant a déjà postulé pour cette offre, si oui message flash d'erreur et redirection vers la page de candidature
        if ($this->applicationModel->hasAlreadyApplied($idUser, $id)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Vous avez déjà postulé à cette offre.'];
            $this->redirect('/apply/' . $id);
        }
        // definit le stockage des fichiers, s'il n'existe pas il le créé
        $uploadDir = __DIR__ . '/../../storage/applications/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        //Upload le CV. Si l'upload échoue → message d'erreur
        $cvPath = $this->uploadFile($_FILES['cv'], $uploadDir);
        if (!$cvPath) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'CV invalide ou trop volumineux (PDF, 5 Mo max).'];
            $this->redirect('/apply/' . $id);
        }

        $letter = trim($_POST['letter'] ?? '');

        $this->applicationModel->createApplication([
            'idUser' => $idUser,
            'idOffer' => $id,
            'resume' => $cvPath,
            'motivationLetter' => $letter !== '' ? $letter : null,
            'applicationDate' => date('Y-m-d'),
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Votre candidature a été envoyée avec succès !'];
        $this->redirect('/apply/' . $id);
    }

    // 
    public function update(int $id): void
    {
        $this->requirePermission(Permission::APPLICATION_APPLY);

        $this->validateCsrfToken();

        header('Content-Type: application/json');

        $idUser = $_SESSION['user']['id'];
        $field = $_POST['_field'] ?? '';

        // mise à jour de la lettre de motivation (texte)
        if ($field === 'letter') {
            $text = trim($_POST['letter_text'] ?? '');
            $this->applicationModel->updateApplication($idUser, $id, 'motivationLetter', $text !== '' ? $text : null);
            echo json_encode(['success' => true, 'newValue' => $text]);
            return;
        }

        // remplacement du CV (fichier PDF)
        if ($field === 'cv') {
            if (empty($_FILES['cv']['name'])) {
                echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu.']);
                return;
            }

            $uploadDir = __DIR__ . '/../../public/uploads/applications/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // sauvegarde du nouveau fichier en premier
            $path = $this->uploadFile($_FILES['cv'], $uploadDir);
            if (!$path) {
                echo json_encode(['success' => false, 'message' => 'Fichier invalide (PDF, 5 Mo max).']);
                return;
            }

            // suppression de l'ancien fichier seulement après succès
            $app = $this->applicationModel->findApplication($idUser, $id);
            $old = $app['resume'] ?? null;
            if ($old) {
                $full = __DIR__ . '/../../public/' . $old;
                if (file_exists($full)) unlink($full);
            }

            $this->applicationModel->updateApplication($idUser, $id, 'resume', $path);
            echo json_encode(['success' => true, 'newValue' => basename($path)]);
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Champ invalide.']);
    }

    private function uploadFile(array $file, string $dir): ?string
    {
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) return null; // verifie si y a une erreur lors de l'upload
        if ($file['size'] > $maxSize) return null; // verifie la taille du fichier
        // evite les fichier hybride
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); // verifie l'extension du fichier grace a pathinfo et strtolower pour eviter les problèmes d'extensions en majuscules (ex: CV.PDF)
        if ($ext !== 'pdf') return null;

        // vérification du MIME type réel (pas seulement l'extension)
        $mime = mime_content_type($file['tmp_name']);
        if ($mime !== 'application/pdf') return null;

        $filename = uniqid('doc_', true) . '.pdf'; // génère un nom de fichier unique pour éviter les conflits
        move_uploaded_file($file['tmp_name'], $dir . $filename); // déplace le fichier uploadé vers le dossier de stockage

        return 'uploads/applications/' . $filename;
    }
}
