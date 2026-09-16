<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;

function getCloudinary(): Cloudinary
{
  $cloudName = getenv('CLOUDINARY_CLOUD_NAME');
  $apiKey = getenv('CLOUDINARY_API_KEY');
  $apiSecret = getenv('CLOUDINARY_API_SECRET');

  if (!$cloudName || !$apiKey || !$apiSecret) {
    throw new RuntimeException(
      'Cloudinary configuration is missing.'
    );
  }

  return new Cloudinary([
    'cloud' => [
      'cloud_name' => $cloudName,
      'api_key' => $apiKey,
      'api_secret' => $apiSecret,
    ],
    'url' => [
      'secure' => true,
    ],
  ]);
}


/**
 * Upload an image to Cloudinary.
 *
 * Returns the permanent HTTPS URL.
 */
function uploadImageToCloudinary(
  string $temporaryFile,
  string $folder
): string {

  $cloudinary = getCloudinary();

  $result = $cloudinary->uploadApi()->upload(
    $temporaryFile,
    [
      'folder' => $folder,
      'resource_type' => 'image',
    ]
  );

  $url = $result['secure_url'] ?? null;

  if (!$url) {
    throw new RuntimeException(
      'Cloudinary did not return an image URL.'
    );
  }

  return $url;
}
