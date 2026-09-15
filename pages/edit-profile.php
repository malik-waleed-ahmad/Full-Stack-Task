<?php

require_once '../includes/auth.php';
requireLogin('login.php');

require_once '../config/database.php';
require_once '../includes/csrf.php';
require_once '../includes/user-data.php';
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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  require_once '../includes/csrf.php';

  if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {

    $error = 'CSRF token validation failed.';
  } else {

    $bio = trim($_POST['bio'] ?? '');

    if (mb_strlen($bio) > 500) {

      $error = 'Bio cannot exceed 500 characters.';
    } else {

      try {

        // Keep old profile picture unless user uploads a new one
        $profilePicture = $user['profile_picture'] ?? null;


        // Check if a new profile picture was selected
        if (
          isset($_FILES['profile_picture']) &&
          $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

          if (
            $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK
          ) {

            throw new Exception(
              'Profile picture upload failed.'
            );
          }


          $tmpName =
            $_FILES['profile_picture']['tmp_name'];

          $fileSize =
            $_FILES['profile_picture']['size'];


          // Maximum 5 MB
          $maxFileSize =
            5 * 1024 * 1024;


          if ($fileSize > $maxFileSize) {

            throw new Exception(
              'Profile picture must be smaller than 5 MB.'
            );
          }


          // Detect real MIME type
          $fileType =
            mime_content_type($tmpName);


          $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
          ];


          if (
            !in_array(
              $fileType,
              $allowedTypes,
              true
            )
          ) {

            throw new Exception(
              'Only JPG, PNG and WebP images are allowed.'
            );
          }


          // Choose safe extension
          $extension = match ($fileType) {

            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
          };


          // Profile upload folder
          $uploadDirectory =
            '../uploads/profile/';


          // Create folder if it does not exist
          if (!is_dir($uploadDirectory)) {

            if (
              !mkdir(
                $uploadDirectory,
                0755,
                true
              )
            ) {

              throw new Exception(
                'Could not create profile upload directory.'
              );
            }
          }


          // Generate random safe filename
          $filename =
            bin2hex(random_bytes(16))
            . '.'
            . $extension;


          $destination =
            $uploadDirectory
            . $filename;


          // Move uploaded file
          if (
            !move_uploaded_file(
              $tmpName,
              $destination
            )
          ) {

            throw new Exception(
              'Failed to save profile picture.'
            );
          }


          $newProfilePicture =
            'uploads/profile/'
            . $filename;


          // Delete previous locally uploaded image
          if (
            !empty($profilePicture) &&
            str_starts_with(
              $profilePicture,
              'uploads/profile/'
            )
          ) {

            $oldProfileFile =
              '../'
              . $profilePicture;


            if (is_file($oldProfileFile)) {

              unlink($oldProfileFile);
            }
          }


          $profilePicture =
            $newProfilePicture;
        }


        // Update database
        $stmt = $pdo->prepare(
          "UPDATE users
         SET bio = ?, profile_picture = ?
         WHERE id = ?"
        );


        $stmt->execute([
          $bio !== '' ? $bio : null,
          $profilePicture,
          $userId
        ]);


        $_SESSION['toast_message'] =
          'Profile updated successfully';

        $_SESSION['toast_type'] =
          'success';


        $success =
          'Profile updated successfully.';


        // Update displayed data
        $user['bio'] =
          $bio;

        $user['profile_picture'] =
          $profilePicture;
      } catch (Exception $e) {

        $error =
          $e->getMessage();
      }
    }
  }
}

$pageTitle = 'Edit Profile';
$basePath = '../';

$avatarUrl = mediaUrl(
  $user['profile_picture'] ?? null,
  $basePath
);

require_once '../includes/header.php';
require_once '../includes/navbar.php';

?>

