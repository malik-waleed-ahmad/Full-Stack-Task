<?php

require_once __DIR__ . '/media.php';


$basePath = $basePath ?? '';

if (!isset($post)) {
  return;
}

$profileImage = mediaUrl(
  $post['profile_picture'] ?? null,
  $basePath
);
?>

<article
  data-post-id="<?= (int) $post['id'] ?>"
  class="min-w-0 overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

  <!-- Post Header -->
  <div class="flex items-center gap-3">

    <?php if ($profileImage): ?>

      <img
        src="<?= htmlspecialchars($profileImage) ?>"
        alt="<?= htmlspecialchars($post['username']) ?>"
        class="h-12 w-12 rounded-full object-cover">

    <?php else: ?>

      <!-- Default Avatar -->
      <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-600">

        <?= htmlspecialchars(
          strtoupper(
            substr($post['username'], 0, 1)
          )
        ) ?>

      </div>

    <?php endif; ?>


    <!-- User Information -->
    <div>

      <a
        href="pages/user-profile.php?id=<?= (int) $post['user_id'] ?>"
        class="font-semibold text-gray-900 hover:text-blue-600">
        <?= htmlspecialchars($post['username']) ?>
      </a>

      <p class="text-sm text-gray-500">
        <?= htmlspecialchars($post['created_at']) ?>
      </p>

    </div>

    <?php if ((int) $post['user_id'] === (int) $_SESSION['user_id']): ?>

      <div class="relative ml-auto">

        <button
          type="button"
          class="post-menu-btn rounded-full p-2 text-gray-500 transition hover:bg-gray-100"
          aria-label="Post options">
          ⋯
        </button>

        <div
          class="post-menu absolute right-0 top-10 z-20 hidden w-36 rounded-xl border border-gray-200 bg-white py-2 shadow-lg">

          <a
            href="pages/edit-post.php?id=<?= (int) $post['id'] ?>"
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            Edit
          </a>

          <form
            method="POST"
            action="api/posts/hide-post.php">

            <input
              type="hidden"
              name="csrf_token"
              value="<?= htmlspecialchars(csrf_token()) ?>">

            <input
              type="hidden"
              name="post_id"
              value="<?= (int) $post['id'] ?>">

            <button
              type="submit"
              class="submit-loading-btn block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
              data-loading-text="Hiding...">
              Hide
            </button>

          </form>

          <form
            method="POST"
            action="api/posts/delete-post.php"
            onsubmit="return confirm('Are you sure you want to delete this post?');">

            <input
              type="hidden"
              name="csrf_token"
              value="<?= htmlspecialchars(csrf_token()) ?>">

            <input
              type="hidden"
              name="post_id"
              value="<?= (int) $post['id'] ?>">

            <button
              type="submit"
              class="submit-loading-btn block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
              data-loading-text="Deleting...">
              Delete
            </button>

          </form>

        </div>

      </div>

    <?php endif; ?>

  </div>


  <!-- Post Content -->
  <?php if (!empty($post['content'])): ?>

    <div class="px-5 pb-4">

      <p class="whitespace-pre-wrap break-words text-[15px] leading-6 text-gray-800">
        <?= htmlspecialchars($post['content']) ?>
      </p>

    </div>

  <?php endif; ?>

  <?php if (!empty($post['images'])): ?>

    <?php $imageCount = count($post['images']); ?>

    <div
      class="grid gap-0.5 overflow-hidden bg-gray-100
      <?= $imageCount === 1 ? 'grid-cols-1' : 'grid-cols-2' ?>">

      <?php foreach ($post['images'] as $index => $image): ?>

        <div
          class="
          relative overflow-hidden bg-gray-100
          <?= $imageCount === 1 ? 'max-h-[520px]' : 'aspect-square' ?>
        ">

          <img
            src="<?= htmlspecialchars($basePath . $image['image_url']) ?>"
            alt="Post image"
            loading="lazy"
            class="h-full w-full object-cover">

        </div>

      <?php endforeach; ?>

    </div>

  <?php endif; ?>

  <?php

  $userLiked = !empty($post['user_liked']);

  ?>

  <!-- Engagement Stats -->
  <div class="flex items-center justify-between px-1 py-3 text-sm text-gray-500">

    <a
      href="pages/post-likes.php?post_id=<?= (int) $post['id'] ?>"
      class="post-like-count hover:text-blue-600 hover:underline">
      <?= (int) $post['like_count'] ?>
      <?= $post['like_count'] == 1 ? 'like' : 'likes' ?>
    </a>

    <button
      type="button"
      class="comments-toggle-btn hover:text-blue-600 hover:underline"
      data-post-id="<?= (int) $post['id'] ?>">
      <?= (int) $post['comment_count'] ?>
      <?= $post['comment_count'] == 1 ? 'comment' : 'comments' ?>
    </button>

  </div>

  <div class="border-t border-gray-100"></div>

  <!-- Action Bar -->
  <div class="grid grid-cols-2 gap-1 py-1">

    <form
      method="POST"
      action="api/posts/toggle-like.php"
      class="like-form">

      <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(csrf_token()) ?>">

      <input
        type="hidden"
        name="post_id"
        value="<?= (int) $post['id'] ?>">

      <button
        type="submit"
        data-like-button
        class="flex w-full items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition
      <?= $userLiked
        ? 'text-red-600 hover:bg-red-50'
        : 'text-gray-600 hover:bg-gray-100'
      ?>">

        <span class="like-icon text-lg">
          <?= $userLiked ? '❤️' : '♡' ?>
        </span>

        <span class="like-label">
          <?= $userLiked ? 'Liked' : 'Like' ?>
        </span>

      </button>

    </form>


    <button
      type="button"
      class="comment-focus-btn flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100"
      data-post-id="<?= (int) $post['id'] ?>">

      <span>💬</span>

      <span>Comment</span>

    </button>

  </div>

  <?php if (
    !empty($post['top_comment']) &&
    (int) $post['top_comment']['like_count'] > 0
  ): ?>

    <div class="mt-4 rounded-xl bg-gray-50 p-4">

      <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
        Most liked comment
      </p>

      <div class="flex items-start justify-between gap-3">

        <div class="min-w-0">

          <a
            href="pages/user-profile.php?id=<?= (int) $post['top_comment']['user_id'] ?>"
            class="text-sm font-semibold text-gray-900 hover:text-blue-600">
            <?= htmlspecialchars($post['top_comment']['username']) ?>
          </a>

          <p class="mt-1 text-sm text-gray-700">
            <?= nl2br(htmlspecialchars($post['top_comment']['content'])) ?>
          </p>

        </div>

        <span class="shrink-0 text-xs text-gray-500">
          ❤️ <?= (int) $post['top_comment']['like_count'] ?>
        </span>

      </div>

    </div>

  <?php endif; ?>

  <!-- Add Comment -->
  <div class="add-comment-form hidden mt-4 w-full">
    <form
      method="POST"
      action="api/posts/add-comment.php"
      class="mt-4 flex w-full min-w-0 items-center gap-3">

      <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(csrf_token()) ?>">

      <input
        type="hidden"
        name="post_id"
        value="<?= (int) $post['id'] ?>">

      <!-- Current User Avatar -->

      <?php if ($profileImage): ?>

        <img
          src="<?= htmlspecialchars($profileImage) ?>"
          alt="<?= htmlspecialchars($post['username']) ?>"
          class="h-9 w-9 rounded-full object-cover">

      <?php else: ?>

        <!-- Default Avatar -->
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-600">

          <?= htmlspecialchars(
            strtoupper(
              substr($post['username'], 0, 1)
            )
          ) ?>

        </div>

      <?php endif; ?>


      <!-- Comment Input -->
      <div class="flex min-w-0 flex-1 items-center rounded-full border border-gray-300 bg-gray-50 px-4 transition focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100">

        <input
          type="text"
          name="content"
          maxlength="1000"
          required
          placeholder="Write a comment..."
          class="min-w-0 flex-1 bg-transparent py-2.5 text-sm text-gray-900 outline-none">

      </div>


      <!-- Submit Button -->
      <button
        type="submit"
        class="submit-loading-btn shrink-0 rounded-full bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
        data-loading-text="Posting...">
        Post
      </button>

    </form>

  </div>

  <!-- Comments -->
  <div
    class="post-comments-section mt-5 hidden w-full border-t border-gray-100 pt-5"
    data-post-id="<?= (int) $post['id'] ?>">

    <h3 class="mb-4 text-sm font-semibold text-gray-900">
      Comments
    </h3>

    <?php if (!empty($post['comments'])): ?>

      <div
        class="comments-container mb-5 w-full space-y-3"
        data-post-id="<?= (int) $post['id'] ?>">

        <?php
        $postId = (int) $post['id'];
        $replies = $post['replies'] ?? [];
        ?>

        <?php foreach ($post['comments'] as $comment): ?>

          <?php require __DIR__ . '/comment-item.php'; ?>

        <?php endforeach; ?>

      </div>

      <?php if (count($post['comments']) === 5): ?>
        <div
          class="comments-scroll-trigger"
          data-post-id="<?= (int)$post['id'] ?>"
          style="height: 20px;">
        </div>
      <?php endif; ?>

    <?php else: ?>

      <p class="mb-5 text-sm text-gray-500">
        No comments yet. Be the first to comment!
      </p>

    <?php endif; ?>


  </div>

</article>