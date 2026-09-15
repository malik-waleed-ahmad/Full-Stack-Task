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
  $_SESSION['toast_message'] = 'Invalid request. Please try again.';
  $_SESSION['toast_type'] = 'error';

  http_response_code(403);
  header('Location: ../../index.php');
  exit;
}

$postId = filter_input(
  INPUT_POST,
  'post_id',
  FILTER_VALIDATE_INT
);

$parentId = filter_input(
  INPUT_POST,
  'parent_id',
  FILTER_VALIDATE_INT
);

$content = trim($_POST['content'] ?? '');

$userId = $_SESSION['user_id'];

// Validate post ID
if (!$postId) {
  $_SESSION['toast_message'] = 'Invalid post.';
  $_SESSION['toast_type'] = 'error';

  header('Location: ../../index.php');
  exit;
}

// Validate comment
if ($content === '') {
  $_SESSION['toast_message'] = 'Comment cannot be empty.';
  $_SESSION['toast_type'] = 'error';

  header('Location: ../../index.php');
  exit;
}

if (strlen($content) > 1000) {
  $_SESSION['toast_message'] = 'Comment is too long.';
  $_SESSION['toast_type'] = 'error';

  header('Location: ../../index.php');
  exit;
}

// Make sure the post exists
$stmt = $pdo->prepare(
  "SELECT id
     FROM posts
     WHERE id = ?
     AND is_hidden = FALSE"
);

$stmt->execute([
  $postId
]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
  $_SESSION['toast_message'] = 'Post not found or unavailable.';
  $_SESSION['toast_type'] = 'error';

  header('Location: ../../index.php');
  exit;
}

if ($parentId) {

  $parentStmt = $pdo->prepare(
    "SELECT id
     FROM comments
     WHERE id = ?
     AND post_id = ?
     LIMIT 1"
  );

  $parentStmt->execute([
    $parentId,
    $postId
  ]);

  $parentComment = $parentStmt->fetch(PDO::FETCH_ASSOC);

  if (!$parentComment) {
    $_SESSION['toast_message'] = 'Invalid parent comment.';
    $_SESSION['toast_type'] = 'error';

    header('Location: ../../index.php');
    exit;
  }
}

// Insert comment
$stmt = $pdo->prepare(
  "INSERT INTO comments (post_id, user_id, parent_id, content)
     VALUES (?, ?, ?, ?)"
);

$stmt->execute([
  $postId,
  $userId,
  $parentId ?: null,
  $content
]);

$_SESSION['toast_message'] = 'Comment added successfully';
$_SESSION['toast_type'] = 'success';

header('Location: ../../index.php');
exit;
