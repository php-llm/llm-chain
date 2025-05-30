<?php

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use AsyncAws\BedrockRuntime\Input\InvokeModelRequest;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

$client = new BedrockRuntimeClient();

$body = [
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                ['text' => 'Who is the current chancellor of Germany? Use Wikipedia to find the answer.'],
            ],
        ],
        [
            'role' => 'assistant',
            'content' => [
                [
                    'toolUse' => [
                        'toolUseId' => '14dd301d-6653-4cf5-bf23-e0914938bf06',
                        'name' => 'wikipedia_search',
                        'input' => [
                            'query' => 'current chancellor of Germany',
                        ],
                    ],
                ],
            ],
        ],
        [
            'role' => 'user',
            'content' => [
                [
                    'toolResult' => [
                        'toolUseId' => '14dd301d-6653-4cf5-bf23-e0914938bf06',
                        'content' => "Articles with the following titles were found on Wikipedia:\n - Chancellor of Germany\n - Vice-Chancellor of Germany\n - List of chancellors of Germany by time in office\n - Social Democratic Party of Germany\n - Chancellor of Austria\n - Chancellor of Switzerland\n - Bundestag\n - Vice-Chancellor of Austria\n - Federal Government of Germany\n - Federal Chancellery of Germany\n\nUse the title of the article with tool \"wikipedia_article\" to load the content.",
                    ],
                ],
            ],
        ],
    ],
    'toolConfig' => [
        'tools' => [
            [
                'toolSpec' => [
                    'name' => 'wikipedia_search',
                    'description' => 'Searches Wikipedia for a given query',
                    'inputSchema' => [
                        'json' => [
                            'type' => 'object',
                            'properties' => [
                                'query' => [
                                    'type' => 'string',
                                    'description' => 'The query to search for on Wikipedia',
                                ],
                            ],
                            'required' => [
                                'query',
                            ],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ],
            [
                'toolSpec' => [
                    'name' => 'wikipedia_article',
                    'description' => 'Retrieves a Wikipedia article by its title',
                    'inputSchema' => [
                        'json' => [
                            'type' => 'object',
                            'properties' => [
                                'title' => [
                                    'type' => 'string',
                                    'description' => 'The title of the article to load from Wikipedia',
                                ],
                            ],
                            'required' => [
                                'title',
                            ],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ],
        ],
    ],
    'inferenceConfig' => [
        'temperature' => 1,
        'maxTokens' => 1000,
    ],
];

$request = [
    'modelId' => 'eu.amazon.nova-pro-v1:0',
    'contentType' => 'application/json',
    'body' => json_encode($body, \JSON_THROW_ON_ERROR),
];

dump($client->invokeModel(new InvokeModelRequest($request))->getBody());
