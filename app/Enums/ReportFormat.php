<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportFormat: string
{
    case TABLE = 'table';
    case MARKDOWN = 'md';
    case JSON = 'json';

    public static function fromCli(string $format): self
    {
        return match (strtolower(trim($format))) {
            'table' => self::TABLE,
            'md', 'markdown' => self::MARKDOWN,
            'json' => self::JSON,
            default => throw new \InvalidArgumentException("Unsupported report format [{$format}]."),
        };
    }
}
