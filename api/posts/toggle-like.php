<?php

require_once '../../includes/auth.php';
requireLogin('../../pages/login.php');

require_once '../../config/database.php';
require_once '../../includes/csrf.php';

header('Content-Type: application/json');

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
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request. Please try again.'
  ]);
  exit;
}

$postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
$userId = $_SESSION['user_id'];

if (!$postId) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'message' => 'Invalid post.'
  ]);
  exit;
}

// Check whether the user has already liked this post
$stmt = $pdo->prepare(
  "SELECT id
     FROM post_likes
     WHERE post_id = ?
     AND user_id = ?"
);

$stmt->execute([
  $postId,
  $userId
]);

$like = $stmt->fetch(PDO::FETCH_ASSOC);

if ($like) {

  // Unlike
  $stmt = $pdo->prepare(
    "DELETE FROM post_likes
         WHERE post_id = ?
         AND user_id = ?"
  );

  $stmt->execute([
    $postId,
    $userId
  ]);
} else {

  // Like
  $stmt = $pdo->prepare(
    "INSERT INTO post_likes (post_id, user_id)
         VALUES (?, ?)"
  );

  $stmt->execute([
    $postId,
    $userId
  ]);
}

$countStmt = $pdo->prepare(
  "SELECT COUNT(*)
   FROM post_likes
   WHERE post_id = ?"
);

$countStmt->execute([$postId]);

echo json_encode([
  'success' => true,
  'liked' => !$like,
  'like_count' => (int) $countStmt->fetchColumn()
]);
exit;
