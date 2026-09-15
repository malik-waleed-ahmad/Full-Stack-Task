<?php

require_once '../config/database.php';
require_once '../includes/csrf.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  require_once '../includes/csrf.php';

  if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $error = 'CSRF token validation failed.';
  } else {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Username validation
    if (empty($username) || empty($password)) {
      $error = 'Username and password are required.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
      $error = 'Username must be between 3 and 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
      $error = 'Username can only contain letters, numbers, and underscores.';
    } elseif (strlen($password) < 6) {
      $error = 'Password must be at least 6 characters long.';
    } else {

      // Check whether username already exists
      $stmt = $pdo->prepare(
        "SELECT id FROM users WHERE username = ?"
      );

      $stmt->execute([$username]);

      if ($stmt->fetch()) {

        $error = 'Username already exists.';
      } else {

        // Hash password securely
        $hashedPassword = password_hash(
          $password,
          PASSWORD_DEFAULT
        );

        // Insert new user
        $stmt = $pdo->prepare(
          "INSERT INTO users (username, password)
                 VALUES (?, ?)"
        );

        $stmt->execute([
          $username,
          $hashedPassword
        ]);

        header('Location: login.php?registered=1');
        exit;
      }
    }
  }
}

$pageTitle = 'Sign Up';
$basePath = '../';

require_once '../includes/header.php';

?>

<main class="min-h-screen bg-gray-50 px-4 py-10">

  <div class="mx-auto flex min-h-[calc(100vh-120px)] max-w-6xl items-center justify-center">

    <div class="grid w-full overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm lg:grid-cols-2">

      <!-- Left Branding Panel -->
      <div class="hidden bg-gradient-to-br from-indigo-600 to-blue-600 p-10 text-white lg:flex lg:flex-col lg:justify-between">

        <div>

          <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-xl font-bold backdrop-blur">
            S
          </div>

          <h2 class="mt-8 text-4xl font-bold leading-tight">
            Join the community.
          </h2>

          <p class="mt-4 max-w-md text-sm leading-6 text-blue-100">
            Create an account, share your thoughts, connect with other users, and become part of the conversation.
          </p>

        </div>

        <p class="text-sm text-blue-100">
          Start sharing in just a few seconds.
        </p>

      </div>


      <!-- Signup Side -->
      <div class="p-6 sm:p-10 lg:p-12">

        <div class="mx-auto max-w-md">

          <!-- Heading -->
          <div class="mb-8">

            <h1 class="text-3xl font-bold text-gray-900">
              Create your account
            </h1>

            <p class="mt-2 text-sm text-gray-500">
              Choose a username and password to get started.
            </p>

          </div>


          <!-- Error Message -->
          <?php if (!empty($error)): ?>

            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
              <?= htmlspecialchars($error) ?>
            </div>

          <?php endif; ?>


          <!-- Signup Form -->
          <form
            method="POST"
            id="signup-form"
            class="space-y-5"
            novalidate>

            <input
              type="hidden"
              name="csrf_token"
              value="<?= htmlspecialchars(csrf_token()) ?>">


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
                name="username"
                required
                minlength="3"
                maxlength="50"
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                placeholder="Choose a username"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

              <p
                id="username-error"
                class="mt-2 hidden text-xs font-medium text-red-500"></p>

              <p class="mt-2 text-xs text-gray-500">
                3–50 characters. Letters, numbers and underscores only.
              </p>

            </div>


            <!-- Password -->
            <div>

              <label
                for="password"
                class="mb-2 block text-sm font-medium text-gray-700">
                Password
              </label>

              <div class="relative">

                <input
                  type="password"
                  id="password"
                  name="password"
                  required
                  minlength="6"
                  autocomplete="new-password"
                  placeholder="Create a password"
                  class="w-full rounded-xl border border-gray-300 px-4 py-3 pr-14 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                <button
                  type="button"
                  id="toggle-password"
                  class="absolute inset-y-0 right-0 flex items-center px-4 text-sm font-medium text-gray-500 transition hover:text-gray-800">
                  Show
                </button>

              </div>

              <p
                id="password-error"
                class="mt-2 hidden text-xs font-medium text-red-500"></p>

              <div class="mt-2 flex items-center justify-between">

                <p class="text-xs text-gray-500">
                  At least 6 characters.
                </p>

                <span
                  id="password-strength"
                  class="text-xs font-medium text-gray-400">
                  —
                </span>

              </div>

            </div>


            <!-- Submit -->
            <button
              type="submit"
              class="submit-loading-btn w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
              data-loading-text="Creating account...">
              Create Account
            </button>

          </form>


          <!-- Login Link -->
          <p class="mt-7 text-center text-sm text-gray-500">

            Already have an account?

            <a
              href="login.php"
              class="font-semibold text-blue-600 transition hover:text-blue-700">
              Sign in
            </a>

          </p>

        </div>

      </div>

    </div>

  </div>

