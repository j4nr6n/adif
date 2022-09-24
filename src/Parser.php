<?php

namespace j4nr6n\ADIF;

use j4nr6n\ADIF\Enum\ParserFlag;
use j4nr6n\ADIF\Exception\ParseException;

/**
 * @see https://adif.org/
 */
class Parser
{
    /**
     * @return array<array-key, array>
     *
     * @throws ParseException
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

    /**
     * @throws ParseException
     */
    public function iterate(string $input): \Iterator
    {
        $isFilePath = in_array(pathinfo($input, PATHINFO_EXTENSION), ['adi', 'adif']);

        if ($isFilePath) {
            if (!is_readable($input)) {
                throw new ParseException(
                    sprintf('Could not read "%s"! Please make sure the file exists and is readable.', $input)
                );
            }

            $input = file_get_contents($input);
        }

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

            while ($i < grapheme_strlen($record)) {
                $ch = grapheme_substr($record, $i, 1);
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
                        }
                        $delimiter = true;
                        break;
                    case '>':
                        if ($flag === ParserFlag::ValueLength) {
                            $flag = ParserFlag::Value;
                        }
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
                            // Don't use the value's length as defined by the spec because of emoji
                            break;
                        case ParserFlag::Value:
                            // Calculate the difference between the current offset and the next `<` if there is one.
                            // Otherwise, the end of the record (`<EOR>` was removed earlier).
                            $valueStartIndex = $i;
                            $valueEndIndex = grapheme_strpos($record, '<', $valueStartIndex);
                            if ($valueEndIndex === false) {
                                $valueEndIndex = grapheme_strlen($record);
                            }
                            $valueLength = (int) $valueEndIndex - $valueStartIndex;

                            $value = trim(grapheme_substr($record, $valueStartIndex, $valueLength));
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