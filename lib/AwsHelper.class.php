<?php

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

class AwsHelper
{
    private $client;

    public function __construct()
    {
        $this->client = new SesClient([
            'version' => 'latest',
            'region'  => $_ENV['AWS_REGION'] ?? 'us-east-2',
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'] ?? '',
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '',
            ],
        ]);
    }

    public function sendTemplatedEmail($recip, $template, $data) {
        try {
            $result = $this->client->sendTemplatedEmail([
                'Destination' => [
                    'ToAddresses' => [...$recip],
                ],
                'Source' => EMAIL_FROM_NAME . ' <' . EMAIL_FROM . '>',
                'Template' => $template,
                'TemplateData' => json_encode($data),
            ]);
        
            return $result['MessageId'];
        } catch (AwsException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
