<?php
use SebastianBergmann\Environment\Console;
class PHPUnit_TextUI_ResultPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    const EVENT_TEST_START      = 0;
    const EVENT_TEST_END        = 1;
    const EVENT_TESTSUITE_START = 2;
    const EVENT_TESTSUITE_END   = 3;
    const COLOR_NEVER   = 'never';
    const COLOR_AUTO    = 'auto';
    const COLOR_ALWAYS  = 'always';
    const COLOR_DEFAULT = self::COLOR_NEVER;
    private static $ansiCodes = array(
      'bold'       => 1,
      'fg-black'   => 30,
      'fg-red'     => 31,
      'fg-yellow'  => 33,
      'fg-cyan'    => 36,
      'fg-white'   => 37,
      'bg-red'     => 41,
      'bg-green'   => 42,
      'bg-yellow'  => 43
    );
    protected $column = 0;
    protected $maxColumn;
    protected $lastTestFailed = false;
    protected $numAssertions = 0;
    protected $numTests = -1;
    protected $numTestsRun = 0;
    protected $numTestsWidth;
    protected $colors = false;
    protected $debug = false;
    protected $verbose = false;
    private $numberOfColumns;
    public function __construct($out = null, $verbose = false, $colors = self::COLOR_DEFAULT, $debug = false, $numberOfColumns = 80)
    {
        parent::__construct($out);
        if (!is_bool($verbose)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'boolean');
        }
        $availableColors = array(self::COLOR_NEVER, self::COLOR_AUTO, self::COLOR_ALWAYS);
        if (!in_array($colors, $availableColors)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                3,
                vsprintf('value from "%s", "%s" or "%s"', $availableColors)
            );
        }
        if (!is_bool($debug)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(4, 'boolean');
        }
        if (!is_int($numberOfColumns) && $numberOfColumns != 'max') {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(5, 'integer or "max"');
        }
        $console            = new Console;
        $maxNumberOfColumns = $console->getNumberOfColumns();
        if ($numberOfColumns == 'max' || $numberOfColumns > $maxNumberOfColumns) {
            $numberOfColumns = $maxNumberOfColumns;
        }
        $this->numberOfColumns = $numberOfColumns;
        $this->verbose         = $verbose;
        $this->debug           = $debug;
        if ($colors === self::COLOR_AUTO && $console->hasColorSupport()) {
            $this->colors = true;
        } else {
            $this->colors = (self::COLOR_ALWAYS === $colors);
        }
    }
    public function printResult(PHPUnit_Framework_TestResult $result)
    {
        $this->printHeader();
        $this->printErrors($result);
        $printSeparator = $result->errorCount() > 0;
        if ($printSeparator && $result->failureCount() > 0) {
            $this->write("\n--\n\n");
        }
        $printSeparator = $printSeparator || $result->failureCount() > 0;
        $this->printFailures($result);
        if ($this->verbose) {
            if ($printSeparator && $result->riskyCount() > 0) {
                $this->write("\n--\n\n");
            }
            $printSeparator = $printSeparator ||
                              $result->riskyCount() > 0;
            $this->printRisky($result);
            if ($printSeparator && $result->notImplementedCount() > 0) {
                $this->write("\n--\n\n");
            }
            $printSeparator = $printSeparator ||
                              $result->notImplementedCount() > 0;
            $this->printIncompletes($result);
            if ($printSeparator && $result->skippedCount() > 0) {
                $this->write("\n--\n\n");
            }
            $this->printSkipped($result);
        }
        $this->printFooter($result);
    }
    protected function printDefects(array $defects, $type)
    {
        $count = count($defects);
        if ($count == 0) {
            return;
        }
        $this->write(
            sprintf(
                "There %s %d %s%s:\n",
                ($count == 1) ? 'was' : 'were',
                $count,
                $type,
                ($count == 1) ? '' : 's'
            )
        );
        $i = 1;
        foreach ($defects as $defect) {
            $this->printDefect($defect, $i++);
        }
    }
    protected function printDefect(PHPUnit_Framework_TestFailure $defect, $count)
    {
        $this->printDefectHeader($defect, $count);
        $this->printDefectTrace($defect);
    }
    protected function printDefectHeader(PHPUnit_Framework_TestFailure $defect, $count)
    {
        $this->write(
            sprintf(
                "\n%d) %s\n",
                $count,
                $defect->getTestName()
            )
        );
    }
    protected function printDefectTrace(PHPUnit_Framework_TestFailure $defect)
    {
        $e = $defect->thrownException();
        $this->write((string) $e);
        while ($e = $e->getPrevious()) {
            $this->write("\nCaused by\n" . $e);
        }
    }
    protected function printErrors(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->errors(), 'error');
    }
    protected function printFailures(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->failures(), 'failure');
    }
    protected function printIncompletes(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->notImplemented(), 'incomplete test');
    }
    protected function printRisky(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->risky(), 'risky test');
    }
    protected function printSkipped(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->skipped(), 'skipped test');
    }
    protected function printHeader()
    {
        $this->write("\n\n" . PHP_Timer::resourceUsage() . "\n\n");
    }
    protected function printFooter(PHPUnit_Framework_TestResult $result)
    {
        if (count($result) === 0) {
            $this->writeWithColor(
                'fg-black, bg-yellow',
                'No tests executed!'
            );
        } elseif ($result->wasSuccessful() &&
                 $result->allHarmless() &&
                 $result->allCompletelyImplemented() &&
                 $result->noneSkipped()) {
            $this->writeWithColor(
                'fg-black, bg-green',
                sprintf(
                    'OK (%d test%s, %d assertion%s)',
                    count($result),
                    (count($result) == 1) ? '' : 's',
                    $this->numAssertions,
                    ($this->numAssertions == 1) ? '' : 's'
                )
            );
        } else {
            if ($result->wasSuccessful()) {
                $color = 'fg-black, bg-yellow';
                if ($this->verbose) {
                    $this->write("\n");
                }
                $this->writeWithColor(
                    $color,
                    'OK, but incomplete, skipped, or risky tests!'
                );
            } else {
                $color = 'fg-white, bg-red';
                $this->write("\n");
                $this->writeWithColor($color, 'FAILURES!');
            }
            $this->writeCountString(count($result), 'Tests', $color, true);
            $this->writeCountString($this->numAssertions, 'Assertions', $color, true);
            $this->writeCountString($result->errorCount(), 'Errors', $color);
            $this->writeCountString($result->failureCount(), 'Failures', $color);
            $this->writeCountString($result->skippedCount(), 'Skipped', $color);
            $this->writeCountString($result->notImplementedCount(), 'Incomplete', $color);
            $this->writeCountString($result->riskyCount(), 'Risky', $color);
            $this->write(".\n");
        }
    }
    public function printWaitPrompt()
    {
        $this->write("\n<RETURN> to continue\n");
    }
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgressWithColor('fg-red, bold', 'E');
        $this->lastTestFailed = true;
    }
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->writeProgressWithColor('bg-red, fg-white', 'F');
        $this->lastTestFailed = true;
    }
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgressWithColor('fg-yellow, bold', 'I');
        $this->lastTestFailed = true;
    }
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgressWithColor('fg-yellow, bold', 'R');
        $this->lastTestFailed = true;
    }
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgressWithColor('fg-cyan, bold', 'S');
        $this->lastTestFailed = true;
    }
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->numTests == -1) {
            $this->numTests      = count($suite);
            $this->numTestsWidth = strlen((string) $this->numTests);
            $this->maxColumn     = $this->numberOfColumns - strlen('  /  (XXX%)') - (2 * $this->numTestsWidth);
        }
    }
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if ($this->debug) {
            $this->write(
                sprintf(
                    "\nStarting test '%s'.\n",
                    PHPUnit_Util_Test::describe($test)
                )
            );
        }
    }
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if (!$this->lastTestFailed) {
            $this->writeProgress('.');
        }
        if ($test instanceof PHPUnit_Framework_TestCase) {
            $this->numAssertions += $test->getNumAssertions();
        } elseif ($test instanceof PHPUnit_Extensions_PhptTestCase) {
            $this->numAssertions++;
        }
        $this->lastTestFailed = false;
        if ($test instanceof PHPUnit_Framework_TestCase) {
            if (!$test->hasExpectationOnOutput()) {
                $this->write($test->getActualOutput());
            }
        }
    }
    protected function writeProgress($progress)
    {
        $this->write($progress);
        $this->column++;
        $this->numTestsRun++;
        if ($this->column == $this->maxColumn) {
            $this->write(
                sprintf(
                    ' %' . $this->numTestsWidth . 'd / %' .
                    $this->numTestsWidth . 'd (%3s%%)',
                    $this->numTestsRun,
                    $this->numTests,
                    floor(($this->numTestsRun / $this->numTests) * 100)
                )
            );
            $this->writeNewLine();
        }
    }
    protected function writeNewLine()
    {
        $this->column = 0;
        $this->write("\n");
    }
    protected function formatWithColor($color, $buffer)
    {
        if (!$this->colors) {
            return $buffer;
        }
        $codes   = array_map('trim', explode(',', $color));
        $lines   = explode("\n", $buffer);
        $padding = max(array_map('strlen', $lines));
        $styles  = array();
        foreach ($codes as $code) {
            $styles[] = self::$ansiCodes[$code];
        }
        $style = sprintf("\x1b[%sm", implode(';', $styles));
        $styledLines = array();
        foreach ($lines as $line) {
            $styledLines[] = $style . str_pad($line, $padding) . "\x1b[0m";
        }
        return implode("\n", $styledLines);
    }
    protected function writeWithColor($color, $buffer, $lf = true)
    {
        $this->write($this->formatWithColor($color, $buffer));
        if ($lf) {
            $this->write("\n");
        }
    }
    protected function writeProgressWithColor($color, $buffer)
    {
        $buffer = $this->formatWithColor($color, $buffer);
        $this->writeProgress($buffer);
    }
    private function writeCountString($count, $name, $color, $always = false)
    {
        static $first = true;
        if ($always || $count > 0) {
            $this->writeWithColor(
                $color,
                sprintf(
                    '%s%s: %d',
                    !$first ? ', ' : '',
                    $name,
                    $count
                ),
                false
            );
            $first = false;
        }
    }
}
