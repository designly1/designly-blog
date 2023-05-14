<?php

try {
    require_once(__DIR__ . '/config.inc.php');
    require_once(DIR . '/inc/build.inc.php');

    $slug = $argv[1] ?? null;

    $client = OpenAI::client($_ENV['OPENAI_KEY']);

    $posts = file_get_contents(DATA_DIR . '/posts.json');
    $posts = json_decode($posts, true);
    $embeddings = [];
    foreach ($posts as $post) {
        doLog('Generating embedding for: ' . $post['title']);
        if ($slug && $slug != $post['slug']) {
            doLog('Skipping post: ' . $post['title']);
            continue;
        }

        $dom = new DOMDocument();
        $dom->loadHTML($post['content']);
        $pres = $dom->getElementsByTagName('pre');
        while ($pres->length > 0) {
            $pre = $pres->item(0);
            $pre->parentNode->removeChild($pre);
        }
        $content = $dom->saveHTML();

        $content = html_entity_decode(preg_replace('/\s+/', ' ', strip_tags($content)));

        $words = explode(' ', $content);
        $count = 0;
        $chunk = '';
        foreach ($words as $word) {
            if ($count <= 150) {
                $chunk .= ' ' . $word;
                $count++;
            } else {
                doLog('Sending chunk');
                $response = $client->embeddings()->create([
                    'model' => 'text-embedding-ada-002',
                    'input' => $chunk,
                ]);

                foreach ($response->embeddings as $embedding) {
                    $emb = [
                        'slug' =>  $post['slug'],
                        'embedding' => $embedding->embedding,
                        'chunk' => $chunk
                    ];
                    $embeddings[] = $emb;
                }
                $chunk = '';
                $count = 0;
            }
        }
        if (strlen($chunk) > 0) {
            doLog('Sending final chunk');
            $response = $client->embeddings()->create([
                'model' => 'text-embedding-ada-002',
                'input' => $chunk
            ]);

            foreach ($response->embeddings as $embedding) {
                $emb = [
                    'slug' =>  $post['slug'],
                    'embedding' => $embedding->embedding,
                    'chunk' => $chunk
                ];
                $embeddings[] = $emb;
            }
        }
        doLog('Embeddings generated: ' . count($embeddings));
    }
    if ($slug) {
        doLog('Loading embeddings file');
        $embeddingsJson = file_get_contents(DATA_DIR . '/embeddings.json');
        $embeddings = json_decode($embeddingsJson, true);
        doLog('Removing stale embeddings');
        if ($slug) {
            $embeddings = array_filter($embeddings, function ($emb) use ($slug) {
                return $emb['slug'] !== $slug;
            });
        }
        doLog('Saving embeddings');
        file_put_contents(DATA_DIR . '/embeddings.json', json_encode(array_values($embeddings)));
    } else {
        doLog('Saving embeddings');
        file_put_contents(DATA_DIR . '/embeddings.json', json_encode($embeddings));
    }
} catch (Exception $e) {
    doLog($e->getMessage());
}
