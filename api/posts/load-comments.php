<?php

require_once '../../includes/auth.php';
requireLogin('../../pages/login.php');

require_once '../../config/database.php';
require_once '../../includes/csrf.php';

$postId = filter_input(
  INPUT_GET,
  'post_id',
  FILTER_VALIDATE_INT
);

$lastCommentId = filter_input(
  INPUT_GET,
  'last_comment_id',
  FILTER_VALIDATE_INT
);

$limit = 5;

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
        comments.id,
        comments.user_id,
        comments.parent_id,
        comments.content,
        comments.created_at,
        users.username,
        users.profile_picture,

        (
            SELECT COUNT(*)
            FROM comment_likes
            WHERE comment_likes.comment_id = comments.id
        ) AS like_count

    FROM comments

    INNER JOIN users
        ON comments.user_id = users.id

    WHERE comments.post_id = ?
    AND comments.parent_id IS NULL
";

$params = [
  $postId
];

if ($lastCommentId) {

  $sql .= " AND comments.id < ?";

  $params[] = $lastCommentId;
}

$sql .= "
    ORDER BY comments.id DESC
    LIMIT $limit
";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$replies = [];

foreach ($comments as $comment) {

  $replyStmt = $pdo->prepare(
    "SELECT
            comments.id,
            comments.user_id,
            comments.parent_id,
            comments.content,
            comments.created_at,
            users.username,
            users.profile_picture,

            (
                SELECT COUNT(*)
                FROM comment_likes
                WHERE comment_likes.comment_id = comments.id
            ) AS like_count

         FROM comments

         INNER JOIN users
            ON comments.user_id = users.id

         WHERE comments.parent_id = ?

         ORDER BY comments.created_at ASC"
  );

  $replyStmt->execute([
    $comment['id']
  ]);

  $replies[$comment['id']] =
    $replyStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Generate HTML
ob_start();

foreach ($comments as $comment) {
  require '../../includes/comment-item.php';
}

$html = ob_get_clean();

header('Content-Type: application/json');

echo json_encode([
  'success' => true,
  'html' => $html,
  'has_more' => count($comments) === $limit
]);

exit;