</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    const passwordInput =
      document.getElementById('password');

    const togglePassword =
      document.getElementById('toggle-password');

    const strengthText =
      document.getElementById('password-strength');


    // Show / Hide Password
    if (passwordInput && togglePassword) {

      togglePassword.addEventListener('click', function() {

        const isHidden =
          passwordInput.type === 'password';

        passwordInput.type =
          isHidden ? 'text' : 'password';

        togglePassword.textContent =
          isHidden ? 'Hide' : 'Show';

      });

    }


    // Simple password strength indicator
    if (passwordInput && strengthText) {

      passwordInput.addEventListener('input', function() {

        const password = this.value;

        if (password.length === 0) {

          strengthText.textContent = '—';
          strengthText.className =
            'text-xs font-medium text-gray-400';

          return;
        }

        if (password.length < 6) {

          strengthText.textContent = 'Too short';
          strengthText.className =
            'text-xs font-medium text-red-500';

          return;
        }

        let score = 0;

        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        if (score <= 1) {

          strengthText.textContent = 'Weak';
          strengthText.className =
            'text-xs font-medium text-orange-500';

        } else if (score <= 3) {

          strengthText.textContent = 'Good';
          strengthText.className =
            'text-xs font-medium text-blue-600';

        } else {

          strengthText.textContent = 'Strong';
          strengthText.className =
            'text-xs font-medium text-green-600';

        }

      });

    }

    const signupForm =
      document.getElementById('signup-form');

    const usernameInput =
      document.getElementById('username');

    const usernameError =
      document.getElementById('username-error');

    const passwordError =
      document.getElementById('password-error');


    function showFieldError(input, errorElement, message) {

      input.classList.remove(
        'border-gray-300',
        'focus:border-blue-500',
        'focus:ring-blue-100'
      );

      input.classList.add(
        'border-red-400',
        'focus:border-red-500',
        'focus:ring-red-100'
      );

      errorElement.textContent = message;
      errorElement.classList.remove('hidden');
    }


    function clearFieldError(input, errorElement) {

      input.classList.remove(
        'border-red-400',
        'focus:border-red-500',
        'focus:ring-red-100'
      );

      input.classList.add(
        'border-gray-300',
        'focus:border-blue-500',
        'focus:ring-blue-100'
      );

      errorElement.textContent = '';
      errorElement.classList.add('hidden');
    }


    function validateUsername() {

      if (!usernameInput || !usernameError) {
        return true;
      }

      const username = usernameInput.value.trim();

      if (username === '') {

        showFieldError(
          usernameInput,
          usernameError,
          'Username is required.'
        );

        return false;
      }

      if (username.length < 3) {

        showFieldError(
          usernameInput,
          usernameError,
          'Username must contain at least 3 characters.'
        );

        return false;
      }

      if (username.length > 50) {

        showFieldError(
          usernameInput,
          usernameError,
          'Username cannot exceed 50 characters.'
        );

        return false;
      }

      if (!/^[a-zA-Z0-9_]+$/.test(username)) {

        showFieldError(
          usernameInput,
          usernameError,
          'Use only letters, numbers and underscores.'
        );

        return false;
      }

      clearFieldError(
        usernameInput,
        usernameError
      );

      return true;
    }


    function validatePassword() {

      if (!passwordInput || !passwordError) {
        return true;
      }

      const password = passwordInput.value;

      if (password === '') {

        showFieldError(
          passwordInput,
          passwordError,
          'Password is required.'
        );

        return false;
      }

      if (password.length < 6) {

        showFieldError(
          passwordInput,
          passwordError,
          'Password must contain at least 6 characters.'
        );

        return false;
      }

      clearFieldError(
        passwordInput,
        passwordError
      );

      return true;
    }


    if (usernameInput) {

      usernameInput.addEventListener('input', function() {

        if (!usernameError.classList.contains('hidden')) {
          validateUsername();
        }

      });

    }


    if (passwordInput) {

      passwordInput.addEventListener('input', function() {

        if (
          passwordError &&
          !passwordError.classList.contains('hidden')
        ) {
          validatePassword();
        }

      });

    }


    if (signupForm) {

      signupForm.addEventListener('submit', function(event) {

        const usernameValid =
          validateUsername();

        const passwordValid =
          validatePassword();

        if (!usernameValid || !passwordValid) {

          event.preventDefault();

          const firstInvalid = !usernameValid ?
            usernameInput :
            passwordInput;

          firstInvalid.focus();
        }

      });

    }

  });
</script>

<?php require_once '../includes/footer.php'; ?>