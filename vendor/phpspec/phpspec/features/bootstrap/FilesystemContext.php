<?php
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Matcher\FileExistsMatcher;
use Matcher\FileHasContentsMatcher;
use PhpSpec\Matcher\MatchersProviderInterface;
use Symfony\Component\Filesystem\Filesystem;
class FilesystemContext implements Context, MatchersProviderInterface
{
    private $workingDirectory;
    private $filesystem;
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }
    public function prepWorkingDirectory()
    {
        $this->workingDirectory = tempnam(sys_get_temp_dir(), 'phpspec-behat');
        $this->filesystem->remove($this->workingDirectory);
        $this->filesystem->mkdir($this->workingDirectory);
        chdir($this->workingDirectory);
    }
    public function removeWorkingDirectory()
    {
        $this->filesystem->remove($this->workingDirectory);
    }
    public function theFileContains($file, PyStringNode $contents)
    {
        $this->filesystem->dumpFile($file, (string)$contents);
    }
    public function theClassOrTraitOrSpecFileContains($file, PyStringNode $contents)
    {
        $this->theFileContains($file, $contents);
        require_once($file);
    }
    public function theConfigFileContains(PyStringNode $contents)
    {
        $this->theFileContains('phpspec.yml', $contents);
    }
    public function thereIsNoFile($file)
    {
        expect($file)->toNotExist();
        expect(file_exists($file))->toBe(false);
    }
    public function theFileShouldContain($file, PyStringNode $contents)
    {
        expect($file)->toExist();
        expect($file)->toHaveContents($contents);
    }
    public function getMatchers()
    {
        return array(
            new FileExistsMatcher(),
            new FileHasContentsMatcher()
        );
    }
}
