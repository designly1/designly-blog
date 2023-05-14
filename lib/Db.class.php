<?php
class Db
{
    private $headers;
    private $endpoint;
    private $key;
    private $databaseName;
    private $collectionName;
    private $client;
    private $db;
    private $collection;
    private $selectedDocument;

    public function __construct($databaseName, $collectionName)
    {
        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;
        $this->endpoint = $_ENV['DB_ENDPOINT'] ?? null;
        $this->key = $_ENV['DB_KEY'] ?? null;

        if (!$this->endpoint) throw new Exception('Please set DB_ENDPOINT env variable');
        if (!$this->key) throw new Exception('Please set DB_KEY env variable');

        $this->headers = array(
            'Content-Type: application/json',
            'api-key: ' . $this->key
        );
    }

    private function sendRequest($method, $endpoint, $data = [])
    {
        $url = $this->endpoint . "/action/{$endpoint}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        $json = json_encode([
            ...$data,
            'dataSource' => 'Cluster0',
            'collection' => $this->collectionName,
            'database' => $this->databaseName
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function insertOne($data)
    {
        return $this->sendRequest('POST', 'insertOne', [
            'document' => $data
        ]);
    }

    public function find($query = '')
    {
        $data = array('filter' => $query);
        return $this->sendRequest('POST', 'find', $data);
    }

    public function findOne($query)
    {
        $data = array(
            'filter' => $query,
            'limit' => 1
        );
        $result = $this->sendRequest('POST', 'find', $data);
        if (!empty($result['documents'])) {
            $this->selectedDocument = $result['documents'][0];
            return $this->selectedDocument;
        }
        return null;
    }

    public function updateOne($query, $data)
    {
        $data = array(
            'filter' => $query,
            'update' => array('$set' => $data)
        );
        return $this->sendRequest('POST', 'updateOne', $data);
    }

    public function updateSelected($data)
    {
        $query = ['_id' => ['$oid' => $this->selectedDocument['_id']]];
        return $this->updateOne(
            $query,
            $data
        );
    }

    public function deleteOne($query)
    {
        $data = array('filter' => $query);
        return $this->sendRequest('POST', 'deleteOne', $data);
    }
}
