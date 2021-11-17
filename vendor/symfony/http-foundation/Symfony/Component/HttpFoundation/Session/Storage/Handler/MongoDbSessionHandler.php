<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
class MongoDbSessionHandler implements \SessionHandlerInterface
{
    private $mongo;
    private $collection;
    private $options;
    public function __construct($mongo, array $options)
    {
        if (!($mongo instanceof \MongoClient || $mongo instanceof \Mongo)) {
            throw new \InvalidArgumentException('MongoClient or Mongo instance required');
        }
        if (!isset($options['database']) || !isset($options['collection'])) {
            throw new \InvalidArgumentException('You must provide the "database" and "collection" option for MongoDBSessionHandler');
        }
        $this->mongo = $mongo;
        $this->options = array_merge(array(
            'id_field' => '_id',
            'data_field' => 'data',
            'time_field' => 'time',
            'expiry_field' => 'expires_at',
        ), $options);
    }
    public function open($savePath, $sessionName)
    {
        return true;
    }
    public function close()
    {
        return true;
    }
    public function destroy($sessionId)
    {
        $this->getCollection()->remove(array(
            $this->options['id_field'] => $sessionId,
        ));
        return true;
    }
    public function gc($maxlifetime)
    {
        $this->getCollection()->remove(array(
            $this->options['expiry_field'] => array('$lt' => new \MongoDate()),
        ));
        return true;
    }
    public function write($sessionId, $data)
    {
        $expiry = new \MongoDate(time() + (int) ini_get('session.gc_maxlifetime'));
        $fields = array(
            $this->options['data_field'] => new \MongoBinData($data, \MongoBinData::BYTE_ARRAY),
            $this->options['time_field'] => new \MongoDate(),
            $this->options['expiry_field'] => $expiry,
        );
        $this->getCollection()->update(
            array($this->options['id_field'] => $sessionId),
            array('$set' => $fields),
            array('upsert' => true, 'multiple' => false)
        );
        return true;
    }
    public function read($sessionId)
    {
        $dbData = $this->getCollection()->findOne(array(
            $this->options['id_field']   => $sessionId,
            $this->options['expiry_field'] => array('$gte' => new \MongoDate()),
        ));
        return null === $dbData ? '' : $dbData[$this->options['data_field']]->bin;
    }
    private function getCollection()
    {
        if (null === $this->collection) {
            $this->collection = $this->mongo->selectCollection($this->options['database'], $this->options['collection']);
        }
        return $this->collection;
    }
    protected function getMongo()
    {
        return $this->mongo;
    }
}
