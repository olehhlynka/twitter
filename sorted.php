<?php 
include_once "includes/functions.php";

$posts = get_posts(0, true);
$title = "Главная страница";
$error = get_error_message();

include_once "includes/header.php";
if (logged_in()) include_once "includes/tweet_form.php";
include_once "includes/posts.php";
include_once "includes/footer.php"; 

