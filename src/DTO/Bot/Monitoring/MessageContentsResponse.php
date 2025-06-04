<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\Monitoring;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

class MessageContentsResponse extends BaseDTO
{
    /**
     * Parsed CSV data.
     *
     * @var array<int, array<string, string>>|null
     */
    protected ?array $parsedMessages = null;

    /**
     * Get the download URL.
     *
     * @return string|null
     */
    public function getDownloadUrl(): ?string
    {
        return $this->get('downloadUrl');
    }

    /**
     * Get the raw CSV content.
     *
     * @return string|null
     */
    public function getCsvContent(): ?string
    {
        $content = $this->get('content');
        
        if (is_array($content) && isset($content['raw_response'])) {
            return $content['raw_response'];
        }
        
        return is_string($content) ? $content : null;
    }

    /**
     * Get the metadata.
     *
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        return $this->get('metadata');
    }

    /**
     * Get parsed messages from CSV data.
     *
     * @return array<int, array<string, string>>
     */
    public function getMessages(): array
    {
        if ($this->parsedMessages === null) {
            $this->parsedMessages = $this->parseCSV();
        }
        
        return $this->parsedMessages;
    }

    /**
     * Get messages count.
     *
     * @return int
     */
    public function getMessageCount(): int
    {
        return count($this->getMessages());
    }

    /**
     * Get messages filtered by sender.
     *
     * @param string $sender
     * @return array<int, array<string, string>>
     */
    public function getMessagesBySender(string $sender): array
    {
        return array_filter($this->getMessages(), function($message) use ($sender) {
            return $message['sender'] === $sender;
        });
    }

    /**
     * Get messages filtered by channel.
     *
     * @param string $channelId
     * @return array<int, array<string, string>>
     */
    public function getMessagesByChannel(string $channelId): array
    {
        return array_filter($this->getMessages(), function($message) use ($channelId) {
            return $message['channel_id'] === $channelId;
        });
    }

    /**
     * Get messages filtered by date range.
     *
     * @param string $startDate Start date in Y-m-d format
     * @param string $endDate End date in Y-m-d format
     * @return array<int, array<string, string>>
     */
    public function getMessagesByDateRange(string $startDate, string $endDate): array
    {
        return array_filter($this->getMessages(), function($message) use ($startDate, $endDate) {
            $messageDate = date('Y-m-d', strtotime($message['datetime']));
            return $messageDate >= $startDate && $messageDate <= $endDate;
        });
    }

    /**
     * Get bot messages only.
     *
     * @return array<int, array<string, string>>
     */
    public function getBotMessages(): array
    {
        return array_filter($this->getMessages(), function($message) {
            return str_starts_with($message['sender'], '[Bot]');
        });
    }

    /**
     * Get user messages only (exclude bot messages).
     *
     * @return array<int, array<string, string>>
     */
    public function getUserMessages(): array
    {
        return array_filter($this->getMessages(), function($message) {
            return !str_starts_with($message['sender'], '[Bot]');
        });
    }

    /**
     * Check if the download was successful.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->has('downloadUrl') && $this->has('content');
    }

    /**
     * Parse CSV content into structured data.
     *
     * @return array<int, array<string, string>>
     */
    protected function parseCSV(): array
    {
        $csvContent = $this->getCsvContent();
        
        if (empty($csvContent)) {
            return [];
        }

        // Remove BOM if present (UTF-8 BOM: EF BB BF)
        $csvContent = $this->removeBom($csvContent);

        // Create a temporary stream from CSV content
        $stream = fopen('php://memory', 'r+');
        if ($stream === false) {
            return [];
        }
        
        fwrite($stream, $csvContent);
        rewind($stream);

        try {
            // Create SplFileObject from the stream
            $file = new \SplFileObject('php://memory', 'r+');
            $file->fwrite($csvContent);
            $file->rewind();
            
            // Set CSV parsing flags
            $file->setFlags(\SplFileObject::READ_CSV);
            
            $messages = [];
            $header = null;
            $headerMap = [
                '日時' => 'datetime',
                '送信者' => 'sender', 
                '受信者' => 'receiver',
                'チャンネルID' => 'channel_id',
                'トーク' => 'message'
            ];
            
            foreach ($file as $lineNumber => $row) {
                // Skip empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }
                
                // First non-empty row is the header
                if ($header === null) {
                    $header = $row;
                    continue;
                }
                
                // Parse data row
                $message = [];
                foreach ($header as $index => $columnName) {
                    $key = $headerMap[$columnName] ?? $columnName;
                    $message[$key] = $row[$index] ?? '';
                }
                
                $messages[] = $message;
            }
            
            return $messages;
            
        } finally {
            // Close the stream
            fclose($stream);
        }
    }

    /**
     * Remove BOM (Byte Order Mark) from the beginning of content.
     *
     * @param string $content
     * @return string
     */
    protected function removeBom(string $content): string
    {
        // UTF-8 BOM: EF BB BF
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            return substr($content, 3);
        }
        
        // UTF-16 BE BOM: FE FF
        if (substr($content, 0, 2) === "\xFE\xFF") {
            return substr($content, 2);
        }
        
        // UTF-16 LE BOM: FF FE
        if (substr($content, 0, 2) === "\xFF\xFE") {
            return substr($content, 2);
        }
        
        return $content;
    }

    /**
     * Convert messages to array for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'downloadUrl' => $this->getDownloadUrl(),
            'messageCount' => $this->getMessageCount(),
            'messages' => $this->getMessages(),
            'metadata' => $this->getMetadata(),
        ];
    }
} 