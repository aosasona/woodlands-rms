<?php

use Woodlands\Core\Auth;

/**
 * @var string $title
 * @var string $description
 **/

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $description ?? "Woodlands RMS" ?>">

    <link rel="stylesheet" href="/public/css/styles.css">

    <script src="/public/js/jquery.min.js"></script>
    <script src="/public/js/index.js" defer></script>
    <script src="/public/js/uikit.min.js" defer></script>
    <script src="/public/js/uikit-icons.min.js" defer></script>

    <title><?= $title ?? "Woodlands RMS" ?></title>
</head>

<body>
<?php
if(Auth::isLoggedIn()) {
    require_once __DIR__ . '/../partials/nav.partial.php';
}
?>
