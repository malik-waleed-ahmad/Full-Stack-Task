<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once '../includes/auth.php';
requireLogin('login.php');

require_once '../config/database.php';
require_once '../includes/csrf.php';
require_once '../includes/user-data.php';
require_once '../includes/media.php';

$basePath = '../';

$currentUser = getUserById(
  $pdo,
  (int) $_SESSION['user_id']
);

$currentUserAvatar = mediaUrl(
  $currentUser['profile_picture'] ?? null,
  $basePath
);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  require_once '../includes/csrf.php';

  if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $error = 'CSRF token validation failed.';
  } else {
    $content = trim($_POST['content'] ?? '');

    if (empty($content)) {

      $error = 'Post content cannot be empty.';
    } else {

      try {

        // Start transaction
        $pdo->beginTransaction();

        // Create the post
        $stmt = $pdo->prepare(
          "INSERT INTO posts (user_id, content)
         VALUES (?, ?)"
        );

        $stmt->execute([
          $_SESSION['user_id'],
          $content
        ]);

        // Get the newly created post ID
        $postId = $pdo->lastInsertId();

        // Handle uploaded images
        if (
          isset($_FILES['images']) &&
          !empty($_FILES['images']['name'][0])
        ) {

          $uploadDirectory = '../uploads/posts/';

          $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
          ];

          $maxFileSize = 5 * 1024 * 1024; // 5 MB

          foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {

            // Skip files with upload errors
            if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
              continue;
            }

            $fileSize = $_FILES['images']['size'][$key];

            $fileType = mime_content_type($tmpName);

            // Validate file type
            if (!in_array($fileType, $allowedTypes, true)) {
              throw new Exception('Only JPG, PNG, and WebP images are allowed.');
            }

            // Validate file size
            if ($fileSize > $maxFileSize) {
              throw new Exception('Each image must be smaller than 5 MB.');
            }

            // Generate a unique filename
            $extension = match ($fileType) {
              'image/jpeg' => 'jpg',
              'image/png'  => 'png',
              'image/webp' => 'webp'
            };

            $filename = bin2hex(random_bytes(16)) . '.' . $extension;

            $destination = $uploadDirectory . $filename;

            // Move uploaded file
            if (!move_uploaded_file($tmpName, $destination)) {
              throw new Exception('Failed to upload image.');
            }

            // Save image URL in database
            $imageUrl = 'uploads/posts/' . $filename;

            $stmt = $pdo->prepare(
              "INSERT INTO post_images (post_id, image_url)
             VALUES (?, ?)"
            );

            $stmt->execute([
              $postId,
              $imageUrl
            ]);
          }
        }

        // Everything succeeded
        $pdo->commit();

        $_SESSION['toast_message'] = 'Post created successfully';
        $_SESSION['toast_type'] = 'success';

        header('Location: ../index.php');
        exit;
      } catch (Exception $e) {

        // Undo database changes
        if ($pdo->inTransaction()) {
          $pdo->rollBack();
        }

        $error = $e->getMessage();
      }
    }
  }
}

$pageTitle = 'Create Post';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

?>

<main class="min-h-screen bg-gray-50 px-4 py-8">

  <div class="mx-auto max-w-2xl">

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

      <!-- Header -->
      <div class="border-b border-gray-100 px-6 py-5">

        <h1 class="text-2xl font-bold text-gray-900">
          Create Post
        </h1>

        <p class="mt-1 text-sm text-gray-500">
          Share something with the community.
        </p>

      </div>


      <!-- Error Message -->
      <?php if (!empty($error)): ?>

        <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <?= htmlspecialchars($error) ?>
        </div>

      <?php endif; ?>


      <!-- Form -->
      <form
        method="POST"
        enctype="multipart/form-data"
        id="create-post-form"
        class="p-6"
        novalidate>
        <input
          type="hidden"
          name="csrf_token"
          value="<?= htmlspecialchars(csrf_token()) ?>">


        <!-- User + Composer -->
        <div class="flex items-start gap-3">

          <!-- Avatar -->
          <?php if ($currentUserAvatar): ?>

            <img
              src="<?= htmlspecialchars($currentUserAvatar) ?>"
              alt="<?= htmlspecialchars($currentUser['username']) ?>"
              class="h-11 w-11 shrink-0 rounded-full object-cover">

          <?php else: ?>

            <div
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-600">
              <?= htmlspecialchars(
                strtoupper(substr($currentUser['username'] ?? 'U', 0, 1))
              ) ?>
            </div>

          <?php endif; ?>


          <!-- Textarea -->
          <div class="min-w-0 flex-1">

            <p class="mb-2 text-sm font-semibold text-gray-900">
              <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
            </p>

            <textarea
              id="content"
              name="content"
              rows="6"
              required
              maxlength="5000"
              placeholder="What's on your mind?"
              class="w-full resize-none border-0 bg-transparent p-0 text-base leading-7 text-gray-900 outline-none placeholder:text-gray-400 focus:ring-0"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>

            <p
              id="content-error"
              class="mt-2 hidden text-xs font-medium text-red-500">
            </p>

            <div class="mt-2 flex items-center justify-between">

              <p class="text-xs text-gray-400">
                Maximum 5000 characters
              </p>

              <p class="text-xs text-gray-400">
                <span id="content-count">0</span>/5000
              </p>

            </div>

          </div>

        </div>


        <!-- Image Preview -->
        <div
          id="image-preview"
          class="mt-5 hidden grid grid-cols-2 gap-2 overflow-hidden rounded-xl"></div>


        <!-- Upload Area -->
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4">

          <div class="flex flex-col items-center justify-center text-center">

            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 text-xl">
              📷
            </div>

            <p class="text-sm font-medium text-gray-800">
              Add photos to your post
            </p>

            <p class="mt-1 text-xs text-gray-500">
              JPG, PNG or WebP · Maximum 5 MB each
            </p>

            <label
              for="images"
              class="mt-4 cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
              Choose Images
            </label>

            <input
              type="file"
              id="images"
              name="images[]"
              multiple
              accept="image/jpeg,image/png,image/webp"
              class="hidden">

          </div>

        </div>

        <p
          id="images-error"
          class="mt-2 hidden text-xs font-medium text-red-500">
        </p>


        <!-- Actions -->
        <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-5">

          <a
            href="../index.php"
            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">
            Cancel
          </a>

          <button
            type="submit"
            class="submit-loading-btn rounded-full bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
            data-loading-text="Publishing...">
            Publish
          </button>

        </div>

      </form>

    </div>

  </div>

