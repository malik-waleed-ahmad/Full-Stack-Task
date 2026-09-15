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

$content = trim($_POST['content'] ?? '');

$userId = $_SESSION['user_id'];

// Validate post ID
if (!$postId) {
  header('Location: ../../index.php');
  exit;
}

// Validate content
if ($content === '') {
  header(
    'Location: ../../pages/edit-post.php?id=' . $postId . '&error=empty'
  );
  exit;
}

if (strlen($content) > 5000) {
  header(
    'Location: ../../pages/edit-post.php?id=' . $postId . '&error=long'
  );
  exit;
}

// Verify that the post belongs to the logged-in user
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

// Update the post
$stmt = $pdo->prepare(
  "UPDATE posts
   SET content = ?
   WHERE id = ?
   AND user_id = ?"
);

$stmt->execute([
  $content,
  $postId,
  $userId
]);

$deleteImages = $_POST['delete_images'] ?? [];

if (!empty($deleteImages)) {

  foreach ($deleteImages as $imageId) {

    $imageId = filter_var(
      $imageId,
      FILTER_VALIDATE_INT
    );

    if (!$imageId) {
      continue;
    }

    // Get image only if it belongs to this user's post
    $stmt = $pdo->prepare(
      "SELECT image_url
       FROM post_images
       INNER JOIN posts
          ON post_images.post_id = posts.id
       WHERE post_images.id = ?
       AND post_images.post_id = ?
       AND posts.user_id = ?"
    );

    $stmt->execute([
      $imageId,
      $postId,
      $userId
    ]);

    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image) {
      continue;
    }

    // Delete physical image file
    $imagePath = '../../' . $image['image_url'];

    if (
      file_exists($imagePath) &&
      is_file($imagePath)
    ) {
      unlink($imagePath);
    }

    // Delete database record
    $stmt = $pdo->prepare(
      "DELETE FROM post_images
       WHERE id = ?
       AND post_id = ?"
    );

    $stmt->execute([
      $imageId,
      $postId
    ]);
  }
}

if (
  isset($_FILES['new_images']) &&
  !empty($_FILES['new_images']['name'][0])
) {

  $uploadDirectory = '../../uploads/posts/';

  if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
  }

  foreach ($_FILES['new_images']['tmp_name'] as $index => $tmpName) {

    if ($_FILES['new_images']['error'][$index] !== UPLOAD_ERR_OK) {
      continue;
    }

    $originalName = $_FILES['new_images']['name'][$index];

    $fileSize = $_FILES['new_images']['size'][$index];

    // Maximum 5 MB
    if ($fileSize > 5 * 1024 * 1024) {
      continue;
    }

    // Validate actual MIME type
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

    $mimeType = finfo_file(
      $fileInfo,
      $tmpName
    );

    finfo_close($fileInfo);

    $allowedTypes = [
      'image/jpeg' => 'jpg',
      'image/png'  => 'png',
      'image/webp' => 'webp'
    ];

    if (!isset($allowedTypes[$mimeType])) {
      continue;
    }

    $extension = $allowedTypes[$mimeType];

    $filename = bin2hex(random_bytes(16))
      . '.'
      . $extension;

    $destination = $uploadDirectory . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
      continue;
    }

    $imageUrl = 'uploads/posts/' . $filename;

    $stmt = $pdo->prepare(
      "INSERT INTO post_images (post_id, image_url)
       VALUES (?, ?)"
    );

    $stmt->execute([
      $postId,
      $imageUrl
    ]);
  }
}

$_SESSION['toast_message'] = 'Post updated successfully';
$_SESSION['toast_type'] = 'success';

header('Location: ../../index.php?post_updated=1');
exit;
