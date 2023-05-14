<?php

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    file_put_contents('/tmp/crap.txt', "test\n", FILE_APPEND);
    Helper::rateLimit();

    $term = $_POST['term'] ?? null;
    if (!$term) {
        http_response_code(400);
        die('Missing term');
    }

    $results = Helper::vectorSearch($term);
    $slugs = [];
    foreach ($results as $result) {
        $slugs[] = $result['slug'];
    }
    $slugs = array_unique($slugs);
    $slugs = array_slice($slugs, 0, 6);

    $posts = json_decode(file_get_contents(DATA_DIR . '/postMeta.json'), true);
    $postCards = [];
    foreach ($posts as $post) {
        if (in_array($post['slug'], $slugs)) {
            $postCards[] = Helper::buildPostCard($post);
        }
    }

    $pageData = [
        'CARDS' => implode("\n", $postCards)
    ];
    $html = Helper::buildTemplate(COMP_DIR . '/searchResults.html', $pageData);

    echo $html;
} else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    require(COMP_DIR . '/search.html');
} else {
    http_response_code(405);
    die('Method not allowed');
}
