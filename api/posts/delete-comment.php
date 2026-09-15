<?php

require_once '../../includes/auth.php';
requireLogin('../../pages/login.php');

require_once '../../config/database.php';
require_once '../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../../index.php');
  exit;
}

if (
  !verify_csrf_token(
    $_POST['csrf_token'] ?? null
  )
) {
  http_response_code(403);
  exit('Invalid CSRF token.');
}

$commentId = filter_input(
  INPUT_POST,
  'comment_id',
  FILTER_VALIDATE_INT
);

$userId = $_SESSION['user_id'];

if (!$commentId) {
  header('Location: ../../index.php');
  exit;
}

// Delete only if the comment belongs to the logged-in user
$stmt = $pdo->prepare(
  "DELETE FROM comments
     WHERE id = ?
     AND user_id = ?"
);

$stmt->execute([
  $commentId,
  $userId
]);

header('Location: ../../index.php?comment_deleted=1');
exit;
