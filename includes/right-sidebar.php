<?php

$basePath = $basePath ?? '';

?>

<!-- Right Sidebar -->
<aside class="hidden lg:col-span-3 lg:block">

  <div class="sticky top-24 space-y-4">

    <!-- Suggestions Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

      <div class="mb-4 flex items-center justify-between">

        <h3 class="font-semibold text-gray-900">
          People to discover
        </h3>

        <span class="text-xs text-gray-400">
          Suggested
        </span>

      </div>


      <div class="space-y-4">

        <div class="flex items-center gap-3">

          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600"
          >
            A
          </div>

          <div class="min-w-0 flex-1">

            <p class="truncate text-sm font-semibold text-gray-900">
              Alex Martin
            </p>

            <p class="truncate text-xs text-gray-500">
              Software Developer
            </p>

          </div>

          <button
            type="button"
            class="text-sm font-medium text-blue-600 hover:text-blue-700"
          >
            View
          </button>

        </div>


        <div class="flex items-center gap-3">

          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-purple-100 text-sm font-semibold text-purple-600"
          >
            S
          </div>

          <div class="min-w-0 flex-1">

            <p class="truncate text-sm font-semibold text-gray-900">
              Sarah Khan
            </p>

            <p class="truncate text-xs text-gray-500">
              UI/UX Designer
            </p>

          </div>

          <button
            type="button"
            class="text-sm font-medium text-blue-600 hover:text-blue-700"
          >
            View
          </button>

        </div>

      </div>

    </div>


    <!-- About Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

      <h3 class="mb-3 font-semibold text-gray-900">
        About
      </h3>

      <p class="text-sm leading-6 text-gray-500">
        Connect, share posts, interact with comments and discover other users.
      </p>

    </div>


    <!-- Footer Links -->
    <div class="px-2 text-xs leading-6 text-gray-400">

      <div class="flex flex-wrap gap-x-3">

        <a
          href="#"
          class="hover:text-gray-600"
        >
          Privacy
        </a>

        <a
          href="#"
          class="hover:text-gray-600"
        >
          Terms
        </a>

        <a
          href="#"
          class="hover:text-gray-600"
        >
          Help
        </a>

      </div>

      <p class="mt-2">
        © <?= date('Y') ?> Social App
      </p>

    </div>

  </div>

</aside>