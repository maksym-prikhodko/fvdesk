<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
class PdoSessionHandler implements \SessionHandlerInterface
{
    const LOCK_NONE = 0;
    const LOCK_ADVISORY = 1;
    const LOCK_TRANSACTIONAL = 2;
    private $pdo;
    private $dsn = false;
    private $driver;
    private $table = 'sessions';
    private $idCol = 'sess_id';
    private $dataCol = 'sess_data';
    private $lifetimeCol = 'sess_lifetime';
    private $timeCol = 'sess_time';
    private $username = '';
    private $password = '';
    private $connectionOptions = array();
    private $lockMode = self::LOCK_TRANSACTIONAL;
    private $unlockStatements = array();
    private $sessionExpired = false;
    private $inTransaction = false;
    private $gcCalled = false;
    public function __construct($pdoOrDsn = null, array $options = array())
    {
        if ($pdoOrDsn instanceof \PDO) {
            if (\PDO::ERRMODE_EXCEPTION !== $pdoOrDsn->getAttribute(\PDO::ATTR_ERRMODE)) {
                throw new \InvalidArgumentException(sprintf('"%s" requires PDO error mode attribute be set to throw Exceptions (i.e. $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION))', __CLASS__));
            }
            $this->pdo = $pdoOrDsn;
            $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } else {
            $this->dsn = $pdoOrDsn;
        }
        $this->table = isset($options['db_table']) ? $options['db_table'] : $this->table;
        $this->idCol = isset($options['db_id_col']) ? $options['db_id_col'] : $this->idCol;
        $this->dataCol = isset($options['db_data_col']) ? $options['db_data_col'] : $this->dataCol;
        $this->lifetimeCol = isset($options['db_lifetime_col']) ? $options['db_lifetime_col'] : $this->lifetimeCol;
        $this->timeCol = isset($options['db_time_col']) ? $options['db_time_col'] : $this->timeCol;
        $this->username = isset($options['db_username']) ? $options['db_username'] : $this->username;
        $this->password = isset($options['db_password']) ? $options['db_password'] : $this->password;
        $this->connectionOptions = isset($options['db_connection_options']) ? $options['db_connection_options'] : $this->connectionOptions;
        $this->lockMode = isset($options['lock_mode']) ? $options['lock_mode'] : $this->lockMode;
    }
    public function createTable()
    {
        $this->getConnection();
        switch ($this->driver) {
            case 'mysql':
                $sql = "CREATE TABLE $this->table ($this->idCol VARBINARY(128) NOT NULL PRIMARY KEY, $this->dataCol BLOB NOT NULL, $this->lifetimeCol MEDIUMINT NOT NULL, $this->timeCol INTEGER UNSIGNED NOT NULL) COLLATE utf8_bin, ENGINE = InnoDB";
                break;
            case 'sqlite':
                $sql = "CREATE TABLE $this->table ($this->idCol TEXT NOT NULL PRIMARY KEY, $this->dataCol BLOB NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";
                break;
            case 'pgsql':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR(128) NOT NULL PRIMARY KEY, $this->dataCol BYTEA NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";
                break;
            case 'oci':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR2(128) NOT NULL PRIMARY KEY, $this->dataCol BLOB NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";
                break;
            case 'sqlsrv':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR(128) NOT NULL PRIMARY KEY, $this->dataCol VARBINARY(MAX) NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";
                break;
            default:
                throw new \DomainException(sprintf('Creating the session table is currently not implemented for PDO driver "%s".', $this->driver));
        }
        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
    }
    public function isSessionExpired()
    {
        return $this->sessionExpired;
    }
    public function open($savePath, $sessionName)
    {
        if (null === $this->pdo) {
            $this->connect($this->dsn ?: $savePath);
        }
        return true;
    }
    public function read($sessionId)
    {
        try {
            return $this->doRead($sessionId);
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
    }
    public function gc($maxlifetime)
    {
        $this->gcCalled = true;
        return true;
    }
    public function destroy($sessionId)
    {
        $sql = "DELETE FROM $this->table WHERE $this->idCol = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
        return true;
    }
    public function write($sessionId, $data)
    {
        $maxlifetime = (int) ini_get('session.gc_maxlifetime');
        try {
            $mergeSql = $this->getMergeSql();
            if (null !== $mergeSql) {
                $mergeStmt = $this->pdo->prepare($mergeSql);
                $mergeStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                $mergeStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
                $mergeStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
                $mergeStmt->bindValue(':time', time(), \PDO::PARAM_INT);
                $mergeStmt->execute();
                return true;
            }
            $updateStmt = $this->pdo->prepare(
                "UPDATE $this->table SET $this->dataCol = :data, $this->lifetimeCol = :lifetime, $this->timeCol = :time WHERE $this->idCol = :id"
            );
            $updateStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $updateStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
            $updateStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
            $updateStmt->bindValue(':time', time(), \PDO::PARAM_INT);
            $updateStmt->execute();
            if (!$updateStmt->rowCount()) {
                try {
                    $insertStmt = $this->pdo->prepare(
                        "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)"
                    );
                    $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                    $insertStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
                    $insertStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
                    $insertStmt->bindValue(':time', time(), \PDO::PARAM_INT);
                    $insertStmt->execute();
                } catch (\PDOException $e) {
                    if (0 === strpos($e->getCode(), '23')) {
                        $updateStmt->execute();
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
        return true;
    }
    public function close()
    {
        $this->commit();
        while ($unlockStmt = array_shift($this->unlockStatements)) {
            $unlockStmt->execute();
        }
        if ($this->gcCalled) {
            $this->gcCalled = false;
            $sql = "DELETE FROM $this->table WHERE $this->lifetimeCol + $this->timeCol < :time";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':time', time(), \PDO::PARAM_INT);
            $stmt->execute();
        }
        if (false !== $this->dsn) {
            $this->pdo = null; 
        }
        return true;
    }
    private function connect($dsn)
    {
        $this->pdo = new \PDO($dsn, $this->username, $this->password, $this->connectionOptions);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }
    private function beginTransaction()
    {
        if (!$this->inTransaction) {
            if ('sqlite' === $this->driver) {
                $this->pdo->exec('BEGIN IMMEDIATE TRANSACTION');
            } else {
                if ('mysql' === $this->driver) {
                    $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                }
                $this->pdo->beginTransaction();
            }
            $this->inTransaction = true;
        }
    }
    private function commit()
    {
        if ($this->inTransaction) {
            try {
                if ('sqlite' === $this->driver) {
                    $this->pdo->exec('COMMIT');
                } else {
                    $this->pdo->commit();
                }
                $this->inTransaction = false;
            } catch (\PDOException $e) {
                $this->rollback();
                throw $e;
            }
        }
    }
    private function rollback()
    {
        if ($this->inTransaction) {
            if ('sqlite' === $this->driver) {
                $this->pdo->exec('ROLLBACK');
            } else {
                $this->pdo->rollback();
            }
            $this->inTransaction = false;
        }
    }
    private function doRead($sessionId)
    {
        $this->sessionExpired = false;
        if (self::LOCK_ADVISORY === $this->lockMode) {
            $this->unlockStatements[] = $this->doAdvisoryLock($sessionId);
        }
        $selectSql = $this->getSelectSql();
        $selectStmt = $this->pdo->prepare($selectSql);
        $selectStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        $selectStmt->execute();
        $sessionRows = $selectStmt->fetchAll(\PDO::FETCH_NUM);
        if ($sessionRows) {
            if ($sessionRows[0][1] + $sessionRows[0][2] < time()) {
                $this->sessionExpired = true;
                return '';
            }
            return is_resource($sessionRows[0][0]) ? stream_get_contents($sessionRows[0][0]) : $sessionRows[0][0];
        }
        if (self::LOCK_TRANSACTIONAL === $this->lockMode && 'sqlite' !== $this->driver) {
            try {
                $insertStmt = $this->pdo->prepare(
                    "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)"
                );
                $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                $insertStmt->bindValue(':data', '', \PDO::PARAM_LOB);
                $insertStmt->bindValue(':lifetime', 0, \PDO::PARAM_INT);
                $insertStmt->bindValue(':time', time(), \PDO::PARAM_INT);
                $insertStmt->execute();
            } catch (\PDOException $e) {
                if (0 === strpos($e->getCode(), '23')) {
                    $selectStmt->execute();
                    $sessionRows = $selectStmt->fetchAll(\PDO::FETCH_NUM);
                    if ($sessionRows) {
                        return is_resource($sessionRows[0][0]) ? stream_get_contents($sessionRows[0][0]) : $sessionRows[0][0];
                    }
                    return '';
                }
                throw $e;
            }
        }
        return '';
    }
    private function doAdvisoryLock($sessionId)
    {
        switch ($this->driver) {
            case 'mysql':
                $stmt = $this->pdo->prepare('SELECT GET_LOCK(:key, 50)');
                $stmt->bindValue(':key', $sessionId, \PDO::PARAM_STR);
                $stmt->execute();
                $releaseStmt = $this->pdo->prepare('DO RELEASE_LOCK(:key)');
                $releaseStmt->bindValue(':key', $sessionId, \PDO::PARAM_STR);
                return $releaseStmt;
            case 'pgsql':
                if (4 === PHP_INT_SIZE) {
                    $sessionInt1 = hexdec(substr($sessionId, 0, 7));
                    $sessionInt2 = hexdec(substr($sessionId, 7, 7));
                    $stmt = $this->pdo->prepare('SELECT pg_advisory_lock(:key1, :key2)');
                    $stmt->bindValue(':key1', $sessionInt1, \PDO::PARAM_INT);
                    $stmt->bindValue(':key2', $sessionInt2, \PDO::PARAM_INT);
                    $stmt->execute();
                    $releaseStmt = $this->pdo->prepare('SELECT pg_advisory_unlock(:key1, :key2)');
                    $releaseStmt->bindValue(':key1', $sessionInt1, \PDO::PARAM_INT);
                    $releaseStmt->bindValue(':key2', $sessionInt2, \PDO::PARAM_INT);
                } else {
                    $sessionBigInt = hexdec(substr($sessionId, 0, 15));
                    $stmt = $this->pdo->prepare('SELECT pg_advisory_lock(:key)');
                    $stmt->bindValue(':key', $sessionBigInt, \PDO::PARAM_INT);
                    $stmt->execute();
                    $releaseStmt = $this->pdo->prepare('SELECT pg_advisory_unlock(:key)');
                    $releaseStmt->bindValue(':key', $sessionBigInt, \PDO::PARAM_INT);
                }
                return $releaseStmt;
            case 'sqlite':
                throw new \DomainException('SQLite does not support advisory locks.');
            default:
                throw new \DomainException(sprintf('Advisory locks are currently not implemented for PDO driver "%s".', $this->driver));
        }
    }
    private function getSelectSql()
    {
        if (self::LOCK_TRANSACTIONAL === $this->lockMode) {
            $this->beginTransaction();
            switch ($this->driver) {
                case 'mysql':
                case 'oci':
                case 'pgsql':
                    return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WHERE $this->idCol = :id FOR UPDATE";
                case 'sqlsrv':
                    return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WITH (UPDLOCK, ROWLOCK) WHERE $this->idCol = :id";
                case 'sqlite':
                    break;
                default:
                    throw new \DomainException(sprintf('Transactional locks are currently not implemented for PDO driver "%s".', $this->driver));
            }
        }
        return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WHERE $this->idCol = :id";
    }
    private function getMergeSql()
    {
        switch ($this->driver) {
            case 'mysql':
                return "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time) ".
                    "ON DUPLICATE KEY UPDATE $this->dataCol = VALUES($this->dataCol), $this->lifetimeCol = VALUES($this->lifetimeCol), $this->timeCol = VALUES($this->timeCol)";
            case 'oci':
                return "MERGE INTO $this->table USING DUAL ON ($this->idCol = :id) ".
                    "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time) ".
                    "WHEN MATCHED THEN UPDATE SET $this->dataCol = :data, $this->lifetimeCol = :lifetime, $this->timeCol = :time";
            case 'sqlsrv' === $this->driver && version_compare($this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '10', '>='):
                return "MERGE INTO $this->table WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ($this->idCol = :id) ".
                    "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time) ".
                    "WHEN MATCHED THEN UPDATE SET $this->dataCol = :data, $this->lifetimeCol = :lifetime, $this->timeCol = :time;";
            case 'sqlite':
                return "INSERT OR REPLACE INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)";
        }
    }
    protected function getConnection()
    {
        if (null === $this->pdo) {
            $this->connect($this->dsn ?: ini_get('session.save_path'));
        }
        return $this->pdo;
    }
}
