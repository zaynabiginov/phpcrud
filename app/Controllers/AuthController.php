<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\View;

final class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 3;
    private const LOGIN_LOCK_SECONDS = 60;

    public function login(): void
    {
        if (current_user()) {
            $this->redirect(url(home_url()));
        }

        $now = time();
        $attempts = (int) ($_SESSION['login_attempts'] ?? 0);
        $lockUntil = (int) ($_SESSION['login_lock_until'] ?? 0);
        if ($lockUntil > 0 && $lockUntil <= $now) {
            $attempts = 0;
            $lockUntil = 0;
            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_lock_until'] = 0;
        }

        $error = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if ($lockUntil > $now) {
                $error = sprintf(t('login_locked'), max(1, $lockUntil - $now));
            } else {
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $pdo = Database::pdo();
                $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['login_lock_until'] = 0;
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_regenerate_id(true);
                    }
                    $_SESSION['user'] = ['id' => (int) $user['id'], 'username' => $user['username'], 'role' => $user['role']];
                    try {
                        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
                        $log = $pdo->prepare("INSERT INTO user_sessions (user_id, login_at, ip) VALUES (?, NOW(), ?)");
                        $log->execute([(int) $user['id'], $ip !== '' ? $ip : null]);
                        $_SESSION['login_log_id'] = (int) $pdo->lastInsertId();
                    } catch (\Throwable $e) {
                        // do not block login
                    }
                    $this->redirect(url(home_url()));
                }

                $attempts++;
                $_SESSION['login_attempts'] = $attempts;
                if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
                    $lockUntil = $now + self::LOGIN_LOCK_SECONDS;
                    $_SESSION['login_lock_until'] = $lockUntil;
                    $error = sprintf(t('login_locked'), self::LOGIN_LOCK_SECONDS);
                } else {
                    $remaining = self::MAX_LOGIN_ATTEMPTS - $attempts;
                    $error = t('login_invalid') . ' ' . sprintf(t('login_attempts_left'), $remaining);
                }
            }
        } elseif ($lockUntil > $now) {
            $error = sprintf(t('login_locked'), max(1, $lockUntil - $now));
        }

        View::render('auth.login', [
            'error' => $error,
            'lockUntil' => $lockUntil,
            'lockRemaining' => max(0, $lockUntil - $now),
        ], null);
    }

    public function logout(): void
    {
        $pdo = Database::pdo();
        $logId = (int) ($_SESSION['login_log_id'] ?? 0);
        $uid = (int) (current_user()['id'] ?? 0);
        if ($logId > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE user_sessions SET logout_at = NOW() WHERE id = ? AND logout_at IS NULL");
                $stmt->execute([$logId]);
            } catch (\Throwable $e) {
            }
        } elseif ($uid > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE user_sessions SET logout_at = NOW() WHERE user_id = ? AND logout_at IS NULL ORDER BY login_at DESC LIMIT 1");
                $stmt->execute([$uid]);
            } catch (\Throwable $e) {
            }
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        if (($_GET['beacon'] ?? '') === '1') {
            http_response_code(204);
            exit;
        }
        $this->redirect(url('login'));
    }
}