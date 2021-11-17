<?php
namespace Psy\Presenter;
class MongoCursorPresenter extends ObjectPresenter
{
    private static $boringFields = array('limit', 'batchSize', 'skip', 'flags');
    private static $ignoreFields = array('server', 'host', 'port', 'connection_type_desc');
    public function canPresent($value)
    {
        return $value instanceof \MongoCursor;
    }
    protected function getProperties($value, \ReflectionClass $class, $propertyFilter)
    {
        $info = $value->info();
        $this->normalizeQueryArray($info);
        $this->normalizeFieldsArray($info);
        $this->unsetBoringFields($info);
        $this->unsetIgnoredFields($info);
        if ($value->dead()) {
            $info['dead'] = true;
        }
        return array_merge(
            $info,
            parent::getProperties($value, $class, $propertyFilter)
        );
    }
    private function normalizeQueryArray(array &$info)
    {
        if (isset($info['query'])) {
            if ($info['query'] === new \StdClass()) {
                $info['query'] = array();
            } elseif (is_array($info['query']) && isset($info['query']['$query'])) {
                if ($info['query']['$query'] === new \StdClass()) {
                    $info['query']['$query'] = array();
                }
            }
        }
    }
    private function normalizeFieldsArray(array &$info)
    {
        if (isset($info['fields']) && $info['fields'] === new \StdClass()) {
            $info['fields'] = array();
        }
    }
    private function unsetBoringFields(array &$info)
    {
        foreach (self::$boringFields as $boring) {
            if ($info[$boring] === 0) {
                unset($info[$boring]);
            }
        }
    }
    private function unsetIgnoredFields(array &$info)
    {
        foreach (self::$ignoreFields as $ignore) {
            if (isset($info[$ignore])) {
                unset($info[$ignore]);
            }
        }
    }
}
