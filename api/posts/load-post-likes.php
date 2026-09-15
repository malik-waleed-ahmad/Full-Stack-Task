<?php

require_once '../../includes/auth.php';
requireLogin('../../pages/login.php');

require_once '../../config/database.php';

$postId = filter_input(
  INPUT_GET,
  'post_id',
  FILTER_VALIDATE_INT
);

$lastLikeId = filter_input(
  INPUT_GET,
  'last_like_id',
  FILTER_VALIDATE_INT
);

$limit = 10;

if (!$postId) {
  header('Content-Type: application/json');

  echo json_encode([
    'success' => false,
    'message' => 'Invalid post.'
  ]);

  exit;
}

$sql = "
    SELECT
        users.id,
        users.username,
        users.profile_picture,
        post_likes.id AS like_id

    FROM post_likes

    INNER JOIN users
        ON post_likes.user_id = users.id

    WHERE post_likes.post_id = ?
";

$params = [
  $postId
];

if ($lastLikeId) {
  $sql .= " AND post_likes.id < ?";
  $params[] = $lastLikeId;
}

$sql .= "
    ORDER BY post_likes.id DESC
    LIMIT $limit
";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();

foreach ($users as $user) {
  require '../../includes/liked-user-item.php';
}

$html = ob_get_clean();

header('Content-Type: application/json');

echo json_encode([
  'success' => true,
  'html' => $html,
  'has_more' => count($users) === $limit
]);

exit;
