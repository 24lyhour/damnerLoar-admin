<?php

namespace App\Concerns;

/**
 * For string-backed enums that define label(), labelKh() and tone().
 * options() is what list pages send to Vue for filters and badges.
 */
trait EnumOptions
{
    /**
     * @return list<array{value: string, label: string, labelKh: string, tone: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $case): array => [
            'value' => $case->value,
            'label' => $case->label(),
            'labelKh' => $case->labelKh(),
            'tone' => $case->tone(),
        ], self::cases());
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