</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    const form =
      document.getElementById('create-post-form');

    const contentInput =
      document.getElementById('content');

    const contentError =
      document.getElementById('content-error');

    const contentCount =
      document.getElementById('content-count');

    const imageInput =
      document.getElementById('images');

    const imagesError =
      document.getElementById('images-error');

    const previewContainer =
      document.getElementById('image-preview');


    const MAX_IMAGES = 6;

    const MAX_FILE_SIZE =
      5 * 1024 * 1024;

    const ALLOWED_TYPES = [
      'image/jpeg',
      'image/png',
      'image/webp'
    ];


    function showContentError(message) {

      contentError.textContent = message;

      contentError.classList.remove('hidden');

      contentInput.classList.add(
        'text-red-700'
      );

    }


    function clearContentError() {

      contentError.textContent = '';

      contentError.classList.add('hidden');

      contentInput.classList.remove(
        'text-red-700'
      );

    }


    function showImagesError(message) {

      imagesError.textContent = message;

      imagesError.classList.remove('hidden');

    }


    function clearImagesError() {

      imagesError.textContent = '';

      imagesError.classList.add('hidden');

    }


    function validateContent() {

      const content =
        contentInput.value.trim();

      if (content === '') {

        showContentError(
          'Post content cannot be empty.'
        );

        return false;
      }

      if (content.length > 5000) {

        showContentError(
          'Post content cannot exceed 5000 characters.'
        );

        return false;
      }

      clearContentError();

      return true;

    }


    function validateImages() {

      const files =
        Array.from(imageInput.files);

      if (files.length > MAX_IMAGES) {

        showImagesError(
          'You can upload a maximum of 6 images.'
        );

        return false;
      }


      for (const file of files) {

        if (!ALLOWED_TYPES.includes(file.type)) {

          showImagesError(
            'Only JPG, PNG and WebP images are allowed.'
          );

          return false;
        }


        if (file.size > MAX_FILE_SIZE) {

          showImagesError(
            `${file.name} is larger than 5 MB.`
          );

          return false;
        }

      }


      clearImagesError();

      return true;

    }


    function renderPreviews() {

      previewContainer.innerHTML = '';

      const files =
        Array.from(imageInput.files);


      if (files.length === 0) {

        previewContainer.classList.add(
          'hidden'
        );

        return;

      }


      previewContainer.classList.remove(
        'hidden'
      );


      files.forEach(function(file) {

        if (!ALLOWED_TYPES.includes(file.type)) {
          return;
        }

        const reader =
          new FileReader();


        reader.onload = function(event) {

          const wrapper =
            document.createElement('div');

          wrapper.className =
            'aspect-square overflow-hidden rounded-xl bg-gray-100';


          const image =
            document.createElement('img');

          image.src =
            event.target.result;

          image.alt =
            'Selected image preview';

          image.className =
            'h-full w-full object-cover';


          wrapper.appendChild(image);

          previewContainer.appendChild(
            wrapper
          );

        };


        reader.readAsDataURL(file);

      });

    }


    if (contentInput) {

      contentCount.textContent =
        contentInput.value.length;


      contentInput.addEventListener(
        'input',
        function() {

          contentCount.textContent =
            this.value.length;


          if (
            !contentError.classList.contains(
              'hidden'
            )
          ) {
            validateContent();
          }

        }
      );

    }


    if (imageInput) {

      imageInput.addEventListener(
        'change',
        function() {

          const valid =
            validateImages();


          if (!valid) {

            previewContainer.innerHTML =
              '';

            previewContainer.classList.add(
              'hidden'
            );

            return;
          }


          renderPreviews();

        }
      );

    }


    if (form) {

      form.addEventListener(
        'submit',
        function(event) {

          const contentValid =
            validateContent();

          const imagesValid =
            validateImages();


          if (
            !contentValid ||
            !imagesValid
          ) {

            event.preventDefault();


            if (!contentValid) {

              contentInput.focus();

            }

          }

        }
      );

    }

  });
</script>

<?php require_once '../includes/footer.php'; ?>