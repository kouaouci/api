<?php

namespace controller;

use DateTime;

require_once 'utils/functions.php';

abstract class Controller
{
    protected $modelName;

    public function __construct()
    {
        $this->model = new $this->modelName;
    }

    public function inscription()
    {
        if ($_POST['pseudo'] && $_POST['email'] && $_POST['password']) {
            $pseudo = verifyInput($_POST['pseudo']);
            $email = verifyInput($_POST['email']);
            $password = verifyInput($_POST['password']);
            if (strlen($pseudo) > 20) {
                $res = "Le pseudo ne peut dépasser 20 caractères";
                return $res;
            }
            $checkPseudo = $this->model->checkByPseudo($pseudo);
            if ($checkPseudo > 0) {
                $res = "Ce pseudo est déjà utilisé";
                return $res;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $res = "adresse email invalide";
                return $res;
            }
            $checkEmail = $this->model->checkByEmail($email);
            if ($checkEmail > 0) {
                $res = "Cet email est déjà utilisé";
                return $res;
            }
            $password = password_hash($password, PASSWORD_DEFAULT);
            $this->model->inscription($pseudo, $email, $password);
            $res = "success";
            return $res;
        } else {
            $res = "veuillez renseigner tous les champs";
            return $res;
        }
    }

    public function connexion()
    {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = verifyInput($_POST['email']);
            $password = verifyInput($_POST['password']);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $check = $this->model->checkByEmail($email);
                if ($check > 0) {
                    $user = $this->model->getAllFromUserByEmail($email);
                    $passwordHashed = $user['password'];
                    if (password_verify($password, $passwordHashed)) {
                        $res = $this->model->getUserInfos($email);
                        return $res;
                    } else {
                        $res = "Mauvais mot de passe";
                        return $res;
                    }
                } else {
                    $res = "Mauvais email";
                    return $res;
                }
            } else {
                $res = "Adresse email invalide";
                return $res;
            }
        } else {
            $res = "Veuillez renseigner tous les champs";
            return $res;
        }
    }

    // Scores

    public function setScore()
    {
        if (isset($_POST['score']) && isset($_POST['category']) && isset($_POST['id'])) {
            $score = verifyInput($_POST['score']);
            $category = urldecode(verifyInput($_POST['category']));
            $id = (int)verifyInput($_POST['id']);
            $date = date("Y-m-d");
            $checkId = $this->model->checkUserId($id);
            if ($checkId === 0) {
                $res = "Utilisateur non existant";
                return $res;
            }
            $this->model->setScore($score, $category, $date, $id);
            $res = "succes";
            return $res;
        } else {
            $res = "Tous les champs sont vides";
            return $res;
        }
    }
}
