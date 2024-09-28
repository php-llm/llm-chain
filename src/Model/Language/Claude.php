<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Language;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Anthropic;
use PhpLlm\LlmChain\Response\ResponseInterface;
use PhpLlm\LlmChain\Response\StreamResponse;
use PhpLlm\LlmChain\Response\TextResponse;

final readonly class Claude implements LanguageModel
{
    public const VERSION_3_HAIKU = 'claude-3-haiku-20240307';
    public const VERSION_3_SONNET = 'claude-3-sonnet-20240229';
    public const VERSION_35_SONNET = 'claude-3-5-sonnet-20240620';
    public const VERSION_3_OPUS = 'claude-3-opus-20240229';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        private Anthropic $platform,
        private string $version = self::VERSION_35_SONNET,
        private array $options = ['temperature' => 1.0, 'max_tokens' => 1000],
    ) {
    }

    /**
     * @param array<string, mixed> $options The options to be used for this specific call.
     *                                      Can overwrite default options.
     */
    public function call(MessageBag $messages, array $options = []): ResponseInterface
    {
        $system = $messages->getSystemMessage();
        $body = array_merge($this->options, $options, [
            'model' => $this->version,
            'system' => $system->content,
            'messages' => $messages->withoutSystemMessage(),
        ]);

        $response = $this->platform->request($body);

        if ($response instanceof \Generator) {
            return new StreamResponse($this->convertStream($response));
        }

        return new TextResponse($response['content'][0]['text']);
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
