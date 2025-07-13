<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Tool;

use MrMySQL\YoutubeTranscript\TranscriptListFetcher;
use PhpLlm\LlmChain\Chain\Exception\LogicException;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
#[AsTool('youtube_transcript', 'Fetches the transcript of a YouTube video')]
final readonly class YouTubeTranscriber
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
        if (!class_exists(TranscriptListFetcher::class)) {
            throw new LogicException('For using the YouTube transcription tool, the mrmysql/youtube-transcript package is required. Try running "composer require mrmysql/youtube-transcript".');
        }
    }

    /**
     * @param string $videoId The ID of the YouTube video
     */
    public function __invoke(string $videoId): string
    {
        $psr18Client = new Psr18Client($this->client);
        $fetcher = new TranscriptListFetcher($psr18Client, $psr18Client, $psr18Client);

        $list = $fetcher->fetch($videoId);
        $transcript = $list->findTranscript($list->getAvailableLanguageCodes());

        return array_reduce($transcript->fetch(), function (string $carry, array $item): string {
            return $carry.\PHP_EOL.$item['text'];
        }, '');
    }
}
