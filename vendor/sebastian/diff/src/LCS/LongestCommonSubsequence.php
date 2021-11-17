<?php
namespace SebastianBergmann\Diff\LCS;
interface LongestCommonSubsequence
{
    public function calculate(array $from, array $to);
}
