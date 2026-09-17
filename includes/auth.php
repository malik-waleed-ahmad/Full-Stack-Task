<?php

require_once __DIR__ . '/db-session.php';

function isLoggedIn(): bool
{
  return isset($_SESSION['user_id']);
}

function preventCache(): void
{
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

function requireLogin(string $redirectPath): void
{
  preventCache();

  if (!isLoggedIn()) {
    header("Location: $redirectPath");
    exit;
  }
}
