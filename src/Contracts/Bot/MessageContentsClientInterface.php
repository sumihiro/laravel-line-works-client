<?php

namespace Sumihiro\LineWorksClient\Contracts\Bot;

use Carbon\CarbonInterface;
use Sumihiro\LineWorksClient\DTO\Bot\Monitoring\MessageContentsResponse;

interface MessageContentsClientInterface
{
    public function download(
        string|CarbonInterface $startTime,
        string|CarbonInterface $endTime,
        string $language = 'ja_JP',
        ?string $botMessageFilterType = null,
        ?int $domainId = null,
        ?string $rogerMessageFilterType = null
    ): MessageContentsResponse;
    
    public function getDownloadUrlOnly(
        string|CarbonInterface $startTime,
        string|CarbonInterface $endTime,
        string $language = 'ja_JP',
        ?string $botMessageFilterType = null,
        ?int $domainId = null,
        ?string $rogerMessageFilterType = null
    ): string;
}