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
                    return $currentUser;
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

    public function addsondage($q,$a1,$a2,$a3,$auteur)
    {
        if (!empty($q) && !empty($a1) && !empty($a2) && !empty($a3) && !empty($auteur)) {
            $req = $this->pdo->prepare("INSERT INTO polls (question,answer1,answer2,answer3,auteur) VALUES (:q,:asw1,:asw2,:asw3,:auteur)");
            $req->bindParam(':q',$q);
            $req->bindParam(':asw1',$a1);
            $req->bindParam(':asw2',$a2);
            $req->bindParam(':asw3',$a3);
            $req->bindParam(':auteur',$auteur);
            $req->execute();
            return true;
        }
        return false;
    }

    public function addAnswer($q,$a1,$a2,$a3)
    {
        if (!empty($idu) && !empty($idq) && !empty($asw)) {
            $req = $this->pdo->prepare("INSERT INTO answers (user_id, poll_id, answer) VALUES (:idu,:idq,:asw)");
            $req->bindParam(':idu',$idu);
            $req->bindParam(':idq',$idq);
            $req->bindParam(':asw',$asw);
            $req->execute();
            return true;
        }
        return false;
    }

    public function findAnswer($id_question, $id_user){
        if (!empty($id_question) && !empty($id_user)) {
            $req = $this->pdo->prepare("SELECT count(*) as nb FROM answers WHERE user_id=:idu AND poll_id=:idq");
            $req->bindParam(':idq',$id_question);
            $req->bindParam(':idu',$id_user);
            $req->execute();
            $cursor = $req->fetch();

            if($cursor['nb'] > 0)
                return true;
        }
        return false;
    }

    public function findAllAnswersId($id_question){
        if (!empty($id_question)) {
            $req = $this->pdo->prepare("SELECT * FROM polls WHERE id=:idq");
            $req->bindParam(':idq',$id_question);
            $req->execute();
            $cursor = $req->fetch();
            return $cursor;
        }
        return false;
    }

    public function findAllAnswers($id_question){
        if (!empty($id_question)) {
            $result = array();

            $sql = "SELECT polls.id, polls.question, polls.answer1, polls.answer2, polls.answer3, polls.auteur, count(answer) as nb FROM answers INNER JOIN polls ON answers.poll_id=polls.id WHERE poll_id=:idq GROUP BY answer";
            $req = $this->pdo->prepare($sql);
            $req->bindParam(':idq',$id_question);
            $req->execute();
            $cursor = $req->fetch();

            //var_dump($cursor);

            $result['id'] = $cursor['id'];
            $result['question'] = $cursor['question'];
            $result['answer1'] = $cursor['answer1'];
            $result['answer2'] = $cursor['answer2'];
            $result['answer3'] = $cursor['answer3'];
            $result['auteur'] = $cursor['auteur'];

            $sql = "SELECT count(answer) as nb FROM answers WHERE poll_id=:idq GROUP BY answer";
            $req = $this->pdo->prepare($sql);
            $req->bindParam(':idq',$id_question);
            $req->execute();
            $cursor = $req->fetchAll();

            
            if(isset($cursor[0]['nb']))
                $result['nb1'] = $cursor[0]['nb'];
            if(isset($cursor[1]['nb']))
                $result['nb2'] = $cursor[1]['nb'];
            if(isset($cursor[2]['nb']))
                $result['nb3'] = $cursor[2]['nb'];

            //var_dump($result);

            return $result;
        }
        return false;
    }

    public function totalAnswers($id_question){
        if (!empty($id_question)) {
            $req = $this->pdo->prepare("SELECT count(answer) as nb FROM answers WHERE poll_id=:idq");
            $req->bindParam(':idq',$id_question);
            $req->execute();
            $cursor = $req->fetch();
            return $cursor;
        }
        return false;
    }

    public function putAnswer($id_user,$id_question,$id_answer){
        if (!empty($id_user) && !empty($id_question) && !empty($id_answer)){
            $sql = "INSERT INTO answers (user_id,poll_id,answer) VALUES (:idu,:idq,:ida)";
            $req = $this->pdo->prepare($sql);
            $req->bindParam(':idu',$id_user);
            $req->bindParam(':idq',$id_question);
            $req->bindParam(':ida',$id_answer);
            return $req->execute();
        }
        return false;
    }

}
