<?php

function loadPostImages(PDO $pdo, int $postId): array
{
  $stmt = $pdo->prepare(
    "SELECT image_url
     FROM post_images
     WHERE post_id = ?
     ORDER BY id ASC"
  );

  $stmt->execute([$postId]);

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function loadPostComments(PDO $pdo, int $postId, int $limit = 5): array
{
  $stmt = $pdo->prepare(
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
     WHERE comments.post_id = ?
     AND comments.parent_id IS NULL
     ORDER BY comments.id DESC
     LIMIT ?"
  );

  $stmt->bindValue(1, $postId, PDO::PARAM_INT);
  $stmt->bindValue(2, $limit, PDO::PARAM_INT);

  $stmt->execute();

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function loadCommentReplies(PDO $pdo, int $parentId): array
{
  $stmt = $pdo->prepare(
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
     ORDER BY comments.id ASC"
  );

  $stmt->execute([$parentId]);

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function loadTopComment(PDO $pdo, int $postId): ?array
{
  $stmt = $pdo->prepare(
    "SELECT
        comments.id,
        comments.user_id,
        comments.content,
        comments.created_at,
        users.username,
        users.profile_picture,
        COUNT(comment_likes.id) AS like_count
     FROM comments
     INNER JOIN users
        ON comments.user_id = users.id
     LEFT JOIN comment_likes
        ON comment_likes.comment_id = comments.id
     WHERE comments.post_id = ?
     AND comments.parent_id IS NULL
     GROUP BY
        comments.id,
        comments.user_id,
        comments.content,
        comments.created_at,
        users.username,
        users.profile_picture
     ORDER BY
        like_count DESC,
        comments.created_at ASC
     LIMIT 1"
  );

  $stmt->execute([$postId]);

  $comment = $stmt->fetch(PDO::FETCH_ASSOC);

  return $comment ?: null;
}

function hasUserLikedPost(
  PDO $pdo,
  int $postId,
  int $userId
): bool {
  $stmt = $pdo->prepare(
    "SELECT id
     FROM post_likes
     WHERE post_id = ?
     AND user_id = ?
     LIMIT 1"
  );

  $stmt->execute([
    $postId,
    $userId
  ]);

  return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

function preparePostData(
    PDO $pdo,
    array $post,
    int $userId
): array
{
    $postId = (int)$post['id'];

    $post['images'] = loadPostImages(
        $pdo,
        $postId
    );

    $post['comments'] = loadPostComments(
        $pdo,
        $postId
    );

    $post['replies'] = [];

    foreach ($post['comments'] as $comment) {
        $post['replies'][$comment['id']] = loadCommentReplies(
            $pdo,
            (int)$comment['id']
        );
    }

    $post['top_comment'] = loadTopComment(
        $pdo,
        $postId
    );

    $post['user_liked'] = hasUserLikedPost(
        $pdo,
        $postId,
        $userId
    );

    return $post;
}