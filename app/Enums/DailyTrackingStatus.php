<?php

namespace App\Enums;

enum DailyTrackingStatus: string
{
    case NO_REQUIERE = 'no_requiere';
    case SURVEY = 'survey';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::NO_REQUIERE => 'No requiere',
            self::SURVEY => 'Encuesta',
            self::CLOSED => 'Cerrado',
        };
    }
}
