
<?php 

class LoginController {
    private $userModel;

    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['UserPassword'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: ../public/index.php");
            exit();
        } else {
            echo "Invalid email or password.";
            include __DIR__ . '/../Views/login.php';
        }
    }
}