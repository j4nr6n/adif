<?php

namespace j4nr6n\ADIF;

use j4nr6n\ADIF\Enum\ParserFlag;

/**
 * @see https://adif.org/
 */
class Parser
{
    /**
     * @return array<array-key, array>
     */
    public function parse(string $input): array
    {
        $data = [];

        /** @var array $datum */
        foreach ($this->iterate($input) as $datum) {
            $data[] = $datum;
        }

        return $data;
    }

    public function iterate(string $input): \Iterator
    {
        // Trim leading or trailing white-space
        $input = trim($input);

        // Discard the header if there is one
        $pos = mb_stripos($input, '<EOH>');
        if ($pos !== false) {
            $input = substr($input, $pos + 5, strlen($input) - $pos - 5);
        }

        // Lines beginning with '#' are comments
        $input = str_replace(["\r\n", "\r"], "\n", $input);
        $lines = explode("\n", $input);
        $input = '';

        foreach ($lines as $line) {
            if (!str_starts_with(ltrim($line), '#')) {
                $input .= $line;
            }
        }

        // <EOR> separates the records
        $input = str_ireplace('<eor>', '<EOR>', $input);
        $records = explode('<EOR>', $input);

        foreach ($records as $record) {
            if (empty($record)) {
                continue; // Ignore empty records
            }

            $datum = [];
            $flag = $tag = '';
            $i = 0;

            while ($i < mb_strlen($record, '8bit')) {
                $ch = mb_substr($record, $i, 1, '8bit');
                $delimiter = false;

                switch ($ch) {
                    case '<':
                        $tag = '';
                        $flag = ParserFlag::Tag;
                        $delimiter = true;
                        break;
                    case ':':
                        if ($flag === ParserFlag::Tag) {
                            $flag = ParserFlag::ValueLength;
                        } elseif ($flag === ParserFlag::ValueLength) {
                            $flag = ParserFlag::DataType;
                        }
                        $delimiter = true;
                        break;
                    case '>':
                        $flag = ParserFlag::Value;
                        $delimiter = true;
                        break;
                    default:
                        break;
                }

                if ($delimiter === false) {
                    switch ($flag) {
                        case ParserFlag::Tag:
                            $tag .= $ch;
                            break;
                        case ParserFlag::ValueLength:
                            /**
                             * Don't use the value's length as defined by the spec. Due to some
                             * ambiguity in the value length definition, there are inconsistencies
                             * between implementations. Some count bytes, some count code-points,
                             * and others count grapheme clusters.
                             *
                             * Without knowing what to count. We instead use a regular expression
                             * to find the next tag in the record. We use that position, or the
                             * position of last character of the string if there are no remaining
                             * tags.
                             */
                            break;
                        case ParserFlag::Value:
                            // Calculate the difference between the current offset and the next tag if there is one.
                            // Otherwise, the end of the record (`<EOR>` was removed earlier).
                            $valueStartIndex = $i;

                            // <key:value_length:optional_data_type>
                            preg_match(
                                '/<\w+:\d+(?::\w+)?>/',
                                $record,
                                $matches,
                                PREG_OFFSET_CAPTURE,
                                $valueStartIndex
                            );

                            $valueEndIndex = $matches[0][1] ?? mb_strlen($record, '8bit');
                            $valueLength = $valueEndIndex - $valueStartIndex;
                            $value = trim(mb_substr($record, $valueStartIndex, $valueLength, '8bit'));
                            $datum[mb_strtoupper($tag)] = mb_convert_encoding($value, 'UTF-8');
                            $i += $valueLength - 1;
                            break;
                        default:
                            break;
                    }
                }

                $i++;
            }

            yield $datum;
        }
    }
}
