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

$commentId = filter_input(
  INPUT_POST,
  'comment_id',
  FILTER_VALIDATE_INT
);

$userId = $_SESSION['user_id'];

if (!$commentId) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'message' => 'Invalid comment.'
  ]);
  exit;
}

// Make sure the comment actually exists
$stmt = $pdo->prepare(
  "SELECT id
     FROM comments
     WHERE id = ?"
);

$stmt->execute([
  $commentId
]);

$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
  http_response_code(404);
  echo json_encode([
    'success' => false,
    'message' => 'Comment not found.'
  ]);
  exit;
}

// Check whether the user already liked this comment
$stmt = $pdo->prepare(
  "SELECT id
     FROM comment_likes
     WHERE comment_id = ?
     AND user_id = ?"
);

$stmt->execute([
  $commentId,
  $userId
]);

$existingLike = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingLike) {

  // Unlike
  $stmt = $pdo->prepare(
    "DELETE FROM comment_likes
         WHERE comment_id = ?
         AND user_id = ?"
  );

  $stmt->execute([
    $commentId,
    $userId
  ]);
} else {

  // Like
  $stmt = $pdo->prepare(
    "INSERT INTO comment_likes
            (comment_id, user_id)
         VALUES (?, ?)"
  );

  $stmt->execute([
    $commentId,
    $userId
  ]);
}

$countStmt = $pdo->prepare(
  "SELECT COUNT(*)
   FROM comment_likes
   WHERE comment_id = ?"
);

$countStmt->execute([$commentId]);

echo json_encode([
  'success' => true,
  'liked' => !$existingLike,
  'like_count' => (int) $countStmt->fetchColumn()
]);
exit;
