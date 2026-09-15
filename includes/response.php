<?php

function jsonResponse(bool $success, string $message, array $data = []): void
{
  header('Content-Type: application/json');

  echo json_encode([
    'success' => $success,
    'message' => $message,
    'data' => $data
  ]);

  exit;
}
