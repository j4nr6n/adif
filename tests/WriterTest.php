<?php

namespace j4nr6n\ADIF\Tests;

use j4nr6n\ADIF\Writer;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    private const ADIF_DATA = [
        ['QSO_DATE' => '19690101', 'CALL' => 'FOO', 'COMMENT' => '🐧'],
        ['QSO_DATE' => '19690101', 'CALL' => 'FOO', 'COMMENT' => 'BAR 🐧'],
    ];

    public function testWriterWrites(): void
    {
        $outputContent = (new Writer())->write(self::ADIF_DATA);

        $this->assertSame(5, mb_substr_count($outputContent, "\n"));
    }
}
