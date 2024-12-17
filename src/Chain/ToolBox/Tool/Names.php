<?php

namespace PhpLlm\LlmChain\Chain\ToolBox\Tool;

enum Names: string
{
    case weather = 'weather';
    case clock = 'clock';
    case serpapi = 'serpapi';
    case similarity_search = 'similarity_search';
    case wikipedia_search = 'wikipedia_search';
    case wikipedia_article = 'wikipedia_article';
    case youtube_transcript = 'youtube_transcript';
}
