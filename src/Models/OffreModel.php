<?php


    namespace Equipe4\Gigastage\Models;

    use Equipe4\Gigastage\Core\Database;
    
    class OffreModel {


        private $database;

        public function __construct() {
            $this->database = new Database();
        }



        public function findXLast(int $x) {
            $pdo = $this->database->getConnection();

            $stmt = $pdo->prepare("SELECT id, titre, ville, duree FROM offres ORDER BY id DESC LIMIT $x");

            $stmt->execute();

            return $stmt->fetchAll();
        }


        // Récupère toutes les offres
        public function findAll() {
            $pdo = $this->database->getConnection();

            $stmt = $pdo->prepare("SELECT id, titre, ville, duree FROM offres ORDER BY id DESC LIMIT 999");

            $stmt->execute();

            return $stmt->fetchAll();
        }


        // Récupère une offre par son id
        public function findById(int $id) {

            $pdo = $this->database->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM offres WHERE id = :id");
            $stmt->execute(['id' => $id]);


            return $stmt->fetch();
        }

        // Sauvegarde une nouvelle offre
        public function create(array $data) {
        }

                
        public function compteColumn(string $table) {
            $tablesAutorisees = ['offres', 'entreprises', 'etudiants'];
            
            if (!in_array($table, $tablesAutorisees)) {
                throw new \Exception("Table non autorisée");
            }

            $pdo = $this->database->getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $stmt->execute();

            return $stmt->fetchColumn();
        }


        public function findPaginated(int $page, int $perPage, string $q = '', string $ville = '') {
            $pdo    = $this->database->getConnection();
            $offset = ($page - 1) * $perPage;

            $where  = 'WHERE 1=1';
            $params = [];

            if ($q !== '') {
                $where .= ' AND titre LIKE :q';
                $params['q'] = '%' . $q . '%';
            }

            if ($ville !== '') {
                $where .= ' AND ville LIKE :ville';
                $params['ville'] = '%' . $ville . '%';
            }

            // Nombre total de résultats
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM offres $where");
            $stmtCount->execute($params);
            $total = (int) $stmtCount->fetchColumn();

            // Offres de la page courante
            $params['limit']  = $perPage;
            $params['offset'] = $offset;

            $stmt = $pdo->prepare("SELECT id, titre, ville, duree FROM offres $where ORDER BY id DESC LIMIT :limit OFFSET :offset");

            // bindValue nécessaire pour les entiers avec PDO
            foreach ($params as $key => $value) {
                if ($key === 'limit' || $key === 'offset') {
                    $stmt->bindValue(":$key", $value, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }

            $stmt->execute();
            $offres = $stmt->fetchAll();

            return [
                'offres'     => $offres,
                'total'      => $total,
                'totalPages' => (int) ceil($total / $perPage),
                'currentPage'=> $page,
            ];
        }
    }

?>