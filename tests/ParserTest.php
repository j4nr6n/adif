<?php

namespace j4nr6n\ADIF\Tests;

use j4nr6n\ADIF\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    private const ADIF_DATA = <<<EOL
        <QSO_DATE:8:D>19690101<CALL:3>FOO<COMMENT:1>🐧<EOR>
        <QSO_DATE:8:D>19690101<CALL:3>FOO<COMMENT:5>BAR 🐧<EOR>
    EOL;

    public function testParseReturnsExpectedCount(): void
    {
        $result = (new Parser())->parse(self::ADIF_DATA);

        self::assertCount(2, $result);
    }

    public function testIterateReturnsIterator(): void
    {
        $result = (new Parser())->iterate(self::ADIF_DATA);

        self::assertIsIterable($result);
    }
}
