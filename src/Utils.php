<?php

declare(strict_types=1);

namespace Guennichi\Psr7RequestCreator;

final class Utils
{
    /**
     * @param array<string, mixed> $source
     *
     * @return array<string, mixed>
     */
    public static function flatten(array $source, string $prepend = ''): array
    {
        $flatten = [];
        foreach ($source as $key => $value) {
            $key = $prepend ? $prepend . '[' . $key . ']' : $key;
            if (\is_array($value) && !empty($value)) {
                $flatten[] = self::flatten($value, $key);
            } else {
                $flatten[] = [$key => $value];
            }
        }

        return array_merge(...$flatten);
    }
}
