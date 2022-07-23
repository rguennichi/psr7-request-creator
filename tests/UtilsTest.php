<?php

declare(strict_types=1);

namespace Tests\Guennichi\Psr7RequestCreator;

use Guennichi\Psr7RequestCreator\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    /**
     * @dataProvider flattenDataProvider
     *
     * @param array<string, mixed> $source
     * @param array<string, string|object> $expectedResult
     */
    public function testFlatten(array $source, array $expectedResult): void
    {
        $this->assertSame($expectedResult, Utils::flatten($source));
    }

    /**
     * @return array<array{array<string, mixed>, array<string, string|object>}>
     */
    public function flattenDataProvider(): array
    {
        $object = new \stdClass();

        return [
            [
                ['foo' => 'bar'],
                ['foo' => 'bar'],
            ],
            [
                ['a' => 'b', 'c' => ['d' => 'e', 'f']],
                ['a' => 'b', 'c[d]' => 'e', 'c[0]' => 'f'],
            ],
            [
                ['c' => ['d' => ['e' => ['f' => 'g']]]],
                ['c[d][e][f]' => 'g'],
            ],
            [
                ['a' => ['b' => $object]],
                ['a[b]' => $object],
            ],
        ];
    }
}
