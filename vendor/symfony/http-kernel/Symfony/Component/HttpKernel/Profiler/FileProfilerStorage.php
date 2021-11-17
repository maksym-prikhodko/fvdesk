<?php
namespace Symfony\Component\HttpKernel\Profiler;
class FileProfilerStorage implements ProfilerStorageInterface
{
    private $folder;
    public function __construct($dsn)
    {
        if (0 !== strpos($dsn, 'file:')) {
            throw new \RuntimeException(sprintf('Please check your configuration. You are trying to use FileStorage with an invalid dsn "%s". The expected format is "file:/path/to/the/storage/folder".', $dsn));
        }
        $this->folder = substr($dsn, 5);
        if (!is_dir($this->folder)) {
            mkdir($this->folder, 0777, true);
        }
    }
    public function find($ip, $url, $limit, $method, $start = null, $end = null)
    {
        $file = $this->getIndexFilename();
        if (!file_exists($file)) {
            return array();
        }
        $file = fopen($file, 'r');
        fseek($file, 0, SEEK_END);
        $result = array();
        while (count($result) < $limit && $line = $this->readLineFromFile($file)) {
            list($csvToken, $csvIp, $csvMethod, $csvUrl, $csvTime, $csvParent) = str_getcsv($line);
            $csvTime = (int) $csvTime;
            if ($ip && false === strpos($csvIp, $ip) || $url && false === strpos($csvUrl, $url) || $method && false === strpos($csvMethod, $method)) {
                continue;
            }
            if (!empty($start) && $csvTime < $start) {
                continue;
            }
            if (!empty($end) && $csvTime > $end) {
                continue;
            }
            $result[$csvToken] = array(
                'token' => $csvToken,
                'ip' => $csvIp,
                'method' => $csvMethod,
                'url' => $csvUrl,
                'time' => $csvTime,
                'parent' => $csvParent,
            );
        }
        fclose($file);
        return array_values($result);
    }
    public function purge()
    {
        $flags = \FilesystemIterator::SKIP_DOTS;
        $iterator = new \RecursiveDirectoryIterator($this->folder, $flags);
        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            if (is_file($file)) {
                unlink($file);
            } else {
                rmdir($file);
            }
        }
    }
    public function read($token)
    {
        if (!$token || !file_exists($file = $this->getFilename($token))) {
            return;
        }
        return $this->createProfileFromData($token, unserialize(file_get_contents($file)));
    }
    public function write(Profile $profile)
    {
        $file = $this->getFilename($profile->getToken());
        $profileIndexed = is_file($file);
        if (!$profileIndexed) {
            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        $data = array(
            'token' => $profile->getToken(),
            'parent' => $profile->getParentToken(),
            'children' => array_map(function ($p) { return $p->getToken(); }, $profile->getChildren()),
            'data' => $profile->getCollectors(),
            'ip' => $profile->getIp(),
            'method' => $profile->getMethod(),
            'url' => $profile->getUrl(),
            'time' => $profile->getTime(),
        );
        if (false === file_put_contents($file, serialize($data))) {
            return false;
        }
        if (!$profileIndexed) {
            if (false === $file = fopen($this->getIndexFilename(), 'a')) {
                return false;
            }
            fputcsv($file, array(
                $profile->getToken(),
                $profile->getIp(),
                $profile->getMethod(),
                $profile->getUrl(),
                $profile->getTime(),
                $profile->getParentToken(),
            ));
            fclose($file);
        }
        return true;
    }
    protected function getFilename($token)
    {
        $folderA = substr($token, -2, 2);
        $folderB = substr($token, -4, 2);
        return $this->folder.'/'.$folderA.'/'.$folderB.'/'.$token;
    }
    protected function getIndexFilename()
    {
        return $this->folder.'/index.csv';
    }
    protected function readLineFromFile($file)
    {
        $line = '';
        $position = ftell($file);
        if (0 === $position) {
            return;
        }
        while (true) {
            $chunkSize = min($position, 1024);
            $position -= $chunkSize;
            fseek($file, $position);
            if (0 === $chunkSize) {
                break;
            }
            $buffer = fread($file, $chunkSize);
            if (false === ($upTo = strrpos($buffer, "\n"))) {
                $line = $buffer.$line;
                continue;
            }
            $position += $upTo;
            $line = substr($buffer, $upTo + 1).$line;
            fseek($file, max(0, $position), SEEK_SET);
            if ('' !== $line) {
                break;
            }
        }
        return '' === $line ? null : $line;
    }
    protected function createProfileFromData($token, $data, $parent = null)
    {
        $profile = new Profile($token);
        $profile->setIp($data['ip']);
        $profile->setMethod($data['method']);
        $profile->setUrl($data['url']);
        $profile->setTime($data['time']);
        $profile->setCollectors($data['data']);
        if (!$parent && $data['parent']) {
            $parent = $this->read($data['parent']);
        }
        if ($parent) {
            $profile->setParent($parent);
        }
        foreach ($data['children'] as $token) {
            if (!$token || !file_exists($file = $this->getFilename($token))) {
                continue;
            }
            $profile->addChild($this->createProfileFromData($token, unserialize(file_get_contents($file)), $profile));
        }
        return $profile;
    }
}
