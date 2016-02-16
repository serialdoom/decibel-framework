<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mysql;

use app\decibel\cache\DCache;
use app\decibel\database\DDatabase;
use app\decibel\database\debug\DDatabaseConnectionException;
use app\decibel\database\debug\DDatabaseSelectionException;
use app\decibel\database\debug\DPacketSizeException;
use mysqli;

/**
 * Database controller for the MySQL database.
 *
 * See the @ref database Developer Guide for further information.
 *
 * @section    versioning Version Control
 *
 * @author     Timothy de Paris
 * @ingroup    database_controllers
 */
class DMySQL extends DDatabase
{
    /**
     * Fulltext index type.
     *
     * @var        string
     */
    const INDEX_TYPE_FULLTEXT = 'FULLTEXT';

    /**
     * Primary key index type.
     *
     * @var        string
     */
    const INDEX_TYPE_PRIMARY = 'PRIMARY';

    /**
     * Standard index type.
     *
     * @var        string
     */
    const INDEX_TYPE_STANDARD = 'INDEX';

    /**
     * Unique index type.
     *
     * @var        string
     */
    const INDEX_TYPE_UNIQUE = 'UNIQUE';

    /**
     * Mapping of MySQL error codes to their applicable {@link DDatabaseException} class.
     *
     * @var        array
     * @see        https://dev.mysql.com/doc/refman/5.5/en/error-messages-client.html
     */
    private static $errorCodes = array(
        1049 => 'app\\decibel\\database\\debug\\DDatabaseSelectionException',
        1062 => 'app\\decibel\\database\\debug\\DDuplicateRowException',
        1064 => 'app\\decibel\\database\\debug\\DQuerySyntaxException',
        1116 => 'app\\decibel\\database\\debug\\DTooManyJoinsException',
        1146 => 'app\\decibel\\database\\debug\\DUnknownTableException',
        2002 => 'app\\decibel\\database\\debug\\DInvalidHostnameException',
        // Connection refused.
        2003 => 'app\\decibel\\database\\debug\\DInvalidHostnameException',
    );

    /**
     * The size of queries (in bytes) that will be accepted by the server.
     *
     * @var        int
     */
    protected $maxPacketSize;

