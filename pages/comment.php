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
        $saveData = [
            'displayName' => $_POST['displayName'] ?? '',
            'email' => $_POST['email'] ?? '',
        ];
        setcookie('COMMENT_INFO', json_encode($saveData),  time() + (86400 * 365));

        // Verify token
        $token = $_POST['token'] ?? null;
        if (!$token) {
            http_response_code(400);
            echo 'Missing token';
            exit;
        }
        try {
            $verifyToken = file_get_contents($_ENV['COMMENTS_ENDPOINT'] . '/?verify=' . $token);
            // get response code
            $responseCode = explode(' ', $http_response_header[0])[1];
            if ($responseCode !== '200') {
                http_response_code($responseCode);
                echo 'Invalid token code ' . $verifyToken;
                exit;
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo 'Error verifying token';
            exit;
        }

        $postUrl = $_POST['url'] . '#comments' ?? '';
        $aws = new AwsHelper();

        // Fetch original comment if reply
        $replyTo = $_POST['replyTo'] ?? null;
        if ($replyTo) {
            $comment = json_decode(file_get_contents($_ENV['COMMENTS_ENDPOINT'] . '/?commentId=' . $replyTo), true);
            if ($comment) {
                // Send email to original commenter
                $data = [
                    'name' => $comment['displayName'] ?? '',
                    'replyName' => $_POST['displayName'] ?? '',
                    'title' => $_POST['title'] ?? '',
                    'url' => $postUrl,
                ];
                $aws->sendTemplatedEmail([$comment['email']], 'commentReply', $data);
            }
        }

        // Send email to admin
        $data = [
            'title' => $_POST['title'] ?? '',
            'name' => $_POST['displayName'] ?? '',
            'email' => $_POST['email'] ?? '',
            'comment' => $_POST['comment'] ?? '',
            'url' => $postUrl,
        ];
        $aws->sendTemplatedEmail([ADMIN_EMAIL], 'commentAdmin', $data);

        break;
    default:
        http_response_code(405);
        echo 'Method not allowed';
}
