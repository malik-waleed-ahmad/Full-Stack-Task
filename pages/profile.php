<?php

require_once '../includes/auth.php';
requireLogin('login.php');

require_once '../config/database.php';
require_once '../includes/user-data.php';
require_once '../includes/csrf.php';
require_once '../includes/media.php';

$userId = (int) $_SESSION['user_id'];

$user = getUserById(
    $pdo,
    $userId
);

if (!$user) {

    session_destroy();

    header('Location: login.php');
    exit;
}


$stats = getUserStats(
    $pdo,
    $userId
);


$posts = getUserVisiblePosts(
    $pdo,
    $userId,
    $userId
);


$pageTitle = 'My Profile';
$basePath = '../';


$avatarUrl = mediaUrl(
    $user['profile_picture'] ?? null,
    $basePath
);


require_once '../includes/header.php';
require_once '../includes/navbar.php';

?>

<main class="min-h-screen bg-gray-50 px-4 py-8">

  <div class="mx-auto max-w-5xl">

    <!-- Profile Card -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

      <!-- Cover Area -->
      <div class="h-40 bg-gradient-to-r from-blue-500 to-indigo-500"></div>


      <!-- Profile Content -->
      <div class="px-6 pb-6">

        <div class="-mt-14 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

          <!-- Left Side -->
          <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-end">

            <!-- Avatar -->
            <?php if (!empty($user['profile_picture'])): ?>

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


            <!-- User Info -->
            <div class="pb-1 text-center sm:text-left">

              <h1 class="text-2xl font-bold text-gray-900">
                <?= htmlspecialchars($user['username']) ?>
              </h1>

              <?php if (!empty($user['bio'])): ?>

                <p class="mt-2 max-w-xl whitespace-pre-wrap break-words text-sm leading-6 text-gray-600">
                  <?= htmlspecialchars($user['bio']) ?>
                </p>

              <?php else: ?>

                <p class="mt-2 text-sm text-gray-400">
                  No bio added yet.
                </p>

              <?php endif; ?>

            </div>

          </div>


          <!-- Edit Profile Button -->
          <div class="flex justify-center sm:justify-end">

            <a
              href="edit-profile.php"
              class="rounded-full border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
              Edit Profile
            </a>

          </div>

        </div>


        <!-- Member Since -->
        <div class="mt-5 border-t border-gray-100 pt-4">

          <p class="text-sm text-gray-500">
            Member since
            <?= date('F Y', strtotime($user['created_at'])) ?>
          </p>

        </div>

      </div>

    </div>


    <!-- Profile Stats -->
    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">

      <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">

        <p class="text-2xl font-bold text-gray-900">
          <?= $stats['posts'] ?>
        </p>

        <p class="mt-1 text-sm text-gray-500">
          Posts
        </p>

      </div>


      <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">

        <p class="text-2xl font-bold text-gray-900">
         <?= $stats['likes_received'] ?>
        </p>

        <p class="mt-1 text-sm text-gray-500">
          Likes Received
        </p>

      </div>


      <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">

        <p class="text-2xl font-bold text-gray-900">
          <?= $stats['comments'] ?>
        </p>

        <p class="mt-1 text-sm text-gray-500">
          Comments
        </p>

      </div>

    </div>


    <!-- My Posts Section -->
    <div class="mt-6">

      <div class="mb-4 flex items-center justify-between">

        <h2 class="text-xl font-bold text-gray-900">
          My Posts
        </h2>

        <a
          href="create-post.php"
          class="text-sm font-medium text-blue-600 hover:text-blue-700">
          Create Post
        </a>

      </div>


      <?php if (!empty($posts)): ?>

        <div class="space-y-5">

          <?php foreach ($posts as $post): ?>

            <?php require '../includes/post-card.php'; ?>

          <?php endforeach; ?>

        </div>

      <?php else: ?>

        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">

          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-xl">
            📝
          </div>

          <h3 class="mt-4 font-semibold text-gray-900">
            No posts yet
          </h3>

          <p class="mt-1 text-sm text-gray-500">
            Your posts will appear here after you publish something.
          </p>

          <a
            href="create-post.php"
            class="mt-4 inline-flex rounded-full bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
            Create your first post
          </a>

        </div>

      <?php endif; ?>

    </div>

  </div>

</main>

<?php require_once '../includes/footer.php'; ?>