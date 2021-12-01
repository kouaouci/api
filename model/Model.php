<?php

namespace model;

require_once 'database.php';

abstract class Model
{
    protected $bdd;
    protected $table;

    public function __construct()
    {
        $this->bdd = getPdo();
    }

    // Check

    public function checkById($id)
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $statement->execute(array($id));
        $count = $statement->rowCount();
        return $count;
    }

    public function checkUserId($id)
    {
        $statement = $this->bdd->prepare("SELECT * FROM user WHERE id = ?");
        $statement->execute(array($id));
        $count = $statement->rowCount();
        return $count;
    }

    public function checkByEmail($email)
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $statement->execute(array($email));
        $count = $statement->rowCount();
        return $count;
    }

    public function checkByPseudo($pseudo)
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE pseudo = ?");
        $statement->execute(array($pseudo));
        $count = $statement->rowCount();
        return $count;
    }

    public function checkIfScoresByCategory($category)
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE category = ?");
        $statement->execute(array($category));
        $count = $statement->rowCount();
        return $count;
    }

    public function checkIfHasPlayedToday($id)
    {
        $date = date("Y-m-d");
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE user_id = ? AND date = ?");
        $statement->execute(array($id, $date));
        $count = $statement->rowCount();
        return $count;
    }

    public function checkIfHasPlayedTodayThatCategory($id, $category)
    {
        $date = date("Y-m-d");
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE user_id = ? AND date = ? AND category = ?");
        $statement->execute(array($id, $date, $category));
        $count = $statement->rowCount();
        return $count;
    }

    public function checkIfHasAlredayPlayed($id)
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $statement->execute(array($id));
        $count = $statement->rowCount();
        return $count;
    }

    public function checkIfHasPlayedhatCategory($id, $category)
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE user_id = ? AND category = ?");
        $statement->execute(array($id, $category));
        $count = $statement->rowCount();
        return $count;
    }

    // Get

    public function getOne($id)
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $statement->execute(array($id));
        $item = $statement->fetch();
        return $item;
    }

    public function getAll()
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} ORDER BY id DESC");
        $statement->execute();
        $items = $statement->fetchAll();
        return $items;
    }

    public function getAllFromUserByEmail($email)
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $statement->execute(array($email));
        $user = $statement->fetch();
        return $user;
    }

    public function getUserInfos($email)
    {
        $statement = $this->bdd->prepare("SELECT email, pseudo, id FROM {$this->table} WHERE email = ?");
        $statement->execute(array($email));
        $user = $statement->fetch();
        return $user;
    }

    public function getProfileImage($id)
    {
        $statement = $this->bdd->prepare("SELECT profile_image FROM {$this->table} WHERE id = ?");
        $statement->execute(array($id));
        $user = $statement->fetch();
        return $user;
    }

    public function getBestScoresByCategory($category)
    {
        $statement = $this->bdd->prepare("SELECT u.pseudo, s.category, s.score, s.date
        FROM score as s
        LEFT JOIN user as u ON s.user_id = u.id
        WHERE s.category = ?
        ORDER BY s.score
        DESC LIMIT 10");
        $statement->execute(array($category));
        $items = $statement->fetchAll();
        return $items;
    }

    public function getBestScoreOfTheDay($id)
    {
        $date = date("Y-m-d");
        $statement = $this->bdd->prepare(
            "SELECT category, score from {$this->table} 
            WHERE date = ? 
            AND user_id = ?
            ORDER BY score DESC LIMIT 1"
        );
        $statement->execute(array($date, $id));
        $items = $statement->fetch();
        return $items;
    }

    public function getBestScoreOfTheDayByCategory($id, $category)
    {
        $date = date("Y-m-d");
        $statement = $this->bdd->prepare(
            "SELECT user.pseudo, score.category, score.score as score, score.date from {$this->table} 
            LEFT JOIN user ON {$this->table}.user_id = user.id 
            WHERE score.date = ? 
            AND user.id = ?
            AND category = ? ORDER BY score.score DESC LIMIT 1"
        );
        $statement->execute(array($date, $id, $category));
        $items = $statement->fetch();
        return $items;
    }

    public function getBestScoreByUser($id)
    {
        $statement = $this->bdd->prepare(
            "SELECT user.pseudo, score.category, score.score as score, score.date from {$this->table} 
            LEFT JOIN user ON {$this->table}.user_id = user.id 
            WHERE user.id = ? ORDER BY score.score DESC LIMIT 1"
        );
        $statement->execute(array($id));
        $items = $statement->fetch();
        return $items;
    }

    public function getBestScoreByUserAndByCategory($id, $category)
    {
        $statement = $this->bdd->prepare(
            "SELECT user.pseudo, score.category, score.score as score, score.date 
            FROM {$this->table} 
            LEFT JOIN user ON {$this->table}.user_id = user.id 
            WHERE user.id = ?
            AND category = ?
            ORDER BY score.score DESC LIMIT 1"
        );
        $statement->execute(array($id, $category));
        $items = $statement->fetch();
        return $items;
    }

    public function getNumberOfExercicesDoneByUser($id)
    {
        $statement = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $statement->execute(array($id));
        $count = $statement->rowCount();
        return $count;
    }

    // Inscription

    public function inscription($pseudo, $email, $password)
    {
        $statement = $this->bdd->prepare("INSERT INTO {$this->table} (pseudo, email, password) VALUES (?,?,?)");
        $statement->execute(array($pseudo, $email, $password));
    }

    // Set 

    public function setScore($score, $category, $date, $id)
    {
        $statement = $this->bdd->prepare("INSERT INTO {$this->table} (score, category, date, user_id) VALUES (?,?,?,?)");
        $statement->execute(array($score, $category, $date, $id));
    }
}
