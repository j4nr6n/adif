<?php

namespace j4nr6n\ADIF\Tests;

use j4nr6n\ADIF\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    private string $adifData = <<<EOL
        <QSO_DATE:8:D>19690101<CALL:3>FOO<COMMENT:1>🐧<EOR>
        <QSO_DATE:8:D>19690101<CALL:3>FOO<COMMENT:5>BAR 🐧<EOR>
    EOL;

    public function testParseReturnsExpectedCount(): void
    {
        $result = (new Parser())->parse($this->adifData);

        self::assertCount(2, $result);
    }

    public function testIterateReturnsIterator(): void
    {
        $result = (new Parser())->iterate($this->adifData);

        self::assertIsIterable($result);
    }

    /**
     * @dataProvider getAdifStrings
     */
    public function testItParsesTheRecord(int $expected, string $adifData): void
    {
        $result = (new Parser())->parse($adifData);

        $this->assertSame($expected, grapheme_strlen($result[0]['COMMENT']));
    }

    public function getAdifStrings(): array
    {
        return [
            [1, '<QSO_DATE:8:D>19690101<CALL:3>FOO<COMMENT:1>🐧<EOR>'],
            [5, '<QSO_DATE:8:D>19690101<CALL:3>FOO<COMMENT:5>BAR 🐧<EOR>'],
            [2, '<QSO_DATE:8:D>19690101<CALL:3>FOO<COMMENT:2>🐧<<EOR>'],
            [11, '<QSO_DATE:8:D>19690101<CALL:3>FOO<COMMENT:11>BAR 🐧<test><EOR>'],
        ];
    }
}
