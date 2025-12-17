<?php

namespace App\Controllers;

use App\Models\User;

class LoginController
{
    private $userModel;

    public function __construct(User $userModel)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->userModel = $userModel;
    }

    public function index()
    {
        // Check if already logged in when viewing the login page
        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            $this->redirectBasedOnRole($_SESSION['role']);
        }

        // Initialize error variable for the view
        $error = null;
        $success = null;
        
        // Check for error messages in URL
        if (isset($_GET['error'])) {
            $errorMessages = [
                'invalid_credentials' => 'Invalid email or password',
                'empty_fields' => 'Please fill in all fields',
                'unauthorized' => 'You do not have permission to access that page',
                'session_expired' => 'Your session has expired. Please log in again.'
            ];
            
            $error = $errorMessages[$_GET['error']] ?? 'An error occurred';
        }

        // Check for success messages (e.g., after signup)
        if (isset($_GET['success'])) {
            $successMessages = [
                'registered' => 'Account created successfully! Please log in.'
            ];
            
            $success = $successMessages[$_GET['success']] ?? 'Success!';
        }
        
        include __DIR__ . '/../Views/login.php';
    }

    public function login($email = null, $password = null)
    {
        // Get from POST if not passed as parameters
        if ($email === null || $password === null) {
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
        }

        // Validate input
        if (empty($email) || empty($password)) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=empty_fields");
            exit();
        }

        // Authenticate user
        $user = $this->userModel->getUserByEmail($email);

        if ($user && isset($user['Password']) && password_verify($password, $user['Password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['name'] = $user['Name'];
            $_SESSION['last_activity'] = time();
            $_SESSION['last_activity_check'] = time();

            // DEBUG: Log what role is being set
            error_log("✅ User logged in: UserID=" . $user['UserID'] . ", Email=" . $email . ", Role=" . $user['Role']);
            error_log("DEBUG Session contents: " . print_r($_SESSION, true));

            // Redirect based on user role
            $this->redirectBasedOnRole($user['Role']);
        } else {
            error_log("❌ Failed login attempt for: " . $email);
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=invalid_credentials");
            exit();
        }
    }

    /**
     * Redirect user based on their role
     */
    private function redirectBasedOnRole($role)
    {
        error_log("DEBUG redirectBasedOnRole called with role: " . $role);
        
        switch (strtolower($role)) {
            case 'admin':
                error_log("DEBUG Redirecting to admin");
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index");
                exit();
            
            case 'staff':
                error_log("DEBUG Redirecting to staff");
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index");
                exit();
            
            case 'guest_staff':
                error_log("DEBUG Redirecting to guest_staff");
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations");
                exit();
            
            case 'user':
            default:
                error_log("DEBUG Redirecting to user/home");
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=home&action=index");
                exit();
        }
    }
}