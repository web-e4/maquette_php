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

    // GET /apply?id=x
    public function show(int $id): void
    {
        $this->requirePermission(Permission::APPLICATION_APPLY);

        $offer = $this->applicationModel->findOfferById($id);

        if (!$offer) {
            http_response_code(404);
            echo '404 - Offer not found';
            return;
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $idUser = $_SESSION['user']['id'];
        $alreadyApplied = $this->applicationModel->hasAlreadyApplied($idUser, $id);
        $application = $alreadyApplied ? $this->applicationModel->findApplication($idUser, $id) : null;
        $profile = $this->profileModel->findById($idUser);

        $publicBase = __DIR__ . '/../../public/';
        $resumeExists = $application && $application['resume']
            && file_exists($publicBase . $application['resume']);

        $this->render('pages/apply.html.twig', [
            'offer' => $offer,
            'flash' => $flash,
            'alreadyApplied' => $alreadyApplied,
            'application' => $application,
            'profile' => $profile,
            'resumeExists' => $resumeExists,
        ]);
    }

    // POST /apply/submit
    public function submit(int $id): void
    {
        $this->requirePermission(Permission::APPLICATION_APPLY);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }

        $this->validateCsrfToken();

        $idUser = $_SESSION['user']['id'];

        if ($this->applicationModel->hasAlreadyApplied($idUser, $id)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Vous avez déjà postulé à cette offre.'];
            $this->redirect('/apply/' . $id);
        }

        $uploadDir = __DIR__ . '/../../public/uploads/applications/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

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

    // POST /apply/update
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

        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > $maxSize) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') return null;

        // vérification du MIME type réel (pas seulement l'extension)
        $mime = mime_content_type($file['tmp_name']);
        if ($mime !== 'application/pdf') return null;

        $filename = uniqid('doc_', true) . '.pdf';
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) return null;

        return 'uploads/applications/' . $filename;
    }
}
