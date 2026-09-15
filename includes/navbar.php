<?php

require_once __DIR__ . '/user-data.php';
require_once __DIR__ . '/media.php';

$basePath = $basePath ?? '';

$navbarUser = null;
$navbarAvatarUrl = null;

if (!empty($_SESSION['user_id']) && isset($pdo)) {

  $navbarUser = getUserById(
    $pdo,
    (int) $_SESSION['user_id']
  );

  if ($navbarUser) {

    $navbarAvatarUrl = mediaUrl(
      $navbarUser['profile_picture'] ?? null,
      $basePath
    );
  }
}

?>

<nav class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 shadow-sm backdrop-blur">

  <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">

    <!-- Logo -->
    <a
      href="<?= $basePath ?? '' ?>index.php"
      class="text-xl font-bold tracking-tight text-blue-600">
      Full Stack Task
    </a>


    <?php if (isset($_SESSION['user_id'])): ?>

      <!-- Desktop Navigation -->
      <div class="hidden items-center gap-2 md:flex">

        <a
          href="<?= $basePath ?? '' ?>index.php"
          class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">
          Home
        </a>

        <a
          href="<?= $basePath ?? '' ?>pages/create-post.php"
          class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">
          Create Post
        </a>

        <a
          href="<?= $basePath ?? '' ?>pages/hidden-posts.php"
          class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">
          Hidden Posts
        </a>


        <!-- Profile Dropdown -->
        <div class="relative ml-2">

          <button
            type="button"
            id="profile-menu-button"
            class="flex items-center gap-2 rounded-full border border-gray-200 bg-white p-1 pr-3 transition hover:bg-gray-50">

            <!-- Avatar -->
            <?php if ($navbarAvatarUrl): ?>

              <img
                src="<?= htmlspecialchars($navbarAvatarUrl) ?>"
                alt="<?= htmlspecialchars($navbarUser['username'] ?? 'User') ?>"
                class="h-9 w-9 shrink-0 rounded-full object-cover">

            <?php else: ?>

              <!-- Fallback avatar -->
              <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                <?= htmlspecialchars(
                  strtoupper(
                    substr($navbarUser['username'] ?? $_SESSION['username'] ?? 'U', 0, 1)
                  )
                ) ?>
              </div>

            <?php endif; ?>

            <!-- Username -->
            <span class="max-w-[120px] truncate text-sm font-medium text-gray-700">
              <?= htmlspecialchars(
                $navbarUser['username'] ?? $_SESSION['username'] ?? 'User'
              ) ?>
            </span>


            <span class="text-xs text-gray-400">
              ▼
            </span>

          </button>


          <!-- Dropdown -->
          <div
            id="profile-menu"
            class="absolute right-0 top-12 hidden w-48 rounded-xl border border-gray-200 bg-white py-2 shadow-lg">

            <a
              href="<?= $basePath ?? '' ?>pages/profile.php"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
              Profile
            </a>

            <a
              href="<?= $basePath ?? '' ?>pages/hidden-posts.php"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
              Hidden Posts
            </a>

            <div class="my-1 border-t border-gray-100"></div>

            <a
              href="<?= $basePath ?? '' ?>api/auth/logout.php"
              class="block px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
              Logout
            </a>

          </div>

        </div>

      </div>


      <!-- Mobile Menu Button -->
      <button
        type="button"
        id="mobile-menu-button"
        class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 md:hidden"
        aria-label="Open menu">
        ☰
      </button>


    <?php else: ?>

      <!-- Guest Navigation -->
      <div class="flex items-center gap-3">

        <a
          href="<?= $basePath ?? '' ?>pages/login.php"
          class="text-sm font-medium text-gray-600 hover:text-blue-600">
          Login
        </a>

        <a
          href="<?= $basePath ?? '' ?>pages/signup.php"
          class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
          Sign Up
        </a>

      </div>

    <?php endif; ?>

  </div>


  <?php if (isset($_SESSION['user_id'])): ?>

    <!-- Mobile Navigation -->
    <div
      id="mobile-menu"
      class="hidden border-t border-gray-100 bg-white px-4 py-3 md:hidden">

      <div class="flex flex-col gap-1">

        <a
          href="<?= $basePath ?? '' ?>index.php"
          class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
          Home
        </a>

        <a
          href="<?= $basePath ?? '' ?>pages/create-post.php"
          class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
          Create Post
        </a>

        <a
          href="<?= $basePath ?? '' ?>pages/profile.php"
          class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
          Profile
        </a>

        <a
          href="<?= $basePath ?? '' ?>pages/hidden-posts.php"
          class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
          Hidden Posts
        </a>

        <a
          href="<?= $basePath ?? '' ?>api/auth/logout.php"
          class="rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
          Logout
        </a>

      </div>

    </div>

  <?php endif; ?>

</nav>