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

// Verify post ownership
$stmt = $pdo->prepare(
  "SELECT id
   FROM posts
   WHERE id = ?
   AND user_id = ?"
);

$stmt->execute([
  $postId,
  $userId
]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
  header('Location: ../../index.php');
  exit;
}

// Get all images belonging to this post
$stmt = $pdo->prepare(
  "SELECT image_url
   FROM post_images
   WHERE post_id = ?"
);

$stmt->execute([
  $postId
]);

$images = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Delete physical image files
foreach ($images as $imageUrl) {

  $imagePath = '../../' . $imageUrl;

  if (
    file_exists($imagePath) &&
    is_file($imagePath)
  ) {
    unlink($imagePath);
  }
}

// Delete the post
$stmt = $pdo->prepare(
  "DELETE FROM posts
   WHERE id = ?
   AND user_id = ?"
);

$stmt->execute([
  $postId,
  $userId
]);

$_SESSION['toast_message'] = 'Post deleted successfully';
$_SESSION['toast_type'] = 'success';

header('Location: ../../index.php?post_deleted=1');
exit;
