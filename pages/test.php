<?php
$aws = new AwsHelper();

$data = [
    'title' => 'This is a test post',
    'name' => 'Joe Blow',
    'email' => 'jsimons232@yahoo.com',
    'comment' => 'This is my comment',
    'url' => 'https://www.google.com',
];

$aws->sendTemplatedEmail(['jay@designly.biz'], 'commentAdmin', $data);