<?php

require_once '../includes/auth.php';
requireLogin('login.php');

require_once '../config/database.php';
require_once '../includes/csrf.php';

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare(
  "SELECT
        posts.id,
        posts.content,
        posts.created_at

     FROM posts

     WHERE posts.user_id = ?
     AND posts.is_hidden = TRUE

     ORDER BY posts.created_at DESC"
);

$stmt->execute([$userId]);

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch images
foreach ($posts as &$post) {

  $imageStmt = $pdo->prepare(
    "SELECT image_url
         FROM post_images
         WHERE post_id = ?
         ORDER BY id ASC"
  );

  $imageStmt->execute([
    $post['id']
  ]);

  $post['images'] = $imageStmt->fetchAll(PDO::FETCH_ASSOC);
}

unset($post);

$pageTitle = 'Hidden Posts';
$basePath = '../';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

?>

<main class="min-h-screen bg-gray-50 px-4 py-8">

  <div class="mx-auto max-w-4xl">

    <!-- Page Header -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          Hidden Posts
        </h1>

        <p class="mt-1 text-sm text-gray-500">
          Posts you hide from your public feed will appear here.
        </p>
      </div>

      <a
        href="../index.php"
        class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
        Back to Feed
      </a>

    </div>


    <?php if (empty($posts)): ?>

      <!-- Empty State -->
      <div class="rounded-2xl border border-gray-200 bg-white px-6 py-14 text-center shadow-sm">

        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-2xl">
          👁️
        </div>

        <h2 class="mt-4 text-lg font-semibold text-gray-900">
          No hidden posts
        </h2>

        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
          You haven't hidden any posts yet. Hidden posts will stay private from your normal profile and feed.
        </p>

        <a
          href="../index.php"
          class="mt-5 inline-flex rounded-full bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
          Return to Feed
        </a>

      </div>

    <?php else: ?>

      <div class="space-y-5">

        <?php foreach ($posts as $post): ?>

          <article
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

            <!-- Hidden Badge + Date -->
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">

              <div class="flex items-center gap-2">

                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                  Hidden
                </span>

              </div>

              <span class="text-xs text-gray-400">
                <?= htmlspecialchars($post['created_at']) ?>
              </span>

            </div>


            <!-- Post Content -->
            <?php if (!empty($post['content'])): ?>

              <div class="px-5 py-4">

                <p class="whitespace-pre-wrap break-words text-[15px] leading-6 text-gray-800">
                  <?= htmlspecialchars($post['content']) ?>
                </p>

              </div>

            <?php endif; ?>


            <!-- Post Images -->
            <?php if (!empty($post['images'])): ?>

              <?php $imageCount = count($post['images']); ?>

              <div
                class="grid gap-0.5 overflow-hidden bg-gray-100 <?= $imageCount === 1 ? 'grid-cols-1' : 'grid-cols-2' ?>">

                <?php foreach ($post['images'] as $image): ?>

                  <div
                    class="overflow-hidden bg-gray-100 <?= $imageCount === 1 ? 'max-h-[520px]' : 'aspect-square' ?>">

                    <img
                      src="../<?= htmlspecialchars($image['image_url']) ?>"
                      alt="Post image"
                      loading="lazy"
                      class="h-full w-full object-cover">

                  </div>

                <?php endforeach; ?>

              </div>

            <?php endif; ?>


            <!-- Bottom Actions -->
            <div class="flex flex-col gap-3 border-t border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

              <p class="text-sm text-gray-500">
                This post is currently hidden.
              </p>

              <form
                method="POST"
                action="../api/posts/unhide-post.php">

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
                  class="submit-loading-btn inline-flex w-full items-center justify-center rounded-full bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 sm:w-auto"
                  data-loading-text="Unhiding...">
                  Unhide Post
                </button>

              </form>

            </div>

          </article>

        <?php endforeach; ?>

      </div>

    <?php endif; ?>

  </div>

</main>
<?php require_once '../includes/footer.php'; ?>