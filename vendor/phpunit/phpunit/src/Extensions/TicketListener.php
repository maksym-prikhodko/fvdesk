<?php
abstract class PHPUnit_Extensions_TicketListener implements PHPUnit_Framework_TestListener
{
    protected $ticketCounts = array();
    protected $ran = false;
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof PHPUnit_Framework_Warning) {
            if ($this->ran) {
                return;
            }
            $name    = $test->getName(false);
            $tickets = PHPUnit_Util_Test::getTickets(get_class($test), $name);
            foreach ($tickets as $ticket) {
                $this->ticketCounts[$ticket][$name] = 1;
            }
            $this->ran = true;
        }
    }
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if (!$test instanceof PHPUnit_Framework_Warning) {
            if ($test->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                $ifStatus   = array('assigned', 'new', 'reopened');
                $newStatus  = 'closed';
                $message    = 'Automatically closed by PHPUnit (test passed).';
                $resolution = 'fixed';
                $cumulative = true;
            } elseif ($test->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
                $ifStatus   = array('closed');
                $newStatus  = 'reopened';
                $message    = 'Automatically reopened by PHPUnit (test failed).';
                $resolution = '';
                $cumulative = false;
            } else {
                return;
            }
            $name    = $test->getName(false);
            $tickets = PHPUnit_Util_Test::getTickets(get_class($test), $name);
            foreach ($tickets as $ticket) {
                if ($test->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                    unset($this->ticketCounts[$ticket][$name]);
                }
                if ($cumulative) {
                    if (count($this->ticketCounts[$ticket]) > 0) {
                        $adjustTicket = false;
                    } else {
                        $adjustTicket = true;
                    }
                } else {
                    $adjustTicket = true;
                }
                $ticketInfo = $this->getTicketInfo($ticket);
                if ($adjustTicket && in_array($ticketInfo['status'], $ifStatus)) {
                    $this->updateTicket($ticket, $newStatus, $message, $resolution);
                }
            }
        }
    }
    abstract protected function getTicketInfo($ticketId = null);
    abstract protected function updateTicket($ticketId, $newStatus, $message, $resolution);
}
