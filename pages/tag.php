<?php
$tag = $url[1] ??  null;
if (!$tag) {
    http_response_code(404);
    echo 'Not Found';
    exit;
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