<main class="min-h-screen bg-gray-50 px-4 py-8">

  <div class="mx-auto max-w-3xl">

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

      <!-- Header -->
      <div class="border-b border-gray-100 px-6 py-5 sm:px-8">

        <h1 class="text-2xl font-bold text-gray-900">
          Edit Profile
        </h1>

        <p class="mt-1 text-sm text-gray-500">
          Update how your profile appears to other users.
        </p>

      </div>


      <!-- Error -->
      <?php if (!empty($error)): ?>

        <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:mx-8">
          <?= htmlspecialchars($error) ?>
        </div>

      <?php endif; ?>


      <!-- Success -->
      <?php if (!empty($success)): ?>

        <div class="mx-6 mt-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 sm:mx-8">
          <?= htmlspecialchars($success) ?>
        </div>

      <?php endif; ?>


      <form
        method="POST"
        enctype="multipart/form-data"
        id="edit-profile-form"
        class="space-y-6 px-6 py-6 sm:px-8"
        novalidate>

        <input
          type="hidden"
          name="csrf_token"
          value="<?= htmlspecialchars(csrf_token()) ?>">

        <!-- Profile Preview -->
        <div class="mb-8 flex flex-col items-center gap-4 rounded-2xl bg-gray-50 p-6 sm:flex-row">

          <!-- Image -->
          <img
            id="profile-preview-image"
            src="<?= $avatarUrl ? htmlspecialchars($avatarUrl) : '' ?>"
            alt="<?= htmlspecialchars($user['username']) ?>"
            class="<?= $avatarUrl ? '' : 'hidden ' ?>h-24 w-24 shrink-0 rounded-full object-cover ring-4 ring-white shadow-sm">

          <!-- Fallback Avatar -->
          <div
            id="profile-preview-fallback"
            class="<?= $avatarUrl ? 'hidden ' : 'flex ' ?>h-24 w-24 shrink-0 items-center justify-center rounded-full bg-blue-100 text-3xl font-bold text-blue-600 ring-4 ring-white shadow-sm">
            <?= htmlspecialchars(
              strtoupper(substr($user['username'], 0, 1))
            ) ?>
          </div>

          <div class="text-center sm:text-left">

            <h2 class="text-lg font-semibold text-gray-900">
              <?= htmlspecialchars($user['username']) ?>
            </h2>

            <p class="mt-1 text-sm text-gray-500">
              Preview of your public profile.
            </p>

          </div>

        </div>


        <div class="text-center sm:text-left">

          <h2 class="text-lg font-semibold text-gray-900">
            <?= htmlspecialchars($user['username']) ?>
          </h2>

          <p class="mt-1 text-sm text-gray-500">
            Preview of your public profile.
          </p>

        </div>

    </div>


    <div class="space-y-6">

      <!-- Username -->
      <div>

        <label
          for="username"
          class="mb-2 block text-sm font-medium text-gray-700">
          Username
        </label>

        <input
          type="text"
          id="username"
          value="<?= htmlspecialchars($user['username']) ?>"
          disabled
          class="w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 text-gray-500 outline-none">

        <p class="mt-2 text-xs text-gray-500">
          Your username cannot be changed.
        </p>

      </div>


      <!-- Bio -->
      <div>

        <div class="mb-2 flex items-center justify-between">

          <label
            for="bio"
            class="text-sm font-medium text-gray-700">
            Bio
          </label>

          <span
            id="bio-count"
            class="text-xs text-gray-400">
            0 / 500
          </span>

        </div>

        <textarea
          id="bio"
          name="bio"
          rows="5"
          maxlength="500"
          placeholder="Tell people something about yourself..."
          class="w-full resize-none rounded-xl border border-gray-300 px-4 py-3 text-sm leading-6 text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"><?= htmlspecialchars($_POST['bio'] ?? $user['bio'] ?? '') ?></textarea>
        <p
          id="bio-error"
          class="mt-2 hidden text-xs font-medium text-red-500">
        </p>

      </div>


      <!-- Profile Picture Upload -->
      <div>

        <label
          for="profile_picture"
          class="mb-2 block text-sm font-medium text-gray-700">
          Profile Picture
        </label>

        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5">

          <div class="flex flex-col items-center text-center">

            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 text-xl">
              📷
            </div>

            <p class="text-sm font-medium text-gray-800">
              Upload a new profile picture
            </p>

            <p class="mt-1 text-xs text-gray-500">
              JPG, PNG or WebP · Maximum 5 MB
            </p>

            <label
              for="profile_picture"
              class="mt-4 cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
              Choose Image
            </label>

            <input
              type="file"
              id="profile_picture"
              name="profile_picture"
              accept="image/jpeg,image/png,image/webp"
              class="hidden">

          </div>

        </div>

        <p
          id="profile-picture-error"
          class="mt-2 hidden text-xs font-medium text-red-500">
        </p>

      </div>

    </div>


    <!-- Actions -->
    <div class="mt-8 flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">

      <a
        href="profile.php"
        class="rounded-full px-5 py-2.5 text-center text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">
        Cancel
      </a>

      <button
        type="submit"
        class="submit-loading-btn rounded-full bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
        data-loading-text="Saving...">
        Save Changes
      </button>

    </div>

    </form>

  </div>

  </div>

</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    const bio = document.getElementById('bio');
    const bioCount = document.getElementById('bio-count');

    const profileInput =
      document.getElementById('profile_picture');

    const profileError =
      document.getElementById('profile-picture-error');

    const previewImage =
      document.getElementById('profile-preview-image');

    const previewFallback =
      document.getElementById('profile-preview-fallback');





    // Bio character counter
    function updateBioCount() {

      if (!bio || !bioCount) {
        return;
      }

      bioCount.textContent = `${bio.value.length} / 500`;
    }

    updateBioCount();

    if (bio) {
      bio.addEventListener('input', updateBioCount);
    }


    // Profile picture file preview
    if (profileInput && previewImage && previewFallback) {

      profileInput.addEventListener('change', function() {

        const file = this.files[0];

        // No file selected
        if (!file) {
          return;
        }

        const allowedTypes = [
          'image/jpeg',
          'image/png',
          'image/webp'
        ];

        const maxFileSize = 5 * 1024 * 1024;


        // Validate file type
        if (!allowedTypes.includes(file.type)) {

          if (profileError) {
            profileError.textContent =
              'Only JPG, PNG and WebP images are allowed.';

            profileError.classList.remove('hidden');
          }

          this.value = '';

          return;
        }


        // Validate file size
        if (file.size > maxFileSize) {

          if (profileError) {
            profileError.textContent =
              'Profile picture must be smaller than 5 MB.';

            profileError.classList.remove('hidden');
          }

          this.value = '';

          return;
        }


        // Clear previous error
        if (profileError) {
          profileError.textContent = '';
          profileError.classList.add('hidden');
        }


        // Preview selected image
        const reader = new FileReader();

        reader.onload = function(event) {

          previewImage.src = event.target.result;

          previewImage.classList.remove('hidden');

          previewFallback.classList.add('hidden');
          previewFallback.classList.remove('flex');

        };

        reader.readAsDataURL(file);

      });


      // Fallback if preview fails
      previewImage.addEventListener('error', function() {

        previewImage.classList.add('hidden');

        previewFallback.classList.remove('hidden');
        previewFallback.classList.add('flex');

      });

    }

  });
</script>

<?php require_once '../includes/footer.php'; ?>