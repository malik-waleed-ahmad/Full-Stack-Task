<?php

require_once '../../includes/auth.php';
requireLogin('../../pages/login.php');

require_once '../../config/database.php';
require_once '../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../../index.php');
  exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
  http_response_code(403);
  exit('Invalid CSRF token.');
}

$postId = filter_input(
  INPUT_POST,
  'post_id',
  FILTER_VALIDATE_INT
);

$userId = $_SESSION['user_id'];

if (!$postId) {
  header('Location: ../../index.php');
  exit;
}

// Only the owner can hide the post
$stmt = $pdo->prepare(
  "UPDATE posts
     SET is_hidden = TRUE
     WHERE id = ?
     AND user_id = ?"
);

$stmt->execute([
  $postId,
  $userId
]);

$_SESSION['toast_message'] = 'Post hidden successfully';
$_SESSION['toast_type'] = 'success';

header('Location: ../../index.php');
exit;
