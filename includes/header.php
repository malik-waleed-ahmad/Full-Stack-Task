<?php

$pageTitle = $pageTitle ?? 'Full Stack Task';

?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">

  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

  <title>
    <?= htmlspecialchars($pageTitle) ?>
  </title>

  <link
    rel="stylesheet"
    href="<?= $basePath ?? '' ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">

</head>

<body>