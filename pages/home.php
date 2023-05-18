<?php

$slug = $url[0] ?? null;
$posts = [];
$cards = [];

if ($slug) {
    fetchPosts();
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
    fetchPosts();
    $tags = Helper::getAllUniqueTags($posts);
    $tagList = Helper::buildTagList($tags);
    $data = [
        'PAGE_TITLE' => 'Designly Blog',
        'POSTS' => implode("\n", $cards),
        'description' => 'Designly Web Developer Blog - articles & tutorials about full-stack development, design, and technology',
        'tagList' => $tagList,
    ];
    try {
        $page = new Page(COMP_DIR . '/home.html', $data);
        $page->render();
    } catch (Exception $e) {
        echo $e->getMessage();
        exit;
    }
}

function fetchPosts()
{
    global $posts, $cards;
    $url = BLOG_BASE_URL;
    $posts = json_decode(Helper::getUtf8($url), true);
    if (empty($posts)) {
        Helper::render404();
        exit;
    }

    $cards = Helper::buildPostCards($posts, 9);
    return $cards;
}
