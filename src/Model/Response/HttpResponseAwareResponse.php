<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

interface HttpResponseAwareResponse
{
    public function getHttpResponse(): HttpResponse;
}
