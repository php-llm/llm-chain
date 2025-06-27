<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Document\Loader;

use PhpLlm\LlmChain\Store\Document\LoaderInterface;
use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Exception\RuntimeException;
use Symfony\Component\Uid\Uuid;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class TextFileLoader implements LoaderInterface
{
    public function __invoke(string $source, array $options = []): iterable
    {
        if (!is_file($source)) {
            throw new RuntimeException(\sprintf('File "%s" does not exist.', $source));
        }

        $content = file_get_contents($source);

        if (false === $content) {
            throw new RuntimeException(\sprintf('Unable to read file "%s"', $source));
        }

        yield new TextDocument(Uuid::v4(), trim($content), new Metadata([
            'source' => $source,
        ]));
    }
}
