<?php

require_once 'includes/auth.php';
requireLogin('pages/login.php');

require_once 'config/database.php';
require_once 'includes/csrf.php';
require_once 'includes/post-data.php';
require_once 'includes/user-data.php';
require_once 'includes/media.php';

$limit = 5;

$page = filter_input(
  INPUT_GET,
  'page',
  FILTER_VALIDATE_INT
);

if (!$page || $page < 1) {
  $page = 1;
}

$offset = ($page - 1) * $limit;

// Get all visible posts with their author's information
$stmt = $pdo->prepare(
  "SELECT
      posts.id,
      posts.user_id,
      posts.content,
      posts.created_at,
      users.username,
      users.profile_picture,

      (
          SELECT COUNT(*)
          FROM post_likes
          WHERE post_likes.post_id = posts.id
      ) AS like_count,

      (
          SELECT COUNT(*)
          FROM comments
          WHERE comments.post_id = posts.id
          AND comments.parent_id IS NULL
      ) AS comment_count

   FROM posts

   INNER JOIN users
      ON posts.user_id = users.id

   WHERE posts.is_hidden = FALSE

   ORDER BY posts.id DESC

   LIMIT :limit
   OFFSET :offset"
);

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as &$post) {
  $post = preparePostData(
    $pdo,
    $post,
    (int)$_SESSION['user_id']
  );
}

unset($post);

$currentUser = getUserById(
  $pdo,
  (int) $_SESSION['user_id']
);

$pageTitle = 'Home';
$basePath = '';

$currentUserAvatar = mediaUrl(
  $currentUser['profile_picture'] ?? null,
  $basePath
);

require_once 'includes/header.php';
require_once 'includes/navbar.php';

?>

<main class="min-h-screen bg-gray-50">

  <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

      <!-- Left Sidebar -->
      <?php require_once 'includes/left-sidebar.php'; ?>

      <!-- Main Feed -->
      <section class="lg:col-span-6">
        <div class="mb-5 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">

          <div class="flex items-center gap-3">

            <?php if ($currentUserAvatar): ?>

              <img
                src="<?= htmlspecialchars($currentUserAvatar) ?>"
                alt="<?= htmlspecialchars($currentUser['username']) ?>"
                class="h-11 w-11 shrink-0 rounded-full object-cover">

            <?php else: ?>

              <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gray-200 font-semibold text-gray-700">
                <?= htmlspecialchars(
                  strtoupper(substr($currentUser['username'] ?? 'U', 0, 1))
                ) ?>
              </div>

            <?php endif; ?>

            <a
              href="pages/create-post.php"
              class="flex-1 rounded-full border border-gray-300 bg-gray-50 px-4 py-3 text-left text-sm text-gray-500 transition hover:bg-gray-100">
              What's on your mind?
            </a>

          </div>

          <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">

            <a
              href="pages/create-post.php"
              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-blue-600">
              <span>🖼️</span>
              <span>Photo</span>
            </a>

            <a
              href="pages/create-post.php"
              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-blue-600">
              <span>✏️</span>
              <span>Post</span>
            </a>

          </div>

        </div>

        <div id="posts-feed" class="space-y-5">

          <!-- Success Message -->
          <?php if (
            isset($_GET['post_created']) &&
            $_GET['post_created'] === '1'
          ): ?>

            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
              Post created successfully!
            </div>

          <?php endif; ?>


          <!-- No Posts -->
          <?php if (empty($posts)): ?>

            <div class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">

              <h2 class="text-lg font-semibold text-gray-800">
                No posts yet
              </h2>

              <p class="mt-2 text-sm text-gray-500">
                Be the first person to share something with the community.
              </p>

              <a
                href="pages/create-post.php"
                class="mt-5 inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Create Your First Post
              </a>

            </div>

          <?php else: ?>


            <!-- Posts -->
            <div id="feed-posts" class="space-y-5">

              <?php foreach ($posts as $post): ?>

                <?php require 'includes/post-card.php'; ?>

              <?php endforeach; ?>

            </div>

            <div
              id="infinite-scroll-trigger"
              class="flex justify-center py-8">
              <span
                id="feed-loading"
                class="hidden text-sm text-gray-500">
                Loading more posts...
              </span>
            </div>

          <?php endif; ?>

        </div>

        <!-- Keep your existing feed scroll trigger -->

      </section>

      <!-- Right Sidebar -->
      <?php require_once 'includes/right-sidebar.php'; ?>
    </div>

  </div>

