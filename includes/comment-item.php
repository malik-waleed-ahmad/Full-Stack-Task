<?php

require_once __DIR__ . '/media.php';

$replies = $replies ?? [];
$basePath = $basePath ?? '';

if (!isset($comment)) {
  return;
}

$commentAvatarUrl = mediaUrl(
  $comment['profile_picture'] ?? null,
  $basePath
);
?>

<div
  class="comment-item w-full"
  data-comment-id="<?= (int) $comment['id'] ?>">

  <!-- Main Comment -->
  <div class="flex w-full min-w-0 items-start gap-3">

    <!-- Avatar -->
    <?php if ($commentAvatarUrl): ?>

      <img
        src="<?= htmlspecialchars($commentAvatarUrl) ?>"
        alt="<?= htmlspecialchars($comment['username']) ?>"
        class="h-9 w-9 shrink-0 rounded-full object-cover">

    <?php else: ?>

      <div
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
        <?= htmlspecialchars(
          strtoupper(substr($comment['username'], 0, 1))
        ) ?>
      </div>

    <?php endif; ?>


    <div class="min-w-0 flex-1">

      <!-- Comment Bubble -->
      <div class="rounded-2xl bg-gray-100 px-4 py-3">

        <a
          href="<?= $basePath ?>pages/user-profile.php?id=<?= (int) $comment['user_id'] ?>"
          class="text-sm font-semibold text-gray-900 hover:text-blue-600">
          <?= htmlspecialchars($comment['username']) ?>
        </a>

        <p class="mt-1 break-words text-sm leading-5 text-gray-700">
          <?= nl2br(htmlspecialchars($comment['content'])) ?>
        </p>

      </div>


      <?php

      $commentLikeStmt = $pdo->prepare(
        "SELECT id
       FROM comment_likes
       WHERE comment_id = ?
       AND user_id = ?"
      );

      $commentLikeStmt->execute([
        $comment['id'],
        $_SESSION['user_id']
      ]);

      $commentLiked = $commentLikeStmt->fetch(PDO::FETCH_ASSOC);

      ?>


      <!-- Comment Actions -->
      <div class="mt-1 flex items-center gap-4 px-2">

        <form
          method="POST"
          action="<?= $basePath ?>api/posts/toggle-comment-like.php"
          class="comment-like-form">

          <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrf_token()) ?>">

          <input
            type="hidden"
            name="comment_id"
            value="<?= (int) $comment['id'] ?>">

          <button
            type="submit"
            data-comment-like-button
            class="text-xs font-medium transition
          <?= $commentLiked
            ? 'text-red-600'
            : 'text-gray-500 hover:text-red-600'
          ?>">
            <span class="comment-like-label">
              <?= $commentLiked ? '❤️ Liked' : '♡ Like' ?>
            </span>
          </button>

        </form>


        <button
          type="button"
          class="text-xs font-medium text-gray-500 transition hover:text-blue-600"
          onclick="document.getElementById('reply-form-<?= (int) $comment['id'] ?>').classList.toggle('hidden')">
          Reply
        </button>


        <span class="comment-like-count text-xs text-gray-400">
          <?= (int) $comment['like_count'] ?>
          <?= (int) $comment['like_count'] === 1 ? 'like' : 'likes' ?>
        </span>


        <?php if ((int) $comment['user_id'] === (int) $_SESSION['user_id']): ?>

          <form
            method="POST"
            action="<?= $basePath ?>api/posts/delete-comment.php"
            class="ml-auto"
            onsubmit="return confirm('Delete this comment?');">

            <input
              type="hidden"
              name="csrf_token"
              value="<?= htmlspecialchars(csrf_token()) ?>">

            <input
              type="hidden"
              name="comment_id"
              value="<?= (int) $comment['id'] ?>">

            <button
              type="submit"
              class="submit-loading-btn text-xs font-medium text-red-500 transition hover:text-red-700"
              data-loading-text="Deleting...">
              Delete
            </button>

          </form>

        <?php endif; ?>

      </div>


      <!-- Reply Form -->
      <form
        id="reply-form-<?= (int) $comment['id'] ?>"
        method="POST"
        action="<?= $basePath ?>api/posts/add-comment.php"
        class="mt-3 hidden">

        <input
          type="hidden"
          name="csrf_token"
          value="<?= htmlspecialchars(csrf_token()) ?>">

        <input
          type="hidden"
          name="post_id"
          value="<?= (int) $postId ?>">

        <input
          type="hidden"
          name="parent_id"
          value="<?= (int) $comment['id'] ?>">

        <div class="flex gap-2">

          <input
            type="text"
            name="content"
            maxlength="1000"
            required
            placeholder="Write a reply..."
            class="min-w-0 flex-1 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

          <button
            type="submit"
            class="submit-loading-btn shrink-0 rounded-full bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
            data-loading-text="Replying...">
            Reply
          </button>

        </div>

      </form>

    </div>

  </div>

  <!-- Replies -->
  <?php if (!empty($replies[$comment['id']])): ?>

    <div class="ml-10 mt-4 space-y-4 border-l border-gray-200 pl-4">

      <?php foreach (($replies[$comment['id']] ?? []) as $reply): ?>

        <?php
        $replyAvatarUrl = mediaUrl(
          $reply['profile_picture'] ?? null,
          $basePath
        );
        ?>

        <div class="flex w-full min-w-0 items-start gap-3">

          <!-- Reply Avatar -->
          <?php if ($replyAvatarUrl): ?>

            <img
              src="<?= htmlspecialchars($replyAvatarUrl) ?>"
              alt="<?= htmlspecialchars($reply['username']) ?>"
              class="h-8 w-8 shrink-0 rounded-full object-cover">

          <?php else: ?>

            <div
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600">
              <?= htmlspecialchars(
                strtoupper(substr($reply['username'], 0, 1))
              ) ?>
            </div>

          <?php endif; ?>


          <div class="min-w-0 flex-1">

            <!-- Reply Bubble -->
            <div class="rounded-2xl bg-gray-50 px-4 py-3">

              <a
                href="<?= $basePath ?>pages/user-profile.php?id=<?= (int) $reply['user_id'] ?>"
                class="text-sm font-semibold text-gray-900 hover:text-blue-600">
                <?= htmlspecialchars($reply['username']) ?>
              </a>

              <p class="mt-1 break-words text-sm leading-5 text-gray-700">
                <?= nl2br(htmlspecialchars($reply['content'])) ?>
              </p>

            </div>


            <?php

            $replyLikeStmt = $pdo->prepare(
              "SELECT id
             FROM comment_likes
             WHERE comment_id = ?
             AND user_id = ?"
            );

            $replyLikeStmt->execute([
              $reply['id'],
              $_SESSION['user_id']
            ]);

            $replyLiked = $replyLikeStmt->fetch(PDO::FETCH_ASSOC);

            ?>


            <!-- Reply Actions -->
            <div class="mt-1 flex items-center gap-4 px-2">

              <form
                method="POST"
                action="<?= $basePath ?>api/posts/toggle-comment-like.php"
                class="comment-like-form">

                <input
                  type="hidden"
                  name="csrf_token"
                  value="<?= htmlspecialchars(csrf_token()) ?>">

                <input
                  type="hidden"
                  name="comment_id"
                  value="<?= (int) $reply['id'] ?>">

                <button
                  type="submit"
                  data-comment-like-button
                  class="text-xs font-medium transition
                <?= $replyLiked
                  ? 'text-red-600'
                  : 'text-gray-500 hover:text-red-600'
                ?>">
                  <span class="comment-like-label">
                    <?= $replyLiked ? '❤️ Liked' : '♡ Like' ?>
                  </span>
                </button>

              </form>


              <span class="comment-like-count text-xs text-gray-400">
                <?= (int) $reply['like_count'] ?>
                <?= (int) $reply['like_count'] === 1 ? 'like' : 'likes' ?>
              </span>


              <?php if ((int) $reply['user_id'] === (int) $_SESSION['user_id']): ?>

                <form
                  method="POST"
                  action="<?= $basePath ?>api/posts/delete-comment.php"
                  class="ml-auto"
                  onsubmit="return confirm('Delete this reply?');">

                  <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(csrf_token()) ?>">

                  <input
                    type="hidden"
                    name="comment_id"
                    value="<?= (int) $reply['id'] ?>">

                  <button
                    type="submit"
                    class="submit-loading-btn text-xs font-medium text-red-500 transition hover:text-red-700"
                    data-loading-text="Deleting...">
                    Delete
                  </button>

                </form>

              <?php endif; ?>

            </div>

          </div>

        </div>

      <?php endforeach; ?>

    </div>

  <?php endif; ?>

</div>