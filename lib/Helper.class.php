<?php

class Helper
{
    static public function buildPostData($post)
    {
        // get css files
        $css = "";
        foreach (BLOG_CSS_FILES as $file) {
            $css .= "<link rel=\"stylesheet\" href=\"$file\" />\n";
        }
        // Configure image path
        $coverImage = str_replace('cdn.designly.biz', 'cdn.designly.biz/imgr',  $post['coverImage']);
        $coverImageSrcSet = Helper::mkSrcSet($post['coverImage']);

        // Create tags
        $tags = [];
        foreach ($post['tagsCollection']['items'] as $t) {
            $tag = $t['tag'];
            $slug = $t['slug'];
            $tags[] = "<a href=\"/tag/$slug\">#$tag</a>";
        }

        // Configure title
        $title = $post['title'] . ' | Designly';

        $data = [
            ...$post,
            'CSS_FILES' => $css,
            'PAGE_TITLE' => $title,
            'postId' => $post['sys']['id'],
            'coverImageThumb' => $coverImage . '?w=400&q=75',
            'coverImagePost' => $coverImage . '?w=1200&q=75',
            'coverImageSrcSet' => $coverImageSrcSet,
            'tagList' => implode("\n", $tags),
            'ogImage' => $coverImage,
            'ogTitle' => $post['title'],
            'description' => $post['excerpt'],
        ];

        return $data;
    }

    static public function buildTemplate($template, $data)
    {
        $html = Page::parseTemplate($template);
        $html = Page::replaceKeys($html, $data);
        $html = Page::cleanUpTags($html);

        return $html;
    }

    static public function buildPostCard($post)
    {
        $post = self::buildPostData($post);

        return self::buildTemplate(COMP_DIR . '/postItem.html', $post);
    }

    static public function buildPostCards($posts, $numCards = null)
    {
        if (!$numCards) {
            $numCards = count($posts);
        }
        $cards = [];
        for ($i = 0; $i < $numCards; $i++) {
            $cards[] = self::buildPostCard($posts[$i]);
            if (($i + 1) % 3 === 0) {
                $ad = self::buildTemplate(COMP_DIR . '/googleAdFeed.html', []);
                $cards[] = $ad;
            }
        }

        return $cards;
    }

    static public function mkSrcSet($src)
    {
        $src = str_replace('cdn.designly.biz', 'cdn.designly.biz/imgr', $src);
        $srcset = [];
        $sizes = [1024, 640, 320];
        foreach ($sizes as $size) {
            $srcset[] = "$src?w=$size&q=75 " . $size . "w";
        }

        return implode(", ", $srcset);
    }

    static public function getUtf8($fn)
    {
        $content = file_get_contents($fn);

        $content = mb_convert_encoding(
            $content,
            'UTF-8',
            'auto'
        );

        return $content;
    }

    static public function render404()
    {
        http_response_code(404);
        $page = new Page(COMP_DIR . '/404.html', [
            'title' => '404 Not Found',
        ]);
        $page->render();
        exit;
    }

    static public function balanceText($text, $lineLength)
    {
        // split the text into words
        $words = explode(" ", $text);

        // initialize variables
        $currentLine = "";
        $output = "";

        // loop through each word
        foreach ($words as $word) {
            // add the current word to the current line
            $tempLine = $currentLine . " " . $word;

            // if the current line is too long, add it to the output with a <br> tag
            if (strlen($tempLine) > $lineLength) {
                $output .= $currentLine . "<br>";
                $currentLine = $word;
            } else {
                $currentLine = $tempLine;
            }
        }

        // add the final line to the output
        $output .= $currentLine;

        return $output;
    }

    static public function cosineSimilarity($embedding1, $embedding2)
    {
        // Calculate the dot product of the two embeddings
        $dot_product = 0;
        for ($i = 0; $i < count($embedding1); $i++) {
            $dot_product += $embedding1[$i] * $embedding2[$i];
        }

        // Calculate the magnitude of the first embedding
        $magnitude1 = 0;
        foreach ($embedding1 as $value) {
            $magnitude1 += $value * $value;
        }
        $magnitude1 = sqrt($magnitude1);

        // Calculate the magnitude of the second embedding
        $magnitude2 = 0;
        foreach ($embedding2 as $value) {
            $magnitude2 += $value * $value;
        }
        $magnitude2 = sqrt($magnitude2);

        // Calculate the cosine similarity
        $cosine_similarity = $dot_product / ($magnitude1 * $magnitude2);

        return $cosine_similarity;
    }

    static public function vectorSearch($term)
    {
        $term = trim($term);
        $term = strip_tags($term);
        $term = preg_replace("/[^A-Za-z0-9 ]/", '', $term);

        $client = OpenAI::client($_ENV['OPENAI_KEY']);

        $response = $client->embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $term,
        ]);
        $emb = $response->embeddings[0];

        $embs = json_decode(file_get_contents(DIR . '/data/embeddings.json'), true);
        $results = [];
        foreach ($embs as $e) {
            $results[] = [
                'similarity' => Helper::cosineSimilarity($e['embedding'], $emb->embedding),
                'slug' => $e['slug'],
                'chunk' => $e['chunk']
            ];
        }

        usort($results, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return $results;
    }

    static public function rateLimit($limit = 10,  $timeFrame = 60)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR']; // Get the client's IP address

        $filePath = '/tmp/rate_limit_' . $ipAddress; // File path where request data is stored

        // Read the file if it exists, if not initialize an empty array
        $data = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];

        if (!isset($data['resetTime']) || time() > $data['resetTime']) {
            // Reset the requests and set new resetTime
            $data['requests'] = 1;
            $data['resetTime'] = time() + $timeFrame;
        } else {
            // If we're within the time frame, increment 'requests'
            $data['requests']++;
        }

        // If the request limit has been exceeded, send a 429 'Too Many Requests' response
        if ($data['requests'] > $limit) {
            http_response_code(429);
            echo "Rate limit exceeded. Try again later.";
            exit;
        }

        // Save the updated data back to the file
        file_put_contents($filePath, json_encode($data));
    }

    static public function getPostsByTagSlug($tagSlug)
    {
        $posts = json_decode(file_get_contents(DIR . '/data/postMeta.json'), true);
        $filteredPosts = [];

        foreach ($posts as $post) {
            $tagsCollection = $post['tagsCollection']['items'];

            foreach ($tagsCollection as $tag) {
                if ($tag['slug'] === $tagSlug) {
                    $filteredPosts[] = $post;
                    break;
                }
            }
        }

        return $filteredPosts;
    }
}
