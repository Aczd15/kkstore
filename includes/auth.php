<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, name, email, avatar_path, role FROM users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function isAuthenticated(): bool
{
    return currentUser() !== null;
}

function hasRole(string $role): bool
{
    $user = currentUser();
    return $user !== null && $user['role'] === $role;
}

function requireAuth(): void
{
    if (!isAuthenticated()) {
        flash('error', 'Требуется авторизация.');
        redirect('/index.php?page=login');
    }
}

function requireRole(string $role): void
{
    requireAuth();

    if (!hasRole($role)) {
        flash('error', 'Недостаточно прав для доступа.');
        redirect('/index.php?page=dashboard');
    }
}
