<?php

namespace App\Controllers;

use App\Models\User;

class SignUpController
{
    private $userModel;

    public function __construct(User $userModel)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If already logged in, redirect to home
        if (isset($_SESSION['user_id'])) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=home&action=index");
            exit();
        }

        $this->userModel = $userModel;
    }

    public function index()
    {
        include __DIR__ . '/../Views/signup.php';
    }

    /**
     * Handle user registration with comprehensive validation
     */
    public function signup($name, $email, $password)
    {
        // Sanitize inputs
        $name = trim($name);
        $email = trim(strtolower($email));
        $password = trim($password);

        // Validate all fields are present
        if (empty($name) || empty($email) || empty($password)) {
            $this->redirectWithError('empty_fields', 'All fields are required');
            return;
        }

        // Validate name
        $nameValidation = $this->validateName($name);
        if (!$nameValidation['valid']) {
            $this->redirectWithError('invalid_name', $nameValidation['message']);
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError('invalid_email', 'Please enter a valid email address');
            return;
        }

        // Additional email validation
        $emailValidation = $this->validateEmail($email);
        if (!$emailValidation['valid']) {
            $this->redirectWithError('invalid_email', $emailValidation['message']);
            return;
        }

        // Validate password strength
        $passwordValidation = $this->validatePassword($password);
        if (!$passwordValidation['valid']) {
            $this->redirectWithError('weak_password', $passwordValidation['message']);
            return;
        }

        // Check if email already exists
        if ($this->userModel->getUserByEmail($email)) {
            error_log("⚠️ Registration failed: Email already exists - " . $email);
            $this->redirectWithError('email_exists', 'This email is already registered');
            return;
        }

        // Get the 'user' role ID (typically RoleID = 2)
        $roleId = $this->userModel->getRoleIdByName('user');
        
        if (!$roleId) {
            error_log("❌ Failed to get 'user' role ID");
            $this->redirectWithError('registration_failed', 'System error. Please contact support');
            return;
        }

        // Create user account with RoleID
        $userId = $this->userModel->create($name, $email, $password, $roleId);

        if ($userId) {
            error_log("✅ New user registered: UserID=" . $userId . ", Email=" . $email);
            
            // Redirect to login page with success message (NO auto-login)
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&success=registered");
            exit();
        } else {
            error_log("❌ Failed to create user account for: " . $email);
            $this->redirectWithError('registration_failed', 'Registration failed. Please try again');
            return;
        }
    }

    /**
     * Validate name format and length
     */
    private function validateName($name)
    {
        if (strlen($name) < 2) {
            return ['valid' => false, 'message' => 'Name must be at least 2 characters long'];
        }

        if (strlen($name) > 100) {
            return ['valid' => false, 'message' => 'Name must not exceed 100 characters'];
        }

        if (!preg_match("/^[a-zA-Z\s\-'\.]+$/u", $name)) {
            return ['valid' => false, 'message' => 'Name can only contain letters, spaces, hyphens, and apostrophes'];
        }

        if (preg_match('/\s{2,}/', $name)) {
            return ['valid' => false, 'message' => 'Name contains excessive spaces'];
        }

        return ['valid' => true];
    }

    /**
     * Validate email format and domain
     */
    private function validateEmail($email)
    {
        if (strlen($email) > 255) {
            return ['valid' => false, 'message' => 'Email address is too long'];
        }

        if (preg_match('/[<>]/', $email)) {
            return ['valid' => false, 'message' => 'Email contains invalid characters'];
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }

        $domain = $parts[1];

        if (strpos($domain, '.') === false) {
            return ['valid' => false, 'message' => 'Email domain is invalid'];
        }

        return ['valid' => true];
    }

    /**
     * Validate password strength
     */
    private function validatePassword($password)
    {
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
        }

        if (strlen($password) > 128) {
            return ['valid' => false, 'message' => 'Password must not exceed 128 characters'];
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
        }

        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter'];
        }

        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one number'];
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one special character'];
        }

        $weakPasswords = ['password123', '12345678', 'qwerty123', 'admin123', 'welcome123'];
        if (in_array(strtolower($password), $weakPasswords)) {
            return ['valid' => false, 'message' => 'Password is too common. Please choose a stronger password'];
        }

        return ['valid' => true];
    }

    /**
     * Helper method to redirect with error message
     */
    private function redirectWithError($errorCode, $errorMessage)
    {
        $_SESSION['signup_error'] = $errorMessage;
        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=signup&action=index&error=" . $errorCode);
        exit();
    }
}