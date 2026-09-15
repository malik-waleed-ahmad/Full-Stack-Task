<?php

require_once __DIR__ . '/post-data.php';


function getUserById(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            id,
            username,
            bio,
            profile_picture,
            created_at
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}


function getUserPostCount(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM posts
        WHERE user_id = ?
        AND is_hidden = 0
    ");

    $stmt->execute([$userId]);

    return (int) $stmt->fetchColumn();
}


function getUserLikesReceived(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM post_likes pl

        INNER JOIN posts p
            ON p.id = pl.post_id

        WHERE p.user_id = ?
        AND p.is_hidden = 0
    ");

    $stmt->execute([$userId]);

    return (int) $stmt->fetchColumn();
}


function getUserCommentCount(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM comments
        WHERE user_id = ?
    ");

    $stmt->execute([$userId]);

    return (int) $stmt->fetchColumn();
}


function getUserStats(PDO $pdo, int $userId): array
{
    return [
        'posts' => getUserPostCount($pdo, $userId),
        'likes_received' => getUserLikesReceived($pdo, $userId),
        'comments' => getUserCommentCount($pdo, $userId),
    ];
}


function getUserVisiblePosts(
    PDO $pdo,
    int $userId,
    int $currentUserId
): array {

    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.user_id,
            p.content,
            p.created_at,

            u.username,
            u.profile_picture,

            (
                SELECT COUNT(*)
                FROM post_likes
                WHERE post_id = p.id
            ) AS like_count,

            (
                SELECT COUNT(*)
                FROM comments
                WHERE post_id = p.id
                AND parent_id IS NULL
            ) AS comment_count

        FROM posts p

        INNER JOIN users u
            ON u.id = p.user_id

        WHERE p.user_id = ?
        AND p.is_hidden = 0

        ORDER BY p.id DESC
    ");

    $stmt->execute([$userId]);

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as &$post) {

        $post = preparePostData(
            $pdo,
            $post,
            $currentUserId
        );
    }

    unset($post);

    return $posts;
}