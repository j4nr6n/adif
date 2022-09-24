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
        $tmpFile = tmpfile();

        (new Writer())->write(stream_get_meta_data($tmpFile)['uri'], self::ADIF_DATA);

        $outputContent = stream_get_contents($tmpFile);

        $this->assertSame(14, mb_substr_count($outputContent, "\n"));
    }
}
