<?php

declare(strict_types=1);

namespace App\Controller;

use Lime\Controller;
use Lime\Database;
use Valitron\Validator;

class AuthController extends Controller
{
    public function login(): void
    {
        session_start();

        if (isset($_SESSION['user_id'])) {
            $this->redirect('/chat');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $v = new Validator($_POST);
            $v->rule('required', ['email', 'password']);
            $v->rule('email', 'email');

            if ($v->validate()) {
                $email    = trim($_POST['email']);
                $password = trim($_POST['password']);

                $user = Database::fetch('SELECT * FROM users WHERE email = ?', [$email]);

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id']   = (int) $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];

                    Database::execute(
                        'UPDATE users SET status = ? WHERE id = ?',
                        ['online', $user['id']]
                    );

                    $this->redirect('/chat');
                }

                $errors[] = 'Invalid email or password.';
            } else {
                $errors = $v->errors();
            }
        }

        $this->view('auth/login', ['errors' => $errors]);
    }

    public function logout(): void
    {
        session_start();

        if (isset($_SESSION['user_id'])) {
            Database::execute(
                'UPDATE users SET status = ? WHERE id = ?',
                ['offline', $_SESSION['user_id']]
            );
        }

        $_SESSION = [];
        session_destroy();

        $this->redirect('/');
    }
}
