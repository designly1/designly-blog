<?php

$slug = $url[0] ?? null;
if ($slug) {
    $cards = getCards();
    $url = BLOG_BASE_URL . '/' . $slug;
    $post = json_decode(Helper::getUtf8($url), true);
    if (empty($post)) {
        Helper::render404();
    }
    $post = $post[0];

    $data = Helper::buildPostData($post);
    $data['POSTS'] = implode("\n", $cards);
    $page = new Page(COMP_DIR . '/post.html', $data);
    $page->render();
} else {
    $cards = getCards();
    $data = [
        'PAGE_TITLE' => 'Designly Blog',
        'POSTS' => implode("\n", $cards),
        'description' => 'Designly Web Developer Blog - articles & tutorials about full-stack development, design, and technology',
    ];
    try {
        $page = new Page(COMP_DIR . '/home.html', $data);
        $page->render();
    } catch (Exception $e) {
        echo $e->getMessage();
        exit;
    }
}

function getCards()
{
    $url = BLOG_BASE_URL;
    $posts = json_decode(Helper::getUtf8($url), true);
    if (empty($posts)) {
        Helper::render404();
        exit;
    }

    $cards = Helper::buildPostCards($posts, 9);
    return $cards;
}
