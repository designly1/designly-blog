<?php
$tag = $url[1] ??  null;
if (!$tag) {
    Helper::render404();
}

$posts = Helper::getPostsByTagSlug($tag);
$cards = Helper::buildPostCards($posts);

$data = [
    'PAGE_TITLE' => '#' . $tag,
    'CARDS' => implode("\n", $cards),
    'description' => 'All posts for tag: ' . $tag,
];

try {
    $page = new Page(COMP_DIR . '/blogList.html', $data);
    $page->render();
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
