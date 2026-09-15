<footer>

  <p>
    &copy; <?= date('Y') ?> Full Stack Task
  </p>

</footer>

<script>
  window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
      window.location.reload();
    }
  });
</script>

<div
  id="toast"
  style="
    display: none;
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 99999;
    padding: 12px 18px;
    border-radius: 8px;
    color: white;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  "></div>

<script>
  function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');

    if (!toast) {
      console.error('Toast element not found');
      return;
    }

    toast.textContent = message;

    if (type === 'success') {
      toast.style.backgroundColor = 'green';
    } else {
      toast.style.backgroundColor = 'red';
    }

    toast.style.display = 'block';

    setTimeout(() => {
      toast.style.display = 'none';
    }, 3000);
  }

  document.addEventListener('DOMContentLoaded', function () {

    const profileButton = document.getElementById('profile-menu-button');
    const profileMenu = document.getElementById('profile-menu');

    const mobileButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');


    // Profile dropdown
    if (profileButton && profileMenu) {

        profileButton.addEventListener('click', function (event) {

            event.stopPropagation();

            profileMenu.classList.toggle('hidden');

        });

    }


    // Mobile menu
    if (mobileButton && mobileMenu) {

        mobileButton.addEventListener('click', function () {

            mobileMenu.classList.toggle('hidden');

        });

    }


    // Close profile menu when clicking outside
    document.addEventListener('click', function (event) {

        if (
            profileMenu &&
            profileButton &&
            !profileMenu.contains(event.target) &&
            !profileButton.contains(event.target)
        ) {
            profileMenu.classList.add('hidden');
        }

    });

});
</script>

<?php if (!empty($_SESSION['toast_message'])): ?>

  <script>
    showToast(
      <?= json_encode($_SESSION['toast_message']) ?>,
      <?= json_encode($_SESSION['toast_type'] ?? 'success') ?>
    );
  </script>

  <?php
  unset($_SESSION['toast_message']);
  unset($_SESSION['toast_type']);
  ?>

<?php endif; ?>

</body>

</html>