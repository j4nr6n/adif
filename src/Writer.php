<?php

namespace j4nr6n\ADIF;

/**
 * @see https://adif.org/
 */
class Writer
{
    public function __construct(
        private ?string $programId = null,
        private ?string $programVersion = null
    ) {
    }

    public function setProgramId(?string $programId): self
    {
        $this->programId = $programId;

        return $this;
    }

    public function setProgramVersion(?string $programVersion): self
    {
        $this->programVersion = $programVersion;

        return $this;
    }

    public function write(array $records): string
    {
        $data = $this->generateHeader();

        /** @var array $record */
        foreach ($records as $record) {
            /**
             * @var string $key
             * @var string|null $value
             */
            foreach ($record as $key => $value) {
                $data .= $this->stringifyField($key, $value);
            }

            $data .= "<EOR>\n";
        }

        return $data;
    }

    private function generateHeader(): string
    {
        $header = '';

        // ADIF_VER
        $header .= $this->stringifyField('ADIF_VER', '3.1.3') . "\n";

        // CREATED_TIMESTAMP
        $header .= $this->stringifyField('CREATED_TIMESTAMP', date('Ymd His')) . "\n";

        // PROGRAMID
        if ($this->programId !== null) {
            $header .= $this->stringifyField('PROGRAMID', $this->programId) . "\n";
        }

        // PROGRAMVERSION
        if ($this->programVersion !== null) {
            $header .= $this->stringifyField('PROGRAMVERSION', $this->programVersion) . "\n";
        }

        return $header . "<EOH>\n";
    }

    private function stringifyField(string $key, ?string $value): string
    {
        return sprintf("<%s:%d>%s", $key, (int) grapheme_strlen($value ?? ''), $value ?? '');
    }
}
