<?php

class AuthController
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function login()
    {
        $error = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $userModel = new User($this->db);
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user'] = $user;
                header("Location: index.php?controller=home&action=index");
                exit;
            } else {
                $error = "Invalid email or password";
                require __DIR__ . "/../Views/auth/login.php";
                return;
            }
        }

        require __DIR__ . "/../Views/auth/login.php";
    }

    public function register()
    {
        $error = "";
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo "Form submitted!<br>";
            var_dump($_POST);
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            $userModel = new User($this->db);
            $existingUser = $userModel->findByEmail($email);

            if ($existingUser) {
                $error = "Email already registered";
                require __DIR__ . "/../Views/auth/register.php";
                return;
            } else {
                if ($userModel->create($email, $name, $password)) {
                    header("Location: ../views/login.php");
                    exit;
                } else {
                    $error = "Failed to create account. Please try again.";
                    require __DIR__ . "/../Views/auth/register.php";
                    return;
                }
            }
        }
        require __DIR__ . "/../Views/auth/register.php";
    }
}
