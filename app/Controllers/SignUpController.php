
<?php 

class SignUpController {
    private $userModel;

    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    public function signup($name, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        if ($this->userModel->create($email, $name, $hashedPassword)) {
            header("Location: ../views/login.php");
            exit();
        } else {
            echo "Error during sign up. Please try again.";
            include __DIR__ . '/../Views/signup.php';
        }
    }
}