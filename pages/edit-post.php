<?php

require_once '../includes/auth.php';
requireLogin('login.php');

require_once '../config/database.php';
require_once '../includes/csrf.php';

$postId = filter_input(
  INPUT_GET,
  'id',
  FILTER_VALIDATE_INT
);

$userId = $_SESSION['user_id'];

if (!$postId) {
  header('Location: ../index.php');
  exit;
}

// Fetch only the user's own post
$stmt = $pdo->prepare(
  "SELECT id, content
     FROM posts
     WHERE id = ?
     AND user_id = ?"
);

$stmt->execute([
  $postId,
  $userId
]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

// Post doesn't exist or doesn't belong to user
if (!$post) {
  header('Location: ../index.php');
  exit;
}

$imageStmt = $pdo->prepare(
  "SELECT id, image_url
   FROM post_images
   WHERE post_id = ?
   ORDER BY id ASC"
);

$imageStmt->execute([
  $postId
]);

$images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Edit Post';
$basePath = '../';

?>



<?php require_once '../includes/header.php'; ?>

<main class="min-h-screen bg-gray-50 px-4 py-8">

  <div class="mx-auto max-w-3xl">

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

      <!-- Header -->
      <div class="border-b border-gray-100 px-6 py-5 sm:px-8">

        <h1 class="text-2xl font-bold text-gray-900">
          Edit Post
        </h1>

        <p class="mt-1 text-sm text-gray-500">
          Update your post content and manage its images.
        </p>

      </div>


      <form
        method="POST"
        action="../api/posts/update-post.php"
        enctype="multipart/form-data"
        class="p-6 sm:p-8">

        <input
          type="hidden"
          name="csrf_token"
          value="<?= htmlspecialchars(csrf_token()) ?>">

        <input
          type="hidden"
          name="post_id"
          value="<?= (int) $post['id'] ?>">


        <!-- Post Content -->
        <div>

          <div class="mb-2 flex items-center justify-between">

            <label
              for="content"
              class="text-sm font-medium text-gray-700">
              Post content
            </label>

            <span
              id="content-count"
              class="text-xs text-gray-400">
              0 / 5000
            </span>

          </div>

          <textarea
            id="content"
            name="content"
            rows="7"
            maxlength="5000"
            required
            class="w-full resize-none rounded-xl border border-gray-300 px-4 py-3 text-[15px] leading-6 text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"><?= htmlspecialchars($post['content']) ?></textarea>

        </div>


        <!-- Existing Images -->
        <div class="mt-8">

          <div class="mb-3">

            <h2 class="text-sm font-semibold text-gray-900">
              Current Images
            </h2>

            <p class="mt-1 text-xs text-gray-500">
              Select an image if you want to remove it from this post.
            </p>

          </div>


          <?php if (!empty($images)): ?>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">

              <?php foreach ($images as $image): ?>

                <label
                  class="group relative cursor-pointer overflow-hidden rounded-xl border border-gray-200 bg-gray-100">

                  <img
                    src="<?= htmlspecialchars($basePath . $image['image_url']) ?>"
                    alt="Post image"
                    class="aspect-square h-full w-full object-cover transition duration-200 group-hover:scale-[1.02]">

                  <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent px-3 pb-3 pt-8">

                    <div class="flex items-center gap-2 rounded-lg bg-white/95 px-3 py-2 text-xs font-medium text-gray-700 shadow-sm">

                      <input
                        type="checkbox"
                        name="delete_images[]"
                        value="<?= (int) $image['id'] ?>"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                      Remove image

                    </div>

                  </div>

                </label>

              <?php endforeach; ?>

            </div>

          <?php else: ?>

            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-5 py-8 text-center">

              <p class="text-sm text-gray-500">
                This post doesn't have any images.
              </p>

            </div>

          <?php endif; ?>

        </div>


        <!-- Add New Images -->
        <div class="mt-8">

          <label
            for="new_images"
            class="mb-3 block text-sm font-semibold text-gray-900">
            Add New Images
          </label>

          <label
            for="new_images"
            class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-center transition hover:border-blue-400 hover:bg-blue-50/30">

            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-xl shadow-sm">
              ＋
            </div>

            <p class="mt-3 text-sm font-medium text-gray-700">
              Choose images
            </p>

            <p class="mt-1 text-xs text-gray-500">
              JPEG, PNG or WEBP. You can select multiple files.
            </p>

          </label>

          <input
            type="file"
            id="new_images"
            name="new_images[]"
            multiple
            accept="image/jpeg,image/png,image/webp"
            class="hidden">


          <!-- New Image Preview -->
          <div
            id="new-image-preview"
            class="mt-4 hidden grid grid-cols-2 gap-3 sm:grid-cols-3"></div>

        </div>


        <!-- Actions -->
        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-end">

          <a
            href="../index.php"
            class="rounded-full px-5 py-2.5 text-center text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">
            Cancel
          </a>

          <button
            type="submit"
            class="submit-loading-btn rounded-full bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
            data-loading-text="Updating...">
            Save Changes
          </button>

        </div>

      </form>

    </div>

  </div>

</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    const content = document.getElementById('content');
    const contentCount = document.getElementById('content-count');

    const newImages = document.getElementById('new_images');
    const previewContainer = document.getElementById('new-image-preview');


    // Character counter
    function updateContentCount() {

      if (!content || !contentCount) {
        return;
      }

      contentCount.textContent =
        `${content.value.length} / 5000`;
    }

    updateContentCount();

    if (content) {
      content.addEventListener('input', updateContentCount);
    }


    // New image previews
    if (newImages && previewContainer) {

      newImages.addEventListener('change', function() {

        previewContainer.innerHTML = '';

        const files = Array.from(this.files);

        if (files.length === 0) {

          previewContainer.classList.add('hidden');
          return;
        }

        previewContainer.classList.remove('hidden');

        files.forEach(function(file) {

          if (!file.type.startsWith('image/')) {
            return;
          }

          const reader = new FileReader();

          reader.addEventListener('load', function(event) {

            const wrapper = document.createElement('div');

            wrapper.className =
              'overflow-hidden rounded-xl border border-gray-200 bg-gray-100';

            const image = document.createElement('img');

            image.src = event.target.result;
            image.alt = 'New image preview';
            image.className =
              'aspect-square h-full w-full object-cover';

            wrapper.appendChild(image);

            previewContainer.appendChild(wrapper);

          });

          reader.readAsDataURL(file);

        });

      });

    }

  });
</script>

<?php require_once '../includes/footer.php'; ?>