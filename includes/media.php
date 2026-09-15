<?php

function mediaUrl(?string $path, string $basePath = ''): ?string
{
  if (empty($path)) {
    return null;
  }

  // External URL
  if (
    str_starts_with($path, 'http://') ||
    str_starts_with($path, 'https://')
  ) {
    return $path;
  }

  // Local stored file
  return $basePath . ltrim($path, '/');
}
