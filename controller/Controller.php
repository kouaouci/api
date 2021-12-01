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

    // Update profile image

    public function updateProfileImage($id)
    {
        if (isset($_FILES['image'])) {
            $image = verifyInput($_FILES['image']['name']);
            $directory = "assets/images/";
            $imagePath = $directory . basename($image);
            $imageSize = $_FILES['image']['size'];
            $imageTmpName = $_FILES['image']['tmp_name'];
            $imageExtension = pathinfo($imagePath, PATHINFO_EXTENSION);
            $imageRandName = $this->rand_str() . "." . $imageExtension;
            if (
                $imageExtension  != "jpg"
                && $imageExtension  != "jpeg"
                && $imageExtension  != "png"
            ) {
                $msg = "Les fichiers autorisés sont: .jpg, .jpeg, .png";
                return $msg;
            }
            if ($imageSize > 2000000) {
                $msg = "Un fichier image ne doit pas dépasser 2Mo";
                return $msg;
            }
            if (file_exists($imagePath)) {
                $msg = "Ce fichier a déjà été uploadé";
                return $msg;
            }
            if (move_uploaded_file($imageTmpName, $directory . $imageRandName)) {
                $url = "https://api-drum-sensei.anthony-charretier.fr/assets/images/";
                $newImageName = $url . $imageRandName;
                $this->model->updateProfileImage($newImageName, $id);
                $res = "success";
                return $res;
            } else {
                $msg = "Il y a eu une erreur lors de l\'upload";
                return $msg;
            }
        } else {
            $res = "veuillez uploader une image";
            return $res;
        }
    }

    public function rand_str($length = 30)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
