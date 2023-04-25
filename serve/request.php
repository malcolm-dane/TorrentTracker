<?php

require_once '../site.php';
$db->connect();

if (array_key_exists('user', $_SESSION)) {
    header(sprintf('Location: %s/', $CONFIG['base_url']));
    die;
}

$errors = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $data = array();
    $keys = array('email');
    foreach ($keys as $key) {
        if (!array_key_exists($key, $_POST)) {
            die;
        }
        $data[$key] = $_POST[$key];
    }


    if (strpos($data['email'], '@') === false) $errors []= 'invalid email';
    if (strlen($data['email']) > 255) $errors []= 'email too long';

    if (empty($errors)) {
        $res = $db->query_params('SELECT email FROM reqinv WHERE email = :email ', array('email' => $data['email']));
        if (!($email= $res->fetch())) {
            $errors []= 'Added for Review';
                    $db->query_params('INSERT INTO reqinv (email) VALUES (:email)', array('email' => $data['email'])) or die('db error');

        }
    }

    if (empty($errors)) {
        $res = $db->query_params('SELECT 1 FROM reqinv WHERE email = :email', array( 'email' => $data['email'])) or die('db error');
        if ($res->fetch()) {
            $errors []= 'username or email already taken';
setcookie(session_name(), session_id(), 1); // to expire the session
$_SESSION = [];        }
    }

    if (empty($errors)) {
        $db->query_params('INSERT INTO reqinv (email) VALUES (:email)', array('email' => $data['email'])) or die('db error');

        header(sprintf('Location: %s/request.php?success', $CONFIG['base_url']));
        die;
                header(sprintf('Location: %s/index.php', $CONFIG['base_url']));

    }
}


site_header();


printf('<form style="width:fit-content;height:fit-content;" class="login" method="POST" action="request.php%s">', array_key_exists('email', $_GET) ? '?email=' . html_escape(urlencode($_GET['email'])) : '');
csrf_html();

printf('<section class="loginbox">');
printf('<h1>Request Invite Code. Requires email used on chainSocial</h1>');

if (!empty($errors)) {
    foreach ($errors as $error) {
        printf('<div class="bad notification">%s</div>', html_escape($error));
    }
}


printf('<input class="text" name="email" type="text" placeholder="Email address">');

printf('<input class="submit" type="submit" value="Request">');

printf('</form>');

printf('</section>');
site_footer();

