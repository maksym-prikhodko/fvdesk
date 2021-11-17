<?php
namespace SebastianBergmann\Diff;
use SebastianBergmann\Diff\LCS\LongestCommonSubsequence;
use SebastianBergmann\Diff\LCS\TimeEfficientImplementation;
use SebastianBergmann\Diff\LCS\MemoryEfficientImplementation;
class Differ
{
    private $header;
    public function __construct($header = "--- Original\n+++ New\n")
    {
        $this->header = $header;
    }
    public function diff($from, $to, LongestCommonSubsequence $lcs = null)
    {
        if (!is_array($from) && !is_string($from)) {
            $from = (string) $from;
        }
        if (!is_array($to) && !is_string($to)) {
            $to = (string) $to;
        }
        $buffer = $this->header;
        $diff   = $this->diffToArray($from, $to, $lcs);
        $inOld = false;
        $i     = 0;
        $old   = array();
        foreach ($diff as $line) {
            if ($line[1] ===  0 ) {
                if ($inOld === false) {
                    $inOld = $i;
                }
            } elseif ($inOld !== false) {
                if (($i - $inOld) > 5) {
                    $old[$inOld] = $i - 1;
                }
                $inOld = false;
            }
            ++$i;
        }
        $start = isset($old[0]) ? $old[0] : 0;
        $end   = count($diff);
        if ($tmp = array_search($end, $old)) {
            $end = $tmp;
        }
        $newChunk = true;
        for ($i = $start; $i < $end; $i++) {
            if (isset($old[$i])) {
                $buffer  .= "\n";
                $newChunk = true;
                $i        = $old[$i];
            }
            if ($newChunk) {
                $buffer  .= "@@ @@\n";
                $newChunk = false;
            }
            if ($diff[$i][1] === 1 ) {
                $buffer .= '+' . $diff[$i][0] . "\n";
            } elseif ($diff[$i][1] === 2 ) {
                $buffer .= '-' . $diff[$i][0] . "\n";
            } else {
                $buffer .= ' ' . $diff[$i][0] . "\n";
            }
        }
        return $buffer;
    }
    public function diffToArray($from, $to, LongestCommonSubsequence $lcs = null)
    {
        preg_match_all('(\r\n|\r|\n)', $from, $fromMatches);
        preg_match_all('(\r\n|\r|\n)', $to, $toMatches);
        if (is_string($from)) {
            $from = preg_split('(\r\n|\r|\n)', $from);
        }
        if (is_string($to)) {
            $to = preg_split('(\r\n|\r|\n)', $to);
        }
        $start      = array();
        $end        = array();
        $fromLength = count($from);
        $toLength   = count($to);
        $length     = min($fromLength, $toLength);
        for ($i = 0; $i < $length; ++$i) {
            if ($from[$i] === $to[$i]) {
                $start[] = $from[$i];
                unset($from[$i], $to[$i]);
            } else {
                break;
            }
        }
        $length -= $i;
        for ($i = 1; $i < $length; ++$i) {
            if ($from[$fromLength - $i] === $to[$toLength - $i]) {
                array_unshift($end, $from[$fromLength - $i]);
                unset($from[$fromLength - $i], $to[$toLength - $i]);
            } else {
                break;
            }
        }
        if ($lcs === null) {
            $lcs = $this->selectLcsImplementation($from, $to);
        }
        $common = $lcs->calculate(array_values($from), array_values($to));
        $diff   = array();
        if (isset($fromMatches[0]) && $toMatches[0] &&
            count($fromMatches[0]) === count($toMatches[0]) &&
            $fromMatches[0] !== $toMatches[0]) {
            $diff[] = array(
              '#Warning: Strings contain different line endings!', 0
            );
        }
        foreach ($start as $token) {
            $diff[] = array($token, 0 );
        }
        reset($from);
        reset($to);
        foreach ($common as $token) {
            while ((($fromToken = reset($from)) !== $token)) {
                $diff[] = array(array_shift($from), 2 );
            }
            while ((($toToken = reset($to)) !== $token)) {
                $diff[] = array(array_shift($to), 1 );
            }
            $diff[] = array($token, 0 );
            array_shift($from);
            array_shift($to);
        }
        while (($token = array_shift($from)) !== null) {
            $diff[] = array($token, 2 );
        }
        while (($token = array_shift($to)) !== null) {
            $diff[] = array($token, 1 );
        }
        foreach ($end as $token) {
            $diff[] = array($token, 0 );
        }
        return $diff;
    }
    private function selectLcsImplementation(array $from, array $to)
    {
        $memoryLimit = 100 * 1024 * 1024;
        if ($this->calculateEstimatedFootprint($from, $to) > $memoryLimit) {
            return new MemoryEfficientImplementation;
        }
        return new TimeEfficientImplementation;
    }
    private function calculateEstimatedFootprint(array $from, array $to)
    {
        $itemSize = PHP_INT_SIZE == 4 ? 76 : 144;
        return $itemSize * pow(min(count($from), count($to)), 2);
    }
}
