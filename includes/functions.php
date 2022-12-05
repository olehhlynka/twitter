<?php

include_once "config.php";

function debug($val, $stop = false)
{
    echo '<pre>';
    print_r($val);
    echo '</pre>';
    if ($stop) die;
}

function get_url($page = '')
{
    return HOST . "/$page";
}

function get_page_title($title = '')
{
    if (!empty($title)) {
        return SITE_NAME . " - $title";
    } else {
        return SITE_NAME;
    }
}

function db()
{
    try {
        return new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8;port=3307;",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    } catch (PDOException $e) {
        die($e->getMessage());
    }
}

function db_query($sql, $exec = false)
{
    if (empty($sql)) return false;

    if ($exec) return db()->exec($sql);

    return db()->query($sql);
}

function get_posts($user_id = 0, $sort = false)
{
    $sorting = 'DESC';
    if($sort) {
        $sorting = 'ASC';
    }

    if ($user_id > 0) {
        return db_query("SELECT posts.*, users.login, users.name, users.avatar FROM posts join users on 
        posts.user_id=users.id WHERE posts.user_id=$user_id order by posts.date $sorting;")->fetchAll();
    }

    return db_query("SELECT posts.*, users.login, users.name, users.avatar FROM posts join users on 
    posts.user_id=users.id order by posts.date $sorting;")->fetchAll();
}

function get_user_info_by_id($user_id) {
    if ($user_id > 0) {
        return db_query("SELECT * FROM `users` WHERE id = '$user_id';")->fetch();
    } 
    return false;
}

function get_user_info($login)
{
    return db_query("SELECT * FROM `users` WHERE login = '$login';")->fetch();
}

function add_user($login, $pass)
{
    $login = trim($login);
    $name = ucfirst($login);
    $password = password_hash($pass, PASSWORD_DEFAULT);

    return db_query("INSERT INTO users (id, login, pass, name) VALUES (NULL, '$login', '$password', '$name');", true);
}

function register_user($auth_data)
{
    if (
        empty($auth_data) || !isset($auth_data['login']) || empty($auth_data['login'])
        || !isset($auth_data['pass']) || empty($auth_data['pass'])
        || !isset($auth_data['pass2']) || empty($auth_data['pass2'])
    ) return false;

    $user = get_user_info($auth_data['login']);

    if (!empty($user)) {
        $_SESSION['error'] = 'Пользователь ' . $auth_data['login'] . ' уже существует!';
        header("Location: " . get_url('register.php'));
        die;
    }

    if ($auth_data['pass'] !== $auth_data['pass2']) {
        $_SESSION['error'] = 'Пароли не совпадают!';
        header("Location: " . get_url('register.php'));
        die;
    }

    if (add_user($auth_data['login'], $auth_data['pass'])) {
        header("Location: " . get_url());
        die;
    }
}

function login($auth_data)
{

    if (
        empty($auth_data) || !isset($auth_data['login']) || empty($auth_data['login'])
        || !isset($auth_data['pass']) || empty($auth_data['pass'])
    ) return false;

    $user = get_user_info($auth_data['login']);

    if (empty($user)) {
        $_SESSION['error'] = 'Пользователь не существует!';
        header("Location: " . get_url());
        die;
    }

    if (password_verify($auth_data['pass'], $user['pass'])) {
        $_SESSION['user'] = $user;
        $_SESSION['error'] = '';
        header("Location: " . get_url('user_posts.php'));
        die;
    } else {
        $_SESSION['error'] = 'Пароль неверный!';
        header("Location: " . get_url());
        die;
    }
}

function get_error_message()
{
    $error = '';

    if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
        $error = $_SESSION['error'];
        $_SESSION['error'] = '';
    }

    return $error;
}

function redirect($location = HOST) {
    header("Location: " . $location);
}

function logged_in() {
    return (isset($_SESSION['user']) && !empty($_SESSION['user']));
}

function add_post($text, $image) {
    $text = trim($text);
    if(mb_strlen($text) > 255) {
        $text = mb_substr($text, 0, 250) . ' ...';
    }

    $user_id = $_SESSION['user']['id'];
    return db_query("INSERT INTO `posts` (`id`, `user_id`, `text`, `image`) 
    VALUES (NULL, $user_id, '$text', '$image');", true);
}

function delete_post($id) {
    $user_id = $_SESSION['user']['id'];
    return db_query("DELETE FROM `posts` WHERE `id` = $id and `user_id` = $user_id;", true);
}

function get_likes_count($post_id) {
    if(empty($post_id)) return 0;

    return db_query("select count(*) from likes where post_id = $post_id;")->fetchColumn();
}

function is_post_liked($post_id) {
    $user_id = $_SESSION['user']['id'];

    if (empty($post_id)) return 0;
    return db_query("select * from likes where post_id = $post_id and user_id = $user_id;")->rowCount() > 0;
}

function get_like($post_id) {
    $user_id = $_SESSION['user']['id'];

    return db_query("INSERT INTO `likes` (`user_id`, `post_id`) VALUES ($user_id, '$post_id');", true);
}

function delete_like($post_id)
{
    $user_id = $_SESSION['user']['id'];
    if (empty($post_id)) return 0;

    return db_query("DELETE FROM `likes` WHERE `post_id` = $post_id and `user_id` = $user_id;", true);
}

function get_liked_posts() {
    $user_id = $_SESSION['user']['id'];
    if (empty($user_id)) return 0;

    return db_query("SELECT posts.*, users.login, users.name, users.avatar FROM likes join posts on 
        posts.id=likes.post_id join users on users.id=posts.user_id WHERE likes.user_id=$user_id order by date desc;")->fetchAll();
}