<?php
namespace libphonenumber\buildtools;
use libphonenumber\PhoneNumberToTimeZonesMapper;
class GenerateTimeZonesMapData
{
    const GENERATION_COMMENT = <<<'EOT'
EOT;
    private $inputTextFile;
    public function __construct($inputFile, $outputDir)
    {
        $this->inputTextFile = $inputFile;
        if (!is_readable($this->inputTextFile)) {
            throw new \RuntimeException("The provided input text file does not exist.");
        }
        $data = $this->parseTextFile();
        $this->writeMappingFile($outputDir, $data);
    }
    private function parseTextFile()
    {
        $data = file($this->inputTextFile);
        $timeZoneMap = array();
        foreach ($data as $line) {
            $line = str_replace("\n", "", $line);
            $line = str_replace("\r", "", $line);
            $line = trim($line);
            if (strlen($line) == 0 || substr($line, 0, 1) == '#') {
                continue;
            }
            if (strpos($line, '|')) {
                $parts = explode('|', $line);
                $prefix = $parts[0];
                $timezone = $parts[1];
                $timeZoneMap[$prefix] = $timezone;
            }
        }
        return $timeZoneMap;
    }
    private function writeMappingFile($outputFile, $data)
    {
        $phpSource = '<?php' . PHP_EOL
            . self::GENERATION_COMMENT
            . 'return ' . var_export($data, true) . ';'
            . PHP_EOL;
        $outputPath = $outputFile . DIRECTORY_SEPARATOR . PhoneNumberToTimeZonesMapper::MAPPING_DATA_FILE_NAME;
        file_put_contents($outputPath, $phpSource);
    }
}
