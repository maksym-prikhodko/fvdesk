<?php
namespace PhpSpec\Config;
class OptionsConfig
{
    private $stopOnFailureEnabled;
    private $codeGenerationEnabled;
    private $reRunEnabled;
    private $fakingEnabled;
    private $bootstrapPath;
    public function __construct(
        $stopOnFailureEnabled,
        $codeGenerationEnabled,
        $reRunEnabled,
        $fakingEnabled,
        $bootstrapPath
    ) {
        $this->stopOnFailureEnabled  = $stopOnFailureEnabled;
        $this->codeGenerationEnabled = $codeGenerationEnabled;
        $this->reRunEnabled = $reRunEnabled;
        $this->fakingEnabled = $fakingEnabled;
        $this->bootstrapPath = $bootstrapPath;
    }
    public function isStopOnFailureEnabled()
    {
        return $this->stopOnFailureEnabled;
    }
    public function isCodeGenerationEnabled()
    {
        return $this->codeGenerationEnabled;
    }
    public function isReRunEnabled()
    {
        return $this->reRunEnabled;
    }
    public function isFakingEnabled()
    {
        return $this->fakingEnabled;
    }
    public function getBootstrapPath()
    {
        return $this->bootstrapPath;
    }
}
