<?php

// Allow from any origin
if (isset($_SERVER["HTTP_ORIGIN"])) {
    // You can decide if the origin in $_SERVER['HTTP_ORIGIN'] is something you want to allow, or as we do here, just allow all
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    //No HTTP_ORIGIN set, so we allow any. You can disallow if needed here
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 600");    // cache for 10 minutes

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"]))
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT"); //Make sure you remove those you do not want to support

    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_HEADERS"]))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    //Just exit with 200 OK with the above headers for OPTIONS method
    exit(0);
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('utils/functions.php');
require_once('utils/autoload.php');
require_once('secret.php');

$headers = apache_request_headers();
if (isset($headers['Authorization'])) {
    session_id($headers['Authorization']);
}

if (isset($_GET['controller'])) {
    $controller = verifyInput($_GET['controller']);
    if (isset($_GET['action'])) {
        $action = verifyInput($_GET['action']);
        if ($action === "inscription") {
            if ($controller === "user") {
                $controller = new \controller\User();
                $res = $controller->inscription();
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            }
        } elseif ($action === "create") {
            if ($controller === "score") {
                session_start();
                if (isset($_SESSION['email'])) {
                    $controller = new \controller\Score();
                    $res = $controller->setScore();
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                } else echo "You're not connected";
            }
        } elseif ($action === "update") {
            if (isset($_GET['id'])) {
                $id = verifyInput($_GET['id']);
                if (!is_nan($id)) {
                    if ($controller === "user") {
                        // session_start();
                        // if (isset($_SESSION['email'])) {
                        //     $controller = new \controller\User();
                        //     $res = $controller->updateUser($id);
                        //     echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        // }
                    }
                } else {
                    $res = "L'id doit être de type number";
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $res = "Veuillez spécifier un id";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            }
        } elseif ($action === "delete") {
            if (isset($_GET['id'])) {
                $id = verifyInput($_GET['id']);
                if (!is_nan($id)) {
                    if ($controller === "user") {
                        // session_start();
                        // if (isset($_SESSION['email'])) {
                        //     $controller = new \controller\User();
                        //     $res = $controller->deleteUser($id);
                        //     echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        // } else {
                        //     $res = "Vous devez être connecté";
                        //     echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        // }
                    }
                } else {
                    $res = "L'id doit être de type number";
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $res = "Veuillez spécifier un id";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            }
        } elseif ($action === "connexion") {
            if ($controller === "user") {
                $controller = new \controller\User();
                $res = $controller->connexion();
                session_start();
                $_SESSION['id'] = $res['id'];
                $_SESSION['email'] = $res['email'];
                $res["sessionId"] = session_id();
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            }
        }
    } else {
        $res = "Vous devez définir l'action que vous souhaitez effectuer";
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
} elseif (isset($_GET['model'])) {
    $model = verifyInput($_GET['model']);
    if (isset($_GET['action'])) {
        $action = verifyInput($_GET['action']);
        if ($action === "getOne") {
            if (isset($_GET['id'])) {
                $id = verifyInput($_GET['id']);
                if (!is_nan($id)) {
                    if ($model === "user") {
                        session_start();
                        if (isset($_SESSION['email'])) {
                            $model = new \model\User();
                            $user = $model->getOne($id);
                            echo json_encode($user, JSON_UNESCAPED_UNICODE);
                        } else echo "You're not connected";
                    }
                } else {
                    $res = "L'id doit être de type number";
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $res = "Veuillez spécifier un id";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            }
        } elseif ($action === "getAll") {
            if ($model === "user") {
                session_start();
                if (isset($_SESSION['email'])) {
                    $model = new \model\User();
                    $users = $model->getAll();
                    echo json_encode($users, JSON_UNESCAPED_UNICODE);
                } else echo "You're not connected";
            }
        } else if ($action === "getBestScoresByCategory") {
            if ($model === "score") {
                $model = new \model\Score();
                $category = urldecode(verifyInput($_GET['category']));
                $checkScoresByCategory = $model->checkIfScoresByCategory($category);
                if ($checkScoresByCategory > 0) {
                    $scores = $model->getBestScoresByCategory($category);
                    echo json_encode($scores, JSON_UNESCAPED_UNICODE);
                }
            }
        } else if ($action === "getBestScoreOfTheDay") {
            if ($model === "score") {
                if (!empty($_GET['userId'])) {
                    session_start();
                    if (isset($_SESSION['email'])) {
                        $model = new \model\Score();
                        $id = (int)verifyInput($_GET['userId']);
                        $checkIfHasPlayedToday = $model->checkIfHasPlayedToday($id);
                        if ($checkIfHasPlayedToday > 0) {
                            $res = $model->getBestScoreOfTheDay($id);
                            echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        } else {
                            $res = "No score";
                            echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        }
                    } else echo "You're not connected";
                } else {
                    $res = "Veuillez spécifier un user id";
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                }
            }
        } else if ($action === "getBestScoreOfTheDayByCategory") {
            if ($model === "score") {
                if (!empty($_GET['userId']) && !empty($_GET['category'])) {
                    session_start();
                    if (isset($_SESSION['email'])) {
                        $model = new \model\Score();
                        $id = (int)verifyInput($_GET['userId']);
                        $category = urldecode(verifyInput($_GET['category']));
                        $checkIfHasPlayedTodayThatCategory = $model->checkIfHasPlayedTodayThatCategory($id, $category);
                        if ($checkIfHasPlayedTodayThatCategory > 0) {
                            $res = $model->getBestScoreOfTheDayByCategory($id, $category);
                            echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        } else {
                            $res = "No score";
                            echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        }
                    } else echo "You're not connected";
                } else {
                    $res = "Veuillez spécifier un user id et une catégorie";
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                }
            }
        } else if ($action === "getBestScoreByUser") {
            if ($model === "score") {
                if (!empty($_GET['userId'])) {
                    session_start();
                    if (isset($_SESSION['email'])) {
                        $model = new \model\Score();
                        $id = (int)verifyInput($_GET['userId']);
                        $checkIfHasAlredayPlayed = $model->checkIfHasAlredayPlayed($id);
                        if ($checkIfHasAlredayPlayed > 0) {
                            $res = $model->getBestScoreByUser($id);
                            echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        } else {
                            $res = "No score";
                            echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        }
                    } else echo "You're not connected";
                } else {
                    $res = "Veuillez spécifier un user id et une catégorie";
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                }
            }
        } else if ($action === "getBestScoreByUserAndByCategory") {
            if ($model === "score") {
                if (!empty($_GET['userId'])) {
                    session_start();
                    if (isset($_SESSION['email'])) {
                        $model = new \model\Score();
                        $id = (int)verifyInput($_GET['userId']);
                        $category = urldecode(verifyInput($_GET['category']));
                        $checkIfHasAlredayPlayed = $model->checkIfHasAlredayPlayed($id);
                        if ($checkIfHasAlredayPlayed > 0) {
                            $checkIfHasPlayedhatCategory = $model->checkIfHasPlayedhatCategory($id, $category);
                            if ($checkIfHasPlayedhatCategory > 0) {
                                $res = $model->getBestScoreByUserAndByCategory($id, $category);
                                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                            } else {
                                $res = "No score";
                                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                            }
                        } else {
                            $res = "No score at all";
                            echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        }
                    } else echo "You're not connected";
                } else {
                    $res = "Veuillez spécifier un user id et une catégorie";
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                }
            }
        } else if ($action === "getNumberOfExercicesDoneByUser") {
            if ($model === "score") {
                if (!empty($_GET['userId'])) {
                    session_start();
                    if (isset($_SESSION['email'])) {
                        $model = new \model\Score();
                        $id = (int)verifyInput($_GET['userId']);
                        $res = $model->getNumberOfExercicesDoneByUser($id);
                        echo json_encode($res, JSON_UNESCAPED_UNICODE);
                    } else echo "You're not connected";
                } else {
                    $res = "Veuillez spécifier un user id et une catégorie";
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                }
            }
        }
    }
} elseif (isset($_GET['contact'])) {
    $contact = verifyInput($_GET['contact']);
    if ($contact = "sendEmail") {
        if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['message']) && isset($_POST['recaptcha'])) {
            $name = verifyInput($_POST['name']);
            $email = verifyInput($_POST['email']);
            $message = verifyInput($_POST['message']);
            $recaptcha = verifyInput($_POST['recaptcha']);
            if (strlen($name) < 2) {
                $res = "Le nombre de caractères du nom doit être supérieur à 2";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            }
            if (strlen($name) > 20) {
                $res = "Le nombre de caractères du nom doit être inférieur à 15";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $res = "Adresse email invalide";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            }
            if (isset($_POST['recaptcha'])) {
                $captcha = $_POST['recaptcha'];
                if (!$captcha) {
                    $msg = "Veuillez valider le captcha";
                } else {
                    $secretKey;
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
                    $response = file_get_contents($url);
                    $responseKeys = json_decode($response, true);
                    if ($responseKeys["success"]) {
                        $emailText = "Name: " . $name . "\n\n";
                        $emailText .= "Email: " . $email . "\n\n";
                        $emailText .= "Message: " . $message . "\n";
                        $headers = "From: " . $name . " <" . $webSite . ">";
                        mail($emailTo, "Un message de " . $webSite, $emailText, $headers);
                        $res = "success";
                        echo json_encode($res, JSON_UNESCAPED_UNICODE);
                    } else {
                        $res = "Non au spam";
                        echo json_encode($res, JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        } else {
            $res = "Veuillez remplir tous les champs";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
        }
    } else {
        $res = "Vous devez faire erreur";
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
} else {
    $res = "Vous devez définir ce que vous souhaitez faire ou récupérer";
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
}
