<?php

require_once '../../includes/auth.php';
requireLogin('../../pages/login.php');

require_once '../../config/database.php';
require_once '../../includes/csrf.php';
require_once '../../includes/post-data.php';

$limit = 5;

$lastId = filter_input(
  INPUT_GET,
  'last_id',
  FILTER_VALIDATE_INT
);

$sql = "
    SELECT
        posts.id,
        posts.user_id,
        posts.content,
        posts.created_at,
        users.username,
        users.profile_picture,

        (
            SELECT COUNT(*)
            FROM post_likes
            WHERE post_likes.post_id = posts.id
        ) AS like_count,

        (
            SELECT COUNT(*)
            FROM comments
            WHERE comments.post_id = posts.id
            AND comments.parent_id IS NULL
        ) AS comment_count

    FROM posts

    INNER JOIN users
        ON posts.user_id = users.id

    WHERE posts.is_hidden = FALSE
";

if ($lastId) {
  $sql .= " AND posts.id < :last_id";
}

$sql .= "
    ORDER BY posts.id DESC
    LIMIT :limit
";

$stmt = $pdo->prepare($sql);

if ($lastId) {
  $stmt->bindValue(
    ':last_id',
    $lastId,
    PDO::PARAM_INT
  );
}

$stmt->bindValue(
  ':limit',
  $limit,
  PDO::PARAM_INT
);

$stmt->execute();

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as &$post) {
    $post = preparePostData(
        $pdo,
        $post,
        (int)$_SESSION['user_id']
    );
}

unset($post);

$hasMore = count($posts) === $limit;

$basePath = '';

ob_start();

foreach ($posts as $post) {
  require '../../includes/post-card.php';
}

$html = ob_get_clean();

header('Content-Type: application/json');

echo json_encode([
  'success' => true,
  'html' => $html,
  'has_more' => $hasMore
]);

exit;
