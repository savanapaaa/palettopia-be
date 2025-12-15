<?php

namespace App\Constants;

class PaletteTypes
{
    /**
     * Valid palette type constants
     */
    public const WINTER_CLEAR = 'winter clear';
    public const SUMMER_COOL = 'summer cool';
    public const SPRING_BRIGHT = 'spring bright';
    public const AUTUMN_WARM = 'autumn warm';

    /**
     * Get all valid palette types
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            self::WINTER_CLEAR,
            self::SUMMER_COOL,
            self::SPRING_BRIGHT,
            self::AUTUMN_WARM,
        ];
    }

    /**
     * Get palette validation rule for Laravel
     * 
     * @return string
     */
    public static function validationRule(): string
    {
        return 'in:' . implode(',', self::all());
    }

    /**
     * Check if palette type is valid
     * 
     * @param string $palette
     * @return bool
     */
    public static function isValid(string $palette): bool
    {
        return in_array($palette, self::all());
    }

    /**
     * Get color codes for each palette type
     * 
     * @param string $palette
     * @return array
     */
    public static function getColors(string $palette): array
    {
        $colors = [
            self::WINTER_CLEAR => [
                '#E8F1F5', '#B4D4E1', '#7FB3D5', '#5499C7', 
                '#2980B9', '#1F618D', '#1A5276', '#154360'
            ],
            self::SUMMER_COOL => [
                '#85E3FF', '#ACE7FF', '#A7C7E7', '#B4E7CE', 
                '#95E1D3', '#7FCDCD', '#82CAFF', '#A0CFEC'
            ],
            self::SPRING_BRIGHT => [
                '#FFB5E8', '#FF9CEE', '#FFCCF9', '#FCC2FF', 
                '#F6A6FF', '#82BDFF', '#C5A3FF', '#D5AAFF'
            ],
            self::AUTUMN_WARM => [
                '#E07A5F', '#F2CC8F', '#81B29A', '#C1666B', 
                '#D4A373', '#3D5A80', '#774936', '#F4F1DE'
            ],
        ];

        return $colors[$palette] ?? [];
    }

    /**
     * Get undertone for palette type
     * 
     * @param string $palette
     * @return string
     */
    public static function getUndertone(string $palette): string
    {
        $undertones = [
            self::WINTER_CLEAR => 'cool',
            self::SUMMER_COOL => 'cool',
            self::SPRING_BRIGHT => 'warm',
            self::AUTUMN_WARM => 'warm',
        ];

        return $undertones[$palette] ?? 'neutral';
    }

    /**
     * Get palette description
     * 
     * @param string $palette
     * @return string
     */
    public static function getDescription(string $palette): string
    {
        $descriptions = [
            self::WINTER_CLEAR => 'High contrast, clear and icy colors, bold and bright',
            self::SUMMER_COOL => 'Soft, muted, dusty and gentle colors',
            self::SPRING_BRIGHT => 'Bright, clear, fresh and vibrant colors',
            self::AUTUMN_WARM => 'Rich, earthy, muted and deep colors',
        ];

        return $descriptions[$palette] ?? '';
    }
}
