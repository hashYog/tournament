<?php

namespace App\Enum;

enum Stage: string
{
    case BEFORE = 'before';
    case GROUP = 'group';
    case QUARTER = 'quarter';
    case SEMI = 'semi';
    case FINAL = 'final';

    public static function getRate(Stage $stage): int
    {
        $map = [
            0 => Stage::BEFORE,
            1 => Stage::GROUP,
            2 => Stage::QUARTER,
            3 => Stage::SEMI,
            4 => Stage::FINAL,
        ];

        return array_search($stage, $map);
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_map(fn(Stage $stage) => $stage->value, self::cases());
    }
}