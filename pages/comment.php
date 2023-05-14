<?php
$method = $_SERVER['REQUEST_METHOD'] ?? null;

switch ($method) {
    case 'GET':
        $cookie = $_COOKIE['COMMENT_INFO'] ?? null;
        $data = [
            'displayName' => '',
            'email' => '',
        ];
        if ($cookie) {
            $data = json_decode($cookie, true);
        }
        echo Helper::buildTemplate(
            COMP_DIR . '/writeComment.html',
            $data
        );
        break;
    case 'POST':
        setcookie('COMMENT_INFO', json_encode($_POST),  time() + (86400 * 365));
        break;
    default:
        http_response_code(405);
        echo 'Method not allowed';
}
