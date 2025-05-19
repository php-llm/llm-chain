<?php

namespace PhpLlm\LlmChain\Bridge\AwsBedrock;

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

final class BedrockRequestSigner
{
    public function __construct(#[\SensitiveParameter] private Credentials $credentials, private string $region)
    {
    }

    public function signRequest(string $method, string $endpoint, array $jsonBody, array $extraHeaders = [])
    {
        $signature = new SignatureV4('bedrock', $this->region);

        $uri = new Uri($endpoint);

        $finalHeaders = array_merge([
            'Host' => $uri->getHost(),
            'Content-Type' => 'application/json',
        ], $extraHeaders);

        $request = new Request(
            $method,
            $uri,
            $finalHeaders,
            $encodedBody = json_encode($jsonBody)
        );

        $signedRequest = $signature->signRequest($request, $this->credentials);

        $signedHeaders = [];
        foreach ($signedRequest->getHeaders() as $name => $values) {
            $signedHeaders[$name] = $signedRequest->getHeaderLine($name);
        }

        unset($request, $finalHeaders, $uri, $signature);

        return [
            'headers' => $signedHeaders,
            'body' => $encodedBody,
        ];
    }
}
