<?php

namespace j4nr6n\ADIF;

use j4nr6n\ADIF\Exception\WriterException;

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

    /**
     * @throws WriterException
     */
    public function write(string $filepath, array $records): void
    {
        if (!is_writable(pathinfo($filepath)['dirname'] ?? '.')) {
            throw new WriterException(
                sprintf('Could not write to "%s"! Please make sure the path is writable.', $filepath)
            );
        }

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

            $data .= "<EOR>\n\n";
        }

        if (file_put_contents($filepath, $data) === false) {
            throw new WriterException(
                sprintf('Could not write to "%s"! Please make sure the file is writable.', $filepath)
            );
        }
    }

    private function generateHeader(): string
    {
        $header = '';

        // ADIF_VER
        $header .= $this->stringifyField('ADIF_VER', '3.1.3');

        // CREATED_TIMESTAMP
        $header .= $this->stringifyField('CREATED_TIMESTAMP', date('Ymd His'));

        // PROGRAMID
        if ($this->programId) {
            $header .= $this->stringifyField('PROGRAMID', $this->programId);
        }

        // PROGRAMVERSION
        if ($this->programVersion) {
            $header .= $this->stringifyField('PROGRAMVERSION', $this->programVersion);
        }

        return $header . "<EOH>\n\n";
    }

    private function stringifyField(string $key, ?string $value): string
    {
        return sprintf("<%s:%d>%s\n", $key, (int) grapheme_strlen($value ?? ''), $value ?? '');
    }
}