    /**
     * Closes the database connection.
     *
     * This function is called automatically by the destructor.
     *
     * @return    void
     */
    public function close()
    {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    /**
     * Opens a connection to the database.
     *
     * @throws    DDatabaseConnectionException    If database server could not be connected to.
     * @throws    DDatabaseSelectionException        If database could not be selected.
     * @return    void
     */
    public function connect()
    {
        // Connect to database server.
        $this->connection = new mysqli(
            $this->hostname,
            $this->username,
            $this->password,
            $this->dbname
        );
        // Convert any connection errors into an exception.
        $this->handleConnectError($this->connection->connect_errno);
        // Set the connection collation.
        if ($this->connection->character_set_name() !== 'utf8') {
            $this->connection->set_charset('utf8');
        }
        // Store max allowed packet size.
        $memoryCache = DCache::load();
        $key = get_class() . '_maxPacketSize';
        $this->maxPacketSize = $memoryCache->get($key);
        if ($this->maxPacketSize === null) {
            // Query database for max packet size.
            $result = $this->connection->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
            $maxPacketSizeRow = $result->fetch_assoc();
            $result->close();
            // Store and cache the returned value.
            $this->maxPacketSize = (int)$maxPacketSizeRow['Value'];
            $memoryCache->set($key, $this->maxPacketSize);
        }
    }

    /**
     * Handles error codes returned when connecting to the database.
     *
     * @param    int $errorCode Returned database error code.
     *
     * @return    void
     * @throws    DDatabaseConnectionException
     */
    protected function handleConnectError($errorCode)
    {
        if ($errorCode === 0) {
            return;
        }
        if (array_key_exists($errorCode, self::$errorCodes)) {
            $exception = self::$errorCodes[ $errorCode ];
        } else {
            $exception = 'app\\decibel\\database\\DDatabaseConnectionException';
        }
        $this->connection = null;
        throw new $exception($this);
    }

    /************************************************************************/
    /*																		*/
    /* Querying Functions													*/
    /*																		*/
    /************************************************************************/
    /**
     * Escapes data for insertion into a query.
     *
     * @param    string $data The data to escape.
     *
     * @return    string    The escaped data.
     */
    public function escape($data)
    {
        if (!$this->connection) {
            $data = null;
        } else {
            $data = $this->connection->real_escape_string($data);
        }

        return $data;
    }

    /**
     * Frees any memory associated with a database result resource.
     *
     * @param    resource $result The result set resource.
     *
     * @return    void
     */
    public function freeResult($result)
    {
        if (is_object($result)) {
            $result->close();
        }
    }

    /**
     * Returns the number of rows affected by the previous query.
     *
     * @return    int
     */
    public function getAffectedRows()
    {
        if (!$this->connection) {
            $affectedRows = null;
        } else {
            $affectedRows = $this->connection->affected_rows;
        }

        return $affectedRows;
    }

    /**
     * Returns the id generated for an auto_increment column by the
     * previous insert query.
     *
     * @return    int
     */
    public function getInsertId()
    {
        if (!$this->connection) {
            $insertId = null;
        } else {
            $insertId = $this->connection->insert_id;
        }

        return $insertId;
    }

    /**
     * Return the next row of results from the query.
     *
     * @param    resource $result The result set resource id.
     *
     * @return    mixed
     */
    public function getNextRow($result)
    {
        if (!is_object($result)) {
            $row = null;
        } else {
            $row = $result->fetch_assoc();
        }

        return $row;
    }

    /**
     * Return the number of rows in a result set.
     *
     * @param    resource $result The result set resource id.
     *
     * @return    int
     */
    public function getNumRows($result)
    {
        if (!$this->connection
            || !$result
        ) {
            $rows = null;
        } else {
            if ($result === true) {
                $rows = 0;
            } else {
                $rows = $result->num_rows;
            }
        }

        return $rows;
    }

    /**
     * Queries the database with the given statement.
     *
     * @param    string $statement The statement to query the database with.
     *
     * @return    resource    The result set resource, or <code>null</code> on failure.
     * @throws    DPacketSizeException    If the specified query exceeds
     *                                    the server's maximum packet size.
     */
    public function query($statement)
    {
        if (!$this->connection) {
            return null;
        }
        // Check maximum packet size won't be exceeded.
        // (If we know the maximum packet size).
        if ($this->maxPacketSize > 0
            && strlen($statement) > $this->maxPacketSize
        ) {
            throw new DPacketSizeException(
                $this->maxPacketSize,
                strlen($statement),
                $statement
            );
        }
        $result = $this->connection->query($statement);
        if ($result === false) {
            $result = null;
        }

        return $result;
    }

    /************************************************************************/
    /*																		*/
    /* Error Functions														*/
    /*																		*/
    /************************************************************************/
    /**
     * Returns the last error to occur for this database connection.
     *
     * @return    DDatabaseException    An exception object describing the error.
     *                                or <code>null</code> if no error occurred.
     */
    public function getError()
    {
        $code = $this->getErrorCode();
        if ($code === 0) {
            return null;
        }
        if (isset(self::$errorCodes[ $code ])) {
            $exceptionClass = self::$errorCodes[ $code ];
        } else {
            $exceptionClass = 'app\\decibel\\database\\debug\\DQueryExecutionException';
        }

        return new $exceptionClass(
            $this->getErrorCode(),
            $this->getErrorMsg()
        );
    }

    /**
     * Returns a code describing the  last error produced by the database.
     *
     * @return    int
     */
    protected function getErrorCode()
    {
        if (!$this->connection) {
            $errorCode = null;
        } else {
            $errorCode = $this->connection->errno;
        }

        return $errorCode;
    }

    /**
     * Returns a message describing the last error produced by the database.
     *
     * @return    string
     */
    protected function getErrorMsg()
    {
        if (!$this->connection) {
            $errorMsg = null;
        } else {
            $errorMsg = $this->connection->error;
        }

        return $errorMsg;
    }
}
