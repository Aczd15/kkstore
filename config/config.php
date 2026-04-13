<?php

declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'kkstore';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

const APP_NAME = 'KKStore';
const BASE_URL = '/kkstore';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
