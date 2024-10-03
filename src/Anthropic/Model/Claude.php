<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Anthropic\Model;

use PhpLlm\LlmChain\Anthropic\Model\Claude\Version;
use PhpLlm\LlmChain\Anthropic\Platform;
use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\Response;
use PhpLlm\LlmChain\Response\ResponseInterface;
use PhpLlm\LlmChain\Response\StreamResponse;

final class Claude implements LanguageModel
{
    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        private readonly Platform $platform,
        private ?Version $version = null,
        private readonly array $options = ['temperature' => 1.0, 'max_tokens' => 1000],
    ) {
        $this->version ??= Version::sonnet35();
    }

    /**
     * @param array<string, mixed> $options The options to be used for this specific call.
     *                                      Can overwrite default options.
     */
    public function call(MessageBag $messages, array $options = []): ResponseInterface
    {
        $system = $messages->getSystemMessage();
        $body = array_merge($this->options, $options, [
            'model' => $this->version->name,
            'system' => $system->content,
            'messages' => $messages->withoutSystemMessage(),
        ]);

        $response = $this->platform->request($body);

        if ($response instanceof \Generator) {
            return new StreamResponse($this->convertStream($response));
        }

        return new Response(new Choice($response['content'][0]['text']));
    }

    public function supportsToolCalling(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsImageInput(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsStructuredOutput(): bool
    {
        return false;
    }

    private function convertStream(\Generator $generator): \Generator
    {
        foreach ($generator as $data) {
            if ('content_block_delta' != $data['type'] || !isset($data['delta']['text'])) {
                continue;
            }

            yield $data['delta']['text'];
        }
    }
}
