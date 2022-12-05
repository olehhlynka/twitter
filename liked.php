<?php 
include_once "includes/functions.php";

if (!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
    header("Location: " . get_url());
}

$posts = get_liked_posts();
$title = "Понравившися твиты";
$error = get_error_message();

include_once "includes/header.php";
include_once "includes/posts.php";
include_once "includes/footer.php"; 

