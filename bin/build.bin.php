<?php
try {
    require_once(__DIR__ . '/config.inc.php');
    require_once(DIR . '/inc/build.inc.php');

    // Fetch posts
    doLog('Fetching posts');
    define('URL', BLOG_BASE_URL . '?content=true');
    $json = json_decode(Helper::getUtf8(URL),  true);
    if (!$json || empty($json)) {
        throw new Exception('Failed to fetch posts');
    }

    doLog('Removing old files');
    $rmfiles = PUB_DIR . '/*.html';
    `rm -f $rmfiles`;

    doLog('Building post pages');
    $postCards = Helper::buildPostCards($json, 9);
    $meta = [];

    foreach ($json as $post) {
        $thisUrl = 'https://blog.designly.biz/' . $post['slug'];
        $data = Helper::buildPostData($post);
        $data['POSTS'] = implode("\n", $postCards);
        $page = new Page(COMP_DIR . '/post.html', $data);
        ob_start();
        $page->render();
        $content = ob_get_clean();
        $file = PUB_DIR . '/' . $post['slug'] . '.html';
        $putres = file_put_contents($file, $content);
        doLog("Writing $file: " . ($putres ? "OK" : "FAIL"));

        $meta[] = array_filter($post, function ($key) {
            return $key !== 'content';
        }, ARRAY_FILTER_USE_KEY);
    }

    // Write meta data to json file
    file_put_contents(DATA_DIR . '/postMeta.json', json_encode($meta));
    file_put_contents(DATA_DIR . '/posts.json', json_encode($json));

    // Create index page
    doLog('Building index page');
    // Get tags list
    $tags = Helper::getAllUniqueTags($json);
    $tagList = Helper::buildTagList($tags);
    // Generate page
    $thisUrl = 'https://blog.designly.biz';
    $page = new Page(COMP_DIR . '/home.html', [
        'PAGE_TITLE' => 'Blog',
        'POSTS' => implode("\n", $postCards),
        'tagList' => $tagList,
    ]);
    $page->write(PUB_DIR . '/index.html');

    // Generate sitemap
    $sitemap = new DOMDocument('1.0', 'UTF-8');

    $urlset = $sitemap->createElement('urlset');
    $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $urlset->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
    $sitemap->appendChild($urlset);

    foreach ($json as $post) {
        $thisUrl = 'https://blog.designly.biz/' . $post['slug'];

        $url = $sitemap->createElement('url');

        $loc = $sitemap->createElement('loc');
        $loc->appendChild($sitemap->createTextNode($thisUrl));
        $url->appendChild($loc);

        // Add image
        $image = $sitemap->createElement('image:image');

        $imageUrl = $post['coverImage']; // Replace with your image URL
        $imageLoc = $sitemap->createElement('image:loc');
        $imageLoc->appendChild($sitemap->createTextNode($imageUrl));
        $image->appendChild($imageLoc);

        $url->appendChild($image);

        $urlset->appendChild($url);
    }

    // Save the sitemap to a file
    $sitemap->formatOutput = true;
    $sitemap->save(PUB_DIR . '/sitemaps/posts.xml');
} catch (Exception $e) {
    doLog($e->getMessage());
}
