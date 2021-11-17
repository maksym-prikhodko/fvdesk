<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
class NativeFileSessionHandler extends NativeSessionHandler
{
    public function __construct($savePath = null)
    {
        if (null === $savePath) {
            $savePath = ini_get('session.save_path');
        }
        $baseDir = $savePath;
        if ($count = substr_count($savePath, ';')) {
            if ($count > 2) {
                throw new \InvalidArgumentException(sprintf('Invalid argument $savePath \'%s\'', $savePath));
            }
            $baseDir = ltrim(strrchr($savePath, ';'), ';');
        }
        if ($baseDir && !is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        ini_set('session.save_path', $savePath);
        ini_set('session.save_handler', 'files');
    }
}
