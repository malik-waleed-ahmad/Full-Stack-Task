<?php

require_once '../includes/auth.php';
requireLogin('login.php');

require_once '../config/database.php';

$postId = filter_input(
  INPUT_GET,
  'post_id',
  FILTER_VALIDATE_INT
);

if (!$postId) {
  header('Location: ../index.php');
  exit;
}

// Make sure the post exists
$stmt = $pdo->prepare(
  "SELECT id
     FROM posts
     WHERE id = ?"
);

$stmt->execute([$postId]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
  header('Location: ../index.php');
  exit;
}

// Fetch users who liked the post
$stmt = $pdo->prepare(
  "SELECT
        users.id,
        users.username,
        users.profile_picture,
      post_likes.created_at,
      post_likes.id AS like_id

     FROM post_likes

     INNER JOIN users
        ON post_likes.user_id = users.id

     WHERE post_likes.post_id = ?

    ORDER BY post_likes.id DESC
    LIMIT 10"
);

$stmt->execute([$postId]);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Post Likes';
$basePath = '../';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

?>

<main class="min-h-screen bg-gray-50 px-4 py-8">

  <div class="mx-auto max-w-2xl">

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

      <!-- Page Header -->
      <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">

        <div>

          <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">
            People who liked this post
          </h1>

          <p class="mt-1 text-sm text-gray-500">
            See who reacted to this post.
          </p>

        </div>

        <a
          href="../index.php"
          class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          aria-label="Back to feed">
          ←
        </a>

      </div>


      <?php if (empty($users)): ?>

        <!-- Empty State -->
        <div class="px-6 py-14 text-center">

          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-2xl">
            ♡
          </div>

          <h2 class="mt-4 text-lg font-semibold text-gray-900">
            No likes yet
          </h2>

          <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-500">
            Nobody has liked this post yet.
          </p>

          <a
            href="../index.php"
            class="mt-5 inline-flex rounded-full bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
            Back to Feed
          </a>

        </div>

      <?php else: ?>

        <!-- Users List -->
        <div
          id="liked-users-container"
          class="divide-y divide-gray-100">

          <?php foreach ($users as $user): ?>

            <?php require '../includes/liked-user-item.php'; ?>

          <?php endforeach; ?>

        </div>


        <?php if (count($users) === 10): ?>

          <!-- Infinite Scroll Trigger -->
          <div
            id="likes-scroll-trigger"
            data-post-id="<?= (int) $postId ?>"
            class="flex min-h-20 items-center justify-center px-6 py-5">

            <span
              id="likes-loading"
              class="hidden items-center gap-2 text-sm text-gray-500">
              <span class="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-blue-600"></span>
              Loading more...
            </span>

          </div>

        <?php endif; ?>

      <?php endif; ?>

    </div>

  </div>

</main>
<script>
  const likesTrigger =
    document.getElementById('likes-scroll-trigger');

  const likesContainer =
    document.getElementById('liked-users-container');

  const likesLoading =
    document.getElementById('likes-loading');

  if (likesTrigger && likesContainer) {

    let isLoading = false;
    let hasMore = true;

    const observer = new IntersectionObserver(
      async (entries) => {

        const entry = entries[0];

        if (!entry.isIntersecting || isLoading || !hasMore) {
          return;
        }

        const likedUsers =
          likesContainer.querySelectorAll(
            '.liked-user[data-like-id]'
          );

        if (likedUsers.length === 0) {
          return;
        }

        const lastUser =
          likedUsers[likedUsers.length - 1];

        const lastLikeId =
          lastUser.dataset.likeId;

        const postId =
          likesTrigger.dataset.postId;

        try {

          isLoading = true;

          if (likesLoading) {
            likesLoading.classList.remove('hidden');
          }

          const response = await fetch(
            `../api/posts/load-post-likes.php?post_id=${encodeURIComponent(postId)}&last_like_id=${encodeURIComponent(lastLikeId)}`
          );

          if (!response.ok) {
            throw new Error('Failed to load more likes');
          }

          const data = await response.json();

          if (!data.success) {
            showToast(
              data.message || 'Unable to load more likes.',
              'error'
            );
            return;
          }

          if (data.html) {
            likesContainer.insertAdjacentHTML(
              'beforeend',
              data.html
            );
          }

          hasMore = data.has_more;

          if (!hasMore) {
            observer.disconnect();

            likesTrigger.innerHTML = `
              <span class="text-sm text-gray-400">
                No more likes
              </span>
            `;
          }

        } catch (error) {

          console.error(error);

          showToast(
            'Unable to load more likes. Please try again.',
            'error'
          );

        } finally {

          isLoading = false;

          if (hasMore && likesLoading) {
            likesLoading.classList.add('hidden');
          }
        }
      }, {
        root: null,
        rootMargin: '200px',
        threshold: 0
      }
    );

    observer.observe(likesTrigger);
  }
</script>

<?php require_once '../includes/footer.php'; ?>