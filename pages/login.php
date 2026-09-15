<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';

$error = '';
$success = '';

if (isset($_GET['registered']) && $_GET['registered'] === '1') {
  $success = 'Account created successfully. Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  require_once '../includes/csrf.php';

  if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $error = 'CSRF token validation failed.';
  } else {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {

      $error = 'Username and password are required.';
    } else {

      $stmt = $pdo->prepare(
        "SELECT id, username, password
             FROM users
             WHERE username = ?"
      );

      $stmt->execute([$username]);

      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$user) {

        $error = 'Invalid username or password.';
      } elseif (!password_verify($password, $user['password'])) {

        $error = 'Invalid username or password.';
      } else {

        // Prevent session fixation
        session_regenerate_id(true);

        // Store logged-in user's information
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Redirect to homepage
        header('Location: ../index.php');
        exit;
      }
    }
  }
}

$pageTitle = 'Login';
$basePath = '../';

require_once '../includes/header.php';

?>

<main class="min-h-screen bg-gray-50 px-4 py-10">

  <div class="mx-auto flex min-h-[calc(100vh-120px)] max-w-6xl items-center justify-center">

    <div class="grid w-full overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm lg:grid-cols-2">

      <!-- Left Branding Panel -->
      <div class="hidden bg-gradient-to-br from-blue-600 to-indigo-600 p-10 text-white lg:flex lg:flex-col lg:justify-between">

        <div>

          <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-xl font-bold backdrop-blur">
            S
          </div>

          <h2 class="mt-8 text-4xl font-bold leading-tight">
            Welcome back.
          </h2>

          <p class="mt-4 max-w-md text-sm leading-6 text-blue-100">
            Sign in to continue sharing posts, connecting with users, and exploring your feed.
          </p>

        </div>

        <p class="text-sm text-blue-100">
          Your community, your conversations.
        </p>

      </div>


      <!-- Login Side -->
      <div class="p-6 sm:p-10 lg:p-12">

        <div class="mx-auto max-w-md">

          <!-- Heading -->
          <div class="mb-8">

            <h1 class="text-3xl font-bold text-gray-900">
              Sign in
            </h1>

            <p class="mt-2 text-sm text-gray-500">
              Enter your account details to continue.
            </p>

          </div>


          <!-- Success Message -->
          <?php if (!empty($success)): ?>

            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
              <?= htmlspecialchars($success) ?>
            </div>

          <?php endif; ?>


          <!-- Error Message -->
          <?php if (!empty($error)): ?>

            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
              <?= htmlspecialchars($error) ?>
            </div>

          <?php endif; ?>


          <!-- Login Form -->
          <form
            method="POST"
            id="login-form"
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
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                placeholder="Enter your username"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

              <p
                id="login-username-error"
                class="mt-2 hidden text-xs font-medium text-red-500"></p>

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
                  autocomplete="current-password"
                  placeholder="Enter your password"
                  class="w-full rounded-xl border border-gray-300 px-4 py-3 pr-14 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                <button
                  type="button"
                  id="toggle-password"
                  class="absolute inset-y-0 right-0 flex items-center px-4 text-sm font-medium text-gray-500 transition hover:text-gray-800">
                  Show
                </button>

              </div>

              <p
                id="login-password-error"
                class="mt-2 hidden text-xs font-medium text-red-500"></p>


            </div>

            <!-- Submit -->
            <button
              type="submit"
              class="submit-loading-btn w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
              data-loading-text="Signing in...">
              Sign in
            </button>

          </form>


          <!-- Signup Link -->
          <p class="mt-7 text-center text-sm text-gray-500">

            Don't have an account?

            <a
              href="signup.php"
              class="font-semibold text-blue-600 transition hover:text-blue-700">
              Create one
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

    const loginForm =
      document.getElementById('login-form');

    const usernameInput =
      document.getElementById('username');

    const usernameError =
      document.getElementById('login-username-error');

    const passwordError =
      document.getElementById('login-password-error');


    function showLoginError(input, errorElement, message) {

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


    function clearLoginError(input, errorElement) {

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


    function validateLoginUsername() {

      if (!usernameInput || !usernameError) {
        return true;
      }

      if (usernameInput.value.trim() === '') {

        showLoginError(
          usernameInput,
          usernameError,
          'Username is required.'
        );

        return false;
      }

      clearLoginError(
        usernameInput,
        usernameError
      );

      return true;
    }


    function validateLoginPassword() {

      if (!passwordInput || !passwordError) {
        return true;
      }

      if (passwordInput.value === '') {

        showLoginError(
          passwordInput,
          passwordError,
          'Password is required.'
        );

        return false;
      }

      clearLoginError(
        passwordInput,
        passwordError
      );

      return true;
    }


    if (usernameInput) {

      usernameInput.addEventListener('input', function() {

        if (
          usernameError &&
          !usernameError.classList.contains('hidden')
        ) {
          validateLoginUsername();
        }

      });

    }


    if (passwordInput) {

      passwordInput.addEventListener('input', function() {

        if (
          passwordError &&
          !passwordError.classList.contains('hidden')
        ) {
          validateLoginPassword();
        }

      });

    }


    if (loginForm) {

      loginForm.addEventListener('submit', function(event) {

        const usernameValid =
          validateLoginUsername();

        const passwordValid =
          validateLoginPassword();

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