</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Handle loading state for submit buttons
    document.addEventListener('submit', function(event) {

      const form = event.target;
      const button = form.querySelector('.submit-loading-btn');

      if (!button) {
        return;
      }

      button.disabled = true;
      button.dataset.originalText = button.textContent;
      button.textContent =
        button.dataset.loadingText || 'Loading...';
    });

    // handle post like form submissions
    document.addEventListener('submit', async function(event) {

      const form = event.target;

      if (!form.classList.contains('like-form')) {
        return;
      }

      event.preventDefault();

      const button = form.querySelector('[data-like-button]');
      const post = form.closest('[data-post-id]');

      if (!button || !post) {
        return;
      }

      button.disabled = true;

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form)
        });

        if (!response.ok) {
          throw new Error('Request failed');
        }

        const data = await response.json();

        if (!data.success) {
          showToast(
            data.message || 'Unable to update like.',
            'error'
          );
          return;
        }

        const icon = button.querySelector('.like-icon');
        const label = button.querySelector('.like-label');
        const count = post.querySelector('.post-like-count');

        button.classList.toggle('bg-red-50', data.liked);
        button.classList.toggle('text-red-600', data.liked);
        button.classList.toggle('hover:bg-red-100', data.liked);
        button.classList.toggle('text-gray-600', !data.liked);
        button.classList.toggle('hover:bg-gray-100', !data.liked);

        if (icon) {
          icon.textContent = data.liked ? '❤️' : '♡';
        }

        if (label) {
          label.textContent = data.liked ? 'Liked' : 'Like';
        }

        if (count) {
          count.innerHTML = `${data.like_count} ${data.like_count === 1 ? 'like' : 'likes'}`;
        }
      } catch (error) {
        console.error(error);

        showToast(
          'Something went wrong. Please try again.',
          'error'
        );
      } finally {
        button.disabled = false;
      }
    });

    // handle comment like form submissions
    document.addEventListener('submit', async function(event) {

      const form = event.target;

      if (!form.classList.contains('comment-like-form')) {
        return;
      }

      event.preventDefault();

      const commentLikeButton = form.querySelector(
        '[data-comment-like-button]'
      );

      const comment = form.closest('[data-comment-id]');

      if (!commentLikeButton || !comment) {
        return;
      }

      commentLikeButton.disabled = true;

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form)
        });

        if (!response.ok) {
          throw new Error('Request failed');
        }

        const data = await response.json();

        if (!data.success) {
          showToast(
            data.message || 'Unable to update comment like.',
            'error'
          );
          return;
        }

        const label = commentLikeButton.querySelector(
          '.comment-like-label'
        );
        const count = comment.querySelector('.comment-like-count');

        commentLikeButton.classList.toggle(
          'text-red-600',
          data.liked
        );
        commentLikeButton.classList.toggle(
          'text-gray-500',
          !data.liked
        );
        commentLikeButton.classList.toggle(
          'hover:text-red-600',
          !data.liked
        );

        if (label) {
          label.textContent = data.liked ? '❤️ Liked' : '♡ Like';
        }

        if (count) {
          count.textContent = `${data.like_count} ${data.like_count === 1 ? 'like' : 'likes'}`;
        }
      } catch (error) {
        console.error(error);

        showToast(
          'Something went wrong. Please try again.',
          'error'
        );
      } finally {
        commentLikeButton.disabled = false;
      }
    });

    const feed = document.getElementById('feed-posts');
    const trigger = document.getElementById('infinite-scroll-trigger');
    const loading = document.getElementById('feed-loading');

    let isLoading = false;
    let hasMore = true;

    // Infinite scrolling observer for posts
    const observer = new IntersectionObserver(
      async (entries) => {

        const entry = entries[0];

        if (!entry.isIntersecting || isLoading || !hasMore) {
          return;
        }

        isLoading = true;
        loading.classList.remove('hidden');

        const postElements = feed.querySelectorAll('[data-post-id]');

        if (postElements.length === 0) {
          isLoading = false;
          loading.classList.add('hidden');
          return;
        }

        const lastPost = postElements[postElements.length - 1];
        const lastId = lastPost.dataset.postId;

        try {

          const response = await fetch(
            `api/posts/load-posts.php?last_id=${encodeURIComponent(lastId)}`
          );

          if (!response.ok) {
            throw new Error('Failed to load posts');
          }

          const data = await response.json();

          if (!data.success) {
            showToast(
              data.message || 'Unable to load more posts.',
              'error'
            );
            return;
          }

          if (data.html) {

            feed.insertAdjacentHTML(
              'beforeend',
              data.html
            );

            observeCommentTriggers();

          }

          hasMore = data.has_more;

          if (!hasMore) {
            observer.disconnect();

            trigger.innerHTML = `
                        <p class="text-sm text-gray-400">
                            You've reached the end.
                        </p>
                    `;
          }

        } catch (error) {

          console.error(error);

          showToast(
            'Unable to load more posts. Please try again.',
            'error'
          );

        } finally {

          isLoading = false;

          if (hasMore) {
            loading.classList.add('hidden');
          }

        }

      }, {
        root: null,
        rootMargin: '200px',
        threshold: 0
      }
    );

    observer.observe(trigger);

    // comment infinite scrolling observer
    const commentsObserver = new IntersectionObserver(
      async (entries) => {

        for (const entry of entries) {

          if (!entry.isIntersecting) {
            continue;
          }

          const trigger = entry.target;

          if (trigger.dataset.loading === 'true') {
            continue;
          }

          const postId = trigger.dataset.postId;

          const postCard =
            trigger.closest('article[data-post-id]');

          if (!postCard) {
            continue;
          }

          const commentsContainer =
            postCard.querySelector('.comments-container');

          if (!commentsContainer) {
            continue;
          }

          const comments =
            commentsContainer.querySelectorAll(
              '[data-comment-id]'
            );

          if (comments.length === 0) {
            continue;
          }

          const lastComment =
            comments[comments.length - 1];

          const lastCommentId =
            lastComment.dataset.commentId;

          trigger.dataset.loading = 'true';

          try {

            const response = await fetch(
              `api/posts/load-comments.php?post_id=${postId}&last_comment_id=${lastCommentId}`
            );

            if (!response.ok) {
              throw new Error(
                'Failed to load comments'
              );
            }

            const data = await response.json();

            if (!data.success) {
              showToast(
                data.message ||
                'Unable to load comments.',
                'error'
              );

              continue;
            }

            if (data.html) {
              commentsContainer.insertAdjacentHTML(
                'beforeend',
                data.html
              );
            }

            if (!data.has_more) {

              commentsObserver.unobserve(trigger);

              trigger.remove();
            }

          } catch (error) {

            console.error(error);

            showToast(
              'Unable to load more comments. Please try again.',
              'error'
            );

          } finally {

            if (trigger.isConnected) {
              trigger.dataset.loading = 'false';
            }
          }
        }
      }, {
        rootMargin: '150px'
      }
    );

    // Observe comment triggers for infinite scrolling
    function observeCommentTriggers() {

      document
        .querySelectorAll('.comments-scroll-trigger')
        .forEach(trigger => {

          if (trigger.dataset.observed === 'true') {
            return;
          }

          trigger.dataset.observed = 'true';

          commentsObserver.observe(trigger);
        });
    }

    observeCommentTriggers();


  });

  // Handle post menu clicks
  document.addEventListener('click', function(event) {

    const menuButton = event.target.closest('.post-menu-btn');

    if (menuButton) {

      const wrapper = menuButton.parentElement;

      const menu = wrapper.querySelector('.post-menu');

      document.querySelectorAll('.post-menu').forEach(item => {
        if (item !== menu) {
          item.classList.add('hidden');
        }
      });

      menu.classList.toggle('hidden');

      return;
    }

    if (!event.target.closest('.post-menu')) {
      document.querySelectorAll('.post-menu').forEach(menu => {
        menu.classList.add('hidden');
      });
    }

    // Focus comment input when Comment button is clicked
    document.addEventListener('click', function(event) {

      // ---------------------------------
      // Comments count clicked
      // ---------------------------------
      const toggleButton = event.target.closest('.comments-toggle-btn');

      if (toggleButton) {

        const postCard =
          toggleButton.closest('article[data-post-id]');

        if (!postCard) {
          return;
        }

        const commentsSection =
          postCard.querySelector('.post-comments-section');

        if (!commentsSection) {
          return;
        }

        commentsSection.classList.toggle('hidden');

        return;
      }


      // ---------------------------------
      // Comment action button clicked
      // ---------------------------------
      const commentButton =
        event.target.closest('.comment-focus-btn');

      if (commentButton) {

        const postCard =
          commentButton.closest('article[data-post-id]');

        if (!postCard) {
          return;
        }

        const commentForm =
          postCard.querySelector('.add-comment-form');

        if (!commentForm) {
          return;
        }

        const isHidden =
          commentForm.classList.contains('hidden');

        if (isHidden) {

          // Show form
          commentForm.classList.remove('hidden');

          // Focus input
          const commentInput =
            commentForm.querySelector('input[name="content"]');

          if (commentInput) {
            commentInput.focus();
          }

        } else {

          // Hide form
          commentForm.classList.add('hidden');

        }
      }

    });
  });
</script>

<?php require 'includes/footer.php'; ?>