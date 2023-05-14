<?php

require_once(__DIR__ . '/config.inc.php');
require_once(DIR . '/inc/build.inc.php');

// Connect to MongoDB
$client = OpenAI::client($_ENV['OPENAI_KEY']);

$response = $client->embeddings()->create([
    'model' => 'text-embedding-ada-002',
    'input' => 'How do I create a sidebar?',
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

print_r($results);