<?php

require_once __DIR__ . '/user-data.php';
require_once __DIR__ . '/media.php';

$basePath = $basePath ?? '';

$sidebarUser = null;
$sidebarAvatarUrl = null;

if (!empty($_SESSION['user_id']) && isset($pdo)) {

    $sidebarUser = getUserById(
        $pdo,
        (int) $_SESSION['user_id']
    );

    if ($sidebarUser) {

        $sidebarAvatarUrl = mediaUrl(
            $sidebarUser['profile_picture'] ?? null,
            $basePath
        );
    }
}

?>

<!-- Left Sidebar -->
<aside class="hidden lg:col-span-3 lg:block">

  <div class="sticky top-24 space-y-4">

    <!-- Profile Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

      <div class="flex items-center gap-3">

        <!-- Avatar -->
        <?php if ($sidebarAvatarUrl): ?>

          <img
            src="<?= htmlspecialchars($sidebarAvatarUrl) ?>"
            alt="<?= htmlspecialchars($sidebarUser['username'] ?? 'User') ?>"
            class="h-12 w-12 shrink-0 rounded-full object-cover"
          >

        <?php else: ?>

          <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-200 font-semibold text-gray-700"
          >
            <?= htmlspecialchars(
              strtoupper(
                substr(
                  $sidebarUser['username'] ?? $_SESSION['username'] ?? 'U',
                  0,
                  1
                )
              )
            ) ?>
          </div>

        <?php endif; ?>


        <!-- User Info -->
        <div class="min-w-0">

          <p class="truncate font-semibold text-gray-900">
            <?= htmlspecialchars(
              $sidebarUser['username'] ?? $_SESSION['username'] ?? 'User'
            ) ?>
          </p>

          <a
            href="<?= $basePath ?>pages/profile.php"
            class="text-sm text-gray-500 hover:text-blue-600"
          >
            View profile
          </a>

        </div>

      </div>

    </div>


    <!-- Navigation -->
    <nav class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">

      <a
        href="<?= $basePath ?>index.php"
        class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-gray-700 transition hover:bg-gray-100"
      >
        <span>🏠</span>
        <span>Home</span>
      </a>

      <a
        href="<?= $basePath ?>pages/profile.php"
        class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-gray-700 transition hover:bg-gray-100"
      >
        <span>👤</span>
        <span>Profile</span>
      </a>

      <a
        href="<?= $basePath ?>pages/create-post.php"
        class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-gray-700 transition hover:bg-gray-100"
      >
        <span>✏️</span>
        <span>Create Post</span>
      </a>

      <a
        href="<?= $basePath ?>pages/hidden-posts.php"
        class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-gray-700 transition hover:bg-gray-100"
      >
        <span>🙈</span>
        <span>Hidden Posts</span>
      </a>

    </nav>

  </div>

</aside>