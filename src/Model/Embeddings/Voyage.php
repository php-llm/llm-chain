<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Embeddings;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingsModel;
use PhpLlm\LlmChain\Platform\Voyage as Platform;

final readonly class Voyage implements EmbeddingsModel
{
    public const VERSION_V3 = 'voyage-3';
    public const VERSION_V3_LITE = 'voyage-3-lite';
    public const VERSION_FINANCE_2 = 'voyage-finance-2';
    public const VERSION_MULTILINGUAL_2 = 'voyage-multilingual-2';
    public const VERSION_LAW_2 = 'voyage-law-2';
    public const VERSION_CODE_2 = 'voyage-code-2';

    public function __construct(
        private Platform $platform,
        private string $version = self::VERSION_V3,
    ) {
    }

    public function create(string $text, array $options = []): Vector
    {
        $vectors = $this->multiCreate([$text], $options);

        return $vectors[0];
    }

    public function multiCreate(array $texts, array $options = []): array
    {
        $response = $this->platform->request(array_merge($options, [
            'model' => $this->version,
            'input' => $texts,
        ]));

        return array_map(fn (array $data) => new Vector($data['embedding']), $response['data']);
    }
}
