<?php 
include_once "includes/functions.php";

$error = get_error_message();

$id = 0;
if (isset($_SESSION['user']['id'])) {
    $id = $_SESSION['user']['id'];
} else if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
} else {
    header("Location: " . get_url());
}

$posts = get_posts($id);
$user_info = get_user_info_by_id($id);

if($id!=0) {
    $title = "Твиты пользователя @" . $user_info['login'];
} else {
    $title = "Твиты";
}

include_once "includes/header.php";
if (logged_in()) include_once "includes/tweet_form.php";
include_once "includes/posts.php";
include_once "includes/footer.php"; 

