<?php

namespace Sondage\Model;

/**
 * Représente le "Model", c'est à dire l'accès à la base de
 * données pour l'application cinéma basé sur MySQL
 */
class Model
{
    protected $pdo;

    public function __construct($host, $database, $user, $password)
    {
        try {
            $this->pdo = new \PDO(
                'mysql:dbname='.$database.';host='.$host,
                $user,
                $password
            );
            $this->pdo->exec('SET CHARSET UTF8');
        } catch (\PDOException $exception) {
            die('Impossible de se connecter au serveur MySQL');
        }
    }

    public function getSondage($id)
    {
        $sql = 
            'SELECT *
            FROM polls
            WHERE id = ?'
            ;

        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));
        return $query->fetch();
    }

    public function getAllSondage()
    {
        $sql = 
            'SELECT id, question
            FROM polls'
            ;

        $query = $this->pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function login($login,$pw)
    {
        if (!empty($login) && !empty($pw)) {
            $query = $this->pdo->prepare('SELECT * FROM users WHERE login=:login');
            $query->bindParam(':login',$login);
            $query->execute();
            if ($query->rowCount()) {
                $user = $query->fetch();
                if ($user['password'] == md5($pw)) {
                    $currentUser = $user;
                    return true;
                }
            }
        }
        return false;
    }

    public function signin($login,$pw)
    {
        if (!empty($login) && !empty($pw)) {
            $query = $this->pdo->prepare('SELECT COUNT(*) as nb FROM users WHERE login=:login');
            $query->bindParam(':login',$login);
            $query->execute();
            $cursor = $query->fetch();
            if ($cursor['nb'] == 0) {
                $query = $this->pdo->prepare('INSERT INTO users (login, password) VALUES (?,?)');
                $query->execute(array($login, md5($pw)));
                return true;
            }
        }
        return false;
    }

    public function addsondage($q,$a1,$a2,$a3)
    {
        if (!empty($q) && !empty($a1) && !empty($a2) && isset($a3)) {
            $req = $this->pdo->prepare("INSERT INTO polls (question,answer1,answer2,answer3) VALUES (:q,:asw1,:asw2,:asw3)");
            $req->bindParam(':q',$q);
            $req->bindParam(':asw1',$a1);
            $req->bindParam(':asw2',$a2);
            $req->bindParam(':asw3',$a3);
            $req->execute();
            return true;
        }
        return false;
    }
}
