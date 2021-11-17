<?php
namespace libphonenumber\buildtools;
use libphonenumber\PhoneMetadata;
class BuildMetadataPHPFromXml
{
    const GENERATION_COMMENT = <<<'EOT'
EOT;
    const MAP_COMMENT = <<<'EOT'
EOT;
    const COUNTRY_CODE_SET_COMMENT =
        "  
    const REGION_CODE_SET_COMMENT =
        "  
    public function start($inputFile, $outputDir, $filePrefix, $mappingClass, $mappingClassLocation, $liteBuild)
    {
        $savePath = $outputDir . $filePrefix;
        $metadataCollection = BuildMetadataFromXml::buildPhoneMetadataCollection($inputFile, $liteBuild);
        $this->writeMetadataToFile($metadataCollection, $savePath);
        $countryCodeToRegionCodeMap = BuildMetadataFromXml::buildCountryCodeToRegionCodeMap($metadataCollection);
        ksort($countryCodeToRegionCodeMap);
        $this->writeCountryCallingCodeMappingToFile($countryCodeToRegionCodeMap, $mappingClassLocation, $mappingClass);
    }
    private function writeMetadataToFile($metadataCollection, $filePrefix)
    {
        foreach ($metadataCollection as $metadata) {
            $regionCode = $metadata->getId();
            if ($regionCode === '001' || $regionCode == '') {
                $regionCode = $metadata->getCountryCode();
            }
            $data = '<?php' . PHP_EOL . self::GENERATION_COMMENT . PHP_EOL . 'return ' . var_export(
                    $metadata->toArray(),
                    true
                ) . ';' . PHP_EOL;
            file_put_contents($filePrefix . "_" . $regionCode . '.php', $data);
        }
    }
    private function writeCountryCallingCodeMappingToFile($countryCodeToRegionCodeMap, $outputDir, $mappingClass)
    {
        $hasRegionCodes = false;
        foreach ($countryCodeToRegionCodeMap as $key => $listWithRegionCode) {
            if (count($listWithRegionCode) > 0) {
                $hasRegionCodes = true;
                break;
            }
        }
        $hasCountryCodes = (count($countryCodeToRegionCodeMap) > 1);
        $variableName = lcfirst($mappingClass);
        $data = '<?php' . PHP_EOL .
            self::GENERATION_COMMENT . PHP_EOL .
            "namespace libphonenumber;" . PHP_EOL .
            "class {$mappingClass} {" . PHP_EOL .
            PHP_EOL;
        if ($hasRegionCodes && $hasCountryCodes) {
            $data .= self::MAP_COMMENT . PHP_EOL;
            $data .= "   public static \${$variableName} = " . var_export(
                    $countryCodeToRegionCodeMap,
                    true
                ) . ";" . PHP_EOL;
        } elseif ($hasCountryCodes) {
            $data .= self::COUNTRY_CODE_SET_COMMENT . PHP_EOL;
            $data .= "   public static \${$variableName} = " . var_export(
                    array_keys($countryCodeToRegionCodeMap),
                    true
                ) . ";" . PHP_EOL;
        } else {
            $data .= self::REGION_CODE_SET_COMMENT . PHP_EOL;
            $data .= "   public static \${$variableName} = " . var_export(
                    $countryCodeToRegionCodeMap[0],
                    true
                ) . ";" . PHP_EOL;
        }
        $data .= PHP_EOL .
            "}" . PHP_EOL;
        file_put_contents($outputDir . $mappingClass . '.php', $data);
    }
}
