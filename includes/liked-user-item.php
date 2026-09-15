<?php

require_once __DIR__ . '/media.php';

if (!isset($user)) {
  return;
}

$basePath = $basePath ?? '';

$profileImage = mediaUrl(
  $user['profile_picture'] ?? null,
  $basePath
);

?>

<div
  class="liked-user flex items-center gap-3 px-6 py-4 transition hover:bg-gray-50"
  data-like-id="<?= (int) $user['like_id'] ?>">

  <!-- Avatar -->
  <a
    href="<?= $basePath ?>pages/user-profile.php?id=<?= (int) $user['id'] ?>"
    class="shrink-0"
    aria-label="View <?= htmlspecialchars($user['username']) ?> profile">

    <?php if ($profileImage): ?>

      <img
        src="<?= htmlspecialchars($profileImage) ?>"
        alt="<?= htmlspecialchars($user['username']) ?>"
        class="h-12 w-12 rounded-full object-cover ring-1 ring-gray-200">

    <?php else: ?>

      <div
        class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-base font-semibold text-blue-600">
        <?= htmlspecialchars(
          strtoupper(substr($user['username'], 0, 1))
        ) ?>
      </div>

    <?php endif; ?>

  </a>


  <!-- User Info -->
  <div class="min-w-0 flex-1">

    <a
      href="<?= $basePath ?>pages/user-profile.php?id=<?= (int) $user['id'] ?>"
      class="block truncate text-sm font-semibold text-gray-900 transition hover:text-blue-600">
      <?= htmlspecialchars($user['username']) ?>
    </a>

    <?php if (!empty($user['created_at'])): ?>

      <p class="mt-0.5 text-xs text-gray-400">
        Liked on
        <?= htmlspecialchars(
          date('M j, Y', strtotime($user['created_at']))
        ) ?>
      </p>

    <?php endif; ?>

  </div>


  <!-- Profile Link -->
  <a
    href="<?= $basePath ?>pages/user-profile.php?id=<?= (int) $user['id'] ?>"
    class="hidden rounded-full border border-gray-200 px-3.5 py-1.5 text-xs font-medium text-gray-600 transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-900 sm:inline-flex">
    View Profile
  </a>

</div>