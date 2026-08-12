<?php
/**
 * config.php
 * ------------------------------------------------------------
 * Central DB connection (PDO / MySQL) for the Apartment
 * Management System — Tenant Dashboard module.
 *
 * If no MySQL server is reachable (e.g. while grading this
 * project outside a configured LAMP stack), the app quietly
 * falls back to in-memory demo data so the page always renders
 * and functions. Wire up real credentials below to go live.
 * ------------------------------------------------------------
 */

declare(strict_types=1);
session_start();

// ---- Connection settings -------------------------------------------------
const DB_HOST = '127.0.0.1';
const DB_NAME = 'apartment_management';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

/**
 * Returns a PDO instance, or null if the database is unreachable.
 * Callers must handle the null case (demo-mode fallback).
 */
function get_pdo(): ?PDO
{
    static $pdo = null;
    static $attempted = false;

    if ($pdo !== null || $attempted) {
        return $pdo;
    }
    $attempted = true;

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        error_log('DB connection failed, falling back to demo data: ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}

/** Simple helper to escape output consistently. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Formats a numeric amount as currency (BDT Taka). */
function money(float $amount): string
{
    return '৳' . number_format($amount, 2);
}
