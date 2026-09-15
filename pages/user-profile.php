<?php

require_once '../includes/auth.php';
requireLogin('login.php');

require_once '../config/database.php';
require_once '../includes/user-data.php';
require_once '../includes/csrf.php';
require_once '../includes/media.php';


$userId = filter_input(
  INPUT_GET,
  'id',
  FILTER_VALIDATE_INT
);

if (!$userId) {
  header('Location: ../index.php');
  exit;
}


$currentUserId = (int) $_SESSION['user_id'];


// Fetch selected user
$user = getUserById(
  $pdo,
  (int) $userId
);

if (!$user) {
  header('Location: ../index.php');
  exit;
}


// Fetch profile statistics
$stats = getUserStats(
  $pdo,
  (int) $userId
);


// Fetch user's visible posts
$posts = getUserVisiblePosts(
  $pdo,
  (int) $userId,
  $currentUserId
);


$pageTitle =
  htmlspecialchars($user['username']) . ' Profile';

$basePath = '../';


// Resolve profile image
$avatarUrl = mediaUrl(
  $user['profile_picture'] ?? null,
  $basePath
);


require_once '../includes/header.php';
require_once '../includes/navbar.php';

?>

<main class="min-h-screen bg-gray-50 px-4 py-8">

  <div class="mx-auto max-w-5xl">

    <!-- Public Profile -->
    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

      <!-- Cover -->
      <div class="h-40 bg-gradient-to-r from-blue-500 to-indigo-500"></div>


      <!-- Profile Details -->
      <div class="px-6 pb-6">

        <div class="-mt-14 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

          <!-- User -->
          <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-end">

            <?php if ($avatarUrl): ?>

              <img
                src="<?= htmlspecialchars($avatarUrl) ?>"
                alt="<?= htmlspecialchars($user['username']) ?>"
                class="h-28 w-28 rounded-full border-4 border-white object-cover shadow-sm">

            <?php else: ?>

              <div
                class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full border-4 border-white bg-blue-100 text-4xl font-bold text-blue-600 shadow-sm">
                <?= htmlspecialchars(
                  strtoupper(substr($user['username'], 0, 1))
                ) ?>
              </div>

            <?php endif; ?>


            <div class="pb-1 text-center sm:text-left">

              <h1 class="text-2xl font-bold text-gray-900">
                <?= htmlspecialchars($user['username']) ?>
              </h1>

              <?php if (!empty($user['bio'])): ?>

                <p class="mt-2 max-w-xl whitespace-pre-wrap break-words text-sm leading-6 text-gray-600"><?= htmlspecialchars($user['bio']) ?></p>

              <?php else: ?>

                <p class="mt-2 text-sm text-gray-400">
                  No bio added yet.
                </p>

              <?php endif; ?>

            </div>

          </div>


          <!-- Own Profile Shortcut -->
          <?php if ((int) $_SESSION['user_id'] === (int) $user['id']): ?>

            <a
              href="edit-profile.php"
              class="rounded-full border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-100">
              Edit Profile
            </a>

          <?php endif; ?>

        </div>


        <!-- Member Since -->
        <div class="mt-5 border-t border-gray-100 pt-4">

          <p class="text-sm text-gray-500">
            Member since
            <?= date('F Y', strtotime($user['created_at'])) ?>
          </p>

        </div>

      </div>

    </section>


    <!-- Stats -->
    <section class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">

      <!-- Posts -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">

        <p class="text-2xl font-bold text-gray-900">
          <?= $stats['posts'] ?>
        </p>

        <p class="mt-1 text-sm text-gray-500">
          Posts
        </p>

      </div>


      <!-- Likes -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">

        <p class="text-2xl font-bold text-gray-900">
          <?= $stats['likes_received'] ?>
        </p>

        <p class="mt-1 text-sm text-gray-500">
          Likes Received
        </p>

      </div>


      <!-- Comments -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">

        <p class="text-2xl font-bold text-gray-900">
          <?= $stats['comments'] ?>
        </p>

        <p class="mt-1 text-sm text-gray-500">
          Comments
        </p>

      </div>

    </section>


    <!-- Posts -->
    <section class="mt-7">

      <div class="mb-4">

        <h2 class="text-xl font-bold text-gray-900">
          Posts
        </h2>

        <p class="mt-1 text-sm text-gray-500">
          Posts shared by <?= htmlspecialchars($user['username']) ?>.
        </p>

      </div>


      <?php if (!empty($posts)): ?>

        <div class="space-y-5">

          <?php foreach ($posts as $post): ?>

            <?php require '../includes/post-card.php'; ?>

          <?php endforeach; ?>

        </div>

      <?php else: ?>

        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white px-6 py-12 text-center shadow-sm">

          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-xl">
            📝
          </div>

          <h3 class="mt-4 font-semibold text-gray-900">
            No posts yet
          </h3>

          <p class="mt-1 text-sm text-gray-500">
            <?= htmlspecialchars($user['username']) ?>
            hasn't shared anything yet.
          </p>

        </div>

      <?php endif; ?>

    </section>

  </div>

</main>

<?php require_once '../includes/footer.php'; ?>