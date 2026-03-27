<?php
    namespace Equipe4\Gigastage\Core;


    use PDO;


    class Database {


        private $pdo;


        public function __construct() {

            $user = "root";
            $password = "A2#DevWeb!";

            try{
                $this->pdo = new PDO("mysql:host=localhost;dbname=gigastage;charset=utf8", $user, $password);
            }
            catch (\PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }

        public function getConnection(){
            return $this->pdo;       
        }

    }

?>