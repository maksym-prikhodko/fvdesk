<?php namespace SuperClosure\Analyzer;
use SuperClosure\Exception\ClosureAnalysisException;
class TokenAnalyzer extends ClosureAnalyzer
{
    public function determineCode(array &$data)
    {
        $this->determineTokens($data);
        $data['code'] = implode('', $data['tokens']);
        $data['hasThis'] = (strpos($data['code'], '$this') !== false);
    }
    private function determineTokens(array &$data)
    {
        $potential = $this->determinePotentialTokens($data['reflection']);
        $braceLevel = $index = $step = $insideUse = 0;
        $data['tokens'] = $data['context'] = [];
        foreach ($potential as $token) {
            $token = new Token($token);
            switch ($step) {
                case 0:
                    if ($token->is(T_FUNCTION)) {
                        $data['tokens'][] = $token;
                        $step++;
                    }
                    break;
                case 1:
                    $data['tokens'][] = $token;
                    if ($insideUse) {
                        if ($token->is(T_VARIABLE)) {
                            $varName = trim($token, '$ ');
                            $data['context'][$varName] = null;
                        } elseif ($token->is('&')) {
                            $data['hasRefs'] = true;
                        }
                    } elseif ($token->is(T_USE)) {
                        $insideUse++;
                    }
                    if ($token->is('{')) {
                        $step++;
                        $braceLevel++;
                    }
                    break;
                case 2:
                    $data['tokens'][] = $token;
                    if ($token->is('{')) {
                        $braceLevel++;
                    } elseif ($token->is('}')) {
                        $braceLevel--;
                        if ($braceLevel === 0) {
                            $step++;
                        }
                    }
                    break;
                case 3:
                    if ($token->is(T_FUNCTION)) {
                        throw new ClosureAnalysisException('Multiple closures '
                            . 'were declared on the same line of code. Could '
                            . 'determine which closure was the intended target.'
                        );
                    }
                    break;
            }
        }
    }
    private function determinePotentialTokens(\ReflectionFunction $reflection)
    {
        $fileName = $reflection->getFileName();
        if (!is_readable($fileName)) {
            throw new ClosureAnalysisException(
                "Cannot read the file containing the closure: \"{$fileName}\"."
            );
        }
        $code = '';
        $file = new \SplFileObject($fileName);
        $file->seek($reflection->getStartLine() - 1);
        while ($file->key() < $reflection->getEndLine()) {
            $code .= $file->current();
            $file->next();
        }
        $code = trim($code);
        if (strpos($code, '<?php') !== 0) {
            $code = "<?php\n" . $code;
        }
        return token_get_all($code);
    }
    protected function determineContext(array &$data)
    {
        $values = $data['reflection']->getStaticVariables();
        foreach ($data['context'] as $name => &$value) {
            if (isset($values[$name])) {
                $value = $values[$name];
            }
        }
    }
}
