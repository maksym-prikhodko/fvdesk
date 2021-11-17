<?php
class PHPUnit_Extensions_PhptTestSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct($directory, array $options = array())
    {
        if (is_string($directory) && is_dir($directory)) {
            $this->setName($directory);
            $facade = new File_Iterator_Facade;
            $files  = $facade->getFilesAsArray($directory, '.phpt');
            foreach ($files as $file) {
                $this->addTestFile($file, $options);
            }
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'directory name');
        }
    }
}
