<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class UserController extends Controller
{
    private const POSITIONS = ['seller', 'manager', 'director', 'storekeeper', 'cashier'];

    public function index(): void
    {
        require_role('admin');
        $pdo = Database::pdo();

        $message = '';
        $messageType = 'success';
        $editId = 0;
        $editEmpId = 0;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create_employee' || $action === 'update_employee') {
                $id = (int) ($_POST['id'] ?? 0);
                $fullName = trim((string) ($_POST['full_name'] ?? ''));
                $birthDate = trim((string) ($_POST['birth_date'] ?? ''));
                $hireDate = trim((string) ($_POST['hire_date'] ?? ''));
                $termDate = trim((string) ($_POST['termination_date'] ?? ''));
                $termPresent = isset($_POST['termination_present']);
                $position = trim((string) ($_POST['position'] ?? 'seller'));
                $userIdRaw = trim((string) ($_POST['user_id'] ?? ''));

                $birthDate = ($birthDate === '' ? null : $birthDate);
                $hireDate = ($hireDate === '' ? null : $hireDate);
                $termDate = ($termPresent || $termDate === '') ? null : $termDate;
                if (!in_array($position, self::POSITIONS, true)) $position = 'seller';
                $userId = ($userIdRaw === '' ? null : (int) $userIdRaw);
                if ($userId !== null && $userId <= 0) $userId = null;

                if ($fullName === '') {
                    $message = t('employee_save_error');
                    $messageType = 'error';
                    $editEmpId = ($action === 'update_employee') ? $id : 0;
                } else {
                    try {
                        if ($userId !== null) {
                            $chk = $pdo->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
                            $chk->execute([$userId]);
                            if (!$chk->fetch()) $userId = null;
                        }
                        $birthYear = null;
                        if ($birthDate !== null && preg_match('/^(\d{4})-/', $birthDate, $m)) {
                            $birthYear = (int) $m[1];
                        }
                        if ($action === 'create_employee') {
                            $stmt = $pdo->prepare("INSERT INTO employees (full_name, birth_date, birth_year, hire_date, termination_date, position, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$fullName, $birthDate, $birthYear, $hireDate, $termDate, $position, $userId]);
                            $message = t('employee_added');
                        } else {
                            $stmt = $pdo->prepare("UPDATE employees SET full_name = ?, birth_date = ?, birth_year = ?, hire_date = ?, termination_date = ?, position = ?, user_id = ? WHERE id = ?");
                            $stmt->execute([$fullName, $birthDate, $birthYear, $hireDate, $termDate, $position, $userId, $id]);
                            $message = t('employee_saved');
                        }
                    } catch (\Throwable $e) {
                        $message = t('employee_save_error') . ' ' . $e->getMessage();
                        $messageType = 'error';
                        $editEmpId = ($action === 'update_employee') ? $id : 0;
                    }
                }
            } elseif ($action === 'delete_employee') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    try {
                        $pdo->prepare("DELETE FROM employees WHERE id = ?")->execute([$id]);
                        $message = t('employee_deleted');
                    } catch (\Throwable $e) {
                        $message = t('employee_save_error');
                        $messageType = 'error';
                    }
                }
            } elseif ($action === 'create') {
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
                $role = $_POST['role'] ?? 'shop';
                if (!in_array($role, ['admin', 'shop'], true)) $role = 'shop';
                if ($username === '' || $password === '' || $passwordConfirm === '') {
                    $message = t('specify_login_passwords'); $messageType = 'error';
                } elseif ($password !== $passwordConfirm) {
                    $message = t('passwords_mismatch'); $messageType = 'error';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
                        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
                        $message = t('user_created');
                    } catch (\Throwable $e) {
                        $message = t('create_failed'); $messageType = 'error';
                    }
                }
            } elseif ($action === 'update') {
                $id = (int) ($_POST['id'] ?? 0);
                $editId = $id;
                $username = trim((string) ($_POST['username'] ?? ''));
                $role = $_POST['role'] ?? 'shop';
                $password = (string) ($_POST['password'] ?? '');
                if (!in_array($role, ['admin', 'shop'], true)) $role = 'shop';
                $self = (int) (current_user()['id'] ?? 0);
                if ($username === '') {
                    $message = t('login_empty'); $messageType = 'error';
                } elseif ($id === $self && $role !== 'admin') {
                    $message = t('cannot_remove_own_admin'); $messageType = 'error';
                } else {
                    try {
                        if ($password !== '') {
                            $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, password_hash = ? WHERE id = ?");
                            $stmt->execute([$username, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
                        } else {
                            $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                            $stmt->execute([$username, $role, $id]);
                        }
                        if ($id === $self) {
                            $_SESSION['user']['username'] = $username;
                            $_SESSION['user']['role'] = $role;
                        }
                        $message = t('user_updated'); $messageType = 'success'; $editId = 0;
                    } catch (\Throwable $e) {
                        $message = t('update_failed'); $messageType = 'error';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                $self = (int) (current_user()['id'] ?? 0);
                if ($id === $self) { $message = t('cannot_delete_self'); $messageType = 'error'; }
                else {
                    try { $pdo->prepare("UPDATE employees SET user_id = NULL WHERE user_id = ?")->execute([$id]); } catch (\Throwable $e) {}
                    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
                    $message = t('user_deleted');
                }
            }
        }

        $users = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id")->fetchAll();
        $selfId = (int) (current_user()['id'] ?? 0);
        $editReopen = ($messageType === 'error' && (($_POST['action'] ?? '') === 'update') && $editId > 0) ? $editId : 0;
        $editReopenUsername = $editReopen ? (string) ($_POST['username'] ?? '') : '';
        $editReopenRole = $editReopen ? (string) ($_POST['role'] ?? 'shop') : 'shop';
        if (!in_array($editReopenRole, ['admin', 'shop'], true)) $editReopenRole = 'shop';
        $createReopen = ($messageType === 'error' && (($_POST['action'] ?? '') === 'create'));

        $userActivity = [];
        foreach ($users as $u) {
            $uid = (int) $u['id'];
            $events = [];
            try {
                $ss = $pdo->prepare("SELECT id, login_at, logout_at, ip FROM user_sessions WHERE user_id = ? ORDER BY login_at DESC, id DESC LIMIT 50");
                $ss->execute([$uid]);
                foreach ($ss->fetchAll() as $srow) {
                    $loginAt = (string) ($srow['login_at'] ?? '');
                    $logoutAt = (string) ($srow['logout_at'] ?? '');
                    $ip = (string) ($srow['ip'] ?? '');
                    $events[] = ['at' => $loginAt, 'type' => 'login', 'title' => t('login_event'), 'detail' => $ip !== '' ? ('IP: ' . $ip) : ''];
                    if ($logoutAt !== '') {
                        $events[] = ['at' => $logoutAt, 'type' => 'logout', 'title' => t('logout_event'), 'detail' => ''];
                    }
                }
            } catch (\Throwable $e) {}
            try {
                $ts = $pdo->prepare("
                    SELECT t.type, t.quantity, t.amount, t.transaction_date, t.created_at, p.name AS product, p.unit
                    FROM transactions t
                    JOIN products p ON p.id = t.product_id
                    WHERE t.user_id = ?
                    ORDER BY t.transaction_date DESC, t.created_at DESC, t.id DESC
                    LIMIT 80
                ");
                $ts->execute([$uid]);
                foreach ($ts->fetchAll() as $tr) {
                    $unit = normalize_unit($tr['unit'] ?? 'шт');
                    $qty = fmt_qty($tr['quantity'] ?? 0) . ' ' . unit_label($unit);
                    $sum = fmt_money($tr['amount'] ?? 0) . ' ' . t('sum_short');
                    $prod = db_text((string) ($tr['product'] ?? ''));
                    $at = (string) ($tr['created_at'] ?? '');
                    if ($at === '' || $at === '0000-00-00 00:00:00') $at = (string) ($tr['transaction_date'] ?? '') . ' 00:00:00';
                    $isIncome = ($tr['type'] === 'income');
                    $events[] = ['at' => $at, 'type' => $isIncome ? 'stock_in' : 'sale', 'title' => $isIncome ? t('stock_in_event') : t('sale_event'), 'detail' => $prod . ' — ' . $qty . ' (' . $sum . ')'];
                }
            } catch (\Throwable $e) {}
            usort($events, static fn($a,$b) => strcmp((string)($b['at']??''), (string)($a['at']??'')));
            if (count($events) > 120) $events = array_slice($events, 0, 120);
            $userActivity[$uid] = $events;
        }

        $employees = [];
        try {
            $employees = $pdo->query("
                SELECT e.*, u.username AS user_username, u.role AS user_role
                FROM employees e
                LEFT JOIN users u ON u.id = e.user_id
                ORDER BY e.id DESC
            ")->fetchAll();
        } catch (\Throwable $e) {}

        $userEmployeeMap = [];
        foreach ($employees as $emp) {
            $empUserId = (int) ($emp['user_id'] ?? 0);
            if ($empUserId > 0 && !isset($userEmployeeMap[$empUserId])) {
                $userEmployeeMap[$empUserId] = $emp;
            }
        }

        $editEmpReopen = ($messageType === 'error' && in_array(($_POST['action'] ?? ''), ['create_employee','update_employee'], true));
        $empForm = [
            'id' => $editEmpReopen ? (int) ($_POST['id'] ?? 0) : 0,
            'action' => $editEmpReopen ? (string) ($_POST['action'] ?? 'create_employee') : 'create_employee',
            'full_name' => $editEmpReopen ? (string) ($_POST['full_name'] ?? '') : '',
            'birth_date' => $editEmpReopen ? (string) ($_POST['birth_date'] ?? '') : '',
            'hire_date' => $editEmpReopen ? (string) ($_POST['hire_date'] ?? '') : '',
            'termination_date' => $editEmpReopen ? (string) ($_POST['termination_date'] ?? '') : '',
            'termination_present' => $editEmpReopen ? isset($_POST['termination_present']) : true,
            'position' => $editEmpReopen ? (string) ($_POST['position'] ?? 'seller') : 'seller',
            'user_id' => $editEmpReopen ? (string) ($_POST['user_id'] ?? '') : '',
        ];

        $this->view('users.index', [
            'title' => t('users') . ' | ' . t('app_name'),
            'active' => 'users',
            'message' => $message,
            'messageType' => $messageType,
            'users' => $users,
            'selfId' => $selfId,
            'userActivity' => $userActivity,
            'userEmployeeMap' => $userEmployeeMap,
            'createReopen' => $createReopen,
            'editReopen' => $editReopen,
            'editReopenUsername' => $editReopenUsername,
            'editReopenRole' => $editReopenRole,
            'employees' => $employees,
            'positions' => self::POSITIONS,
            'editEmpReopen' => $editEmpReopen,
            'empForm' => $empForm,
        ]);
    }
}
