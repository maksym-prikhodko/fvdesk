<?php
namespace Psy\ExecutionLoop;
use Psy\Shell;
class ForkingLoop extends Loop
{
    private $savegame;
    public function run(Shell $shell)
    {
        list($up, $down) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if (!$up) {
            throw new \RuntimeException('Unable to create socket pair.');
        }
        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException('Unable to start execution loop.');
        } elseif ($pid > 0) {
            fclose($up);
            $read   = array($down);
            $write  = null;
            $except = null;
            if (stream_select($read, $write, $except, null) === false) {
                throw new \RuntimeException('Error waiting for execution loop.');
            }
            $content = stream_get_contents($down);
            fclose($down);
            if ($content) {
                $shell->setScopeVariables(@unserialize($content));
            }
            return;
        }
        if (function_exists('setproctitle')) {
            setproctitle('psysh (loop)');
        }
        fclose($down);
        parent::run($shell);
        fwrite($up, $this->serializeReturn($shell->getScopeVariables()));
        fclose($up);
        exit;
    }
    public function beforeLoop()
    {
        $this->createSavegame();
    }
    public function afterLoop()
    {
        if (isset($this->savegame)) {
            posix_kill($this->savegame, SIGKILL);
            pcntl_signal_dispatch();
        }
    }
    private function createSavegame()
    {
        $this->savegame = posix_getpid();
        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException('Unable to create savegame fork.');
        } elseif ($pid > 0) {
            pcntl_waitpid($pid, $status);
            if (!pcntl_wexitstatus($status)) {
                posix_kill(posix_getpid(), SIGKILL);
            }
            $this->createSavegame();
        }
    }
    private function serializeReturn(array $return)
    {
        $serializable = array();
        foreach ($return as $key => $value) {
            try {
                serialize($value);
                $serializable[$key] = $value;
            } catch (\Exception $e) {
            }
        }
        return serialize($serializable);
    }
}
