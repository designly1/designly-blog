<?php
$index = intval($_GET['index'] ?? 0);

$posts = json_decode(file_get_contents(DATA_DIR . '/postMeta.json'), true);
$posts = array_slice($posts, $index, 3);

$postsCards = [];
$i = 0;
foreach ($posts as $post) {
    $data = Helper::buildPostData($post);
    // Generate post item cards
    $card = Page::parseTemplate(COMP_DIR . '/postItem.html');
    $card = Page::replaceKeys($card, $data);
    $card = Page::cleanUpTags($card);
    $postCards[] = $card;
    if (($i + 1) % 2 === 0) {
        $ad = Helper::buildTemplate(COMP_DIR . '/googleAdFeed.html', []);
        $postCards[] = $ad;
    }
    $i++;
}

header('Content-Type: application/json');
echo json_encode($postCards);
