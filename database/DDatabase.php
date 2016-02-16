<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database;

use app\decibel\adapter\DAdaptable;
use app\decibel\adapter\DAdapterCache;
use app\decibel\database\debug\DDatabaseConnectionException;
use app\decibel\database\debug\DDatabaseSelectionException;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\mysql\DMySQL;
use app\decibel\debug\DDebuggable;
use app\decibel\debug\DProfiler;
use app\decibel\decorator\DDecoratable;
use app\decibel\decorator\DDecoratorCache;
use app\decibel\utility\DBaseClass;

/**
 * Provides base functionality for establishing an interface with an RDBMS.
 *
 * This class can be extended to allow Decibel to directly access any
 * type of RDBMS system through the {@link app::decibel::database::DQuery DQuery} class.
 *
 * See the @ref database Developer Guide for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database
 */
abstract class DDatabase implements DAdaptable, DDebuggable, DDecoratable
{
    use DAdapterCache;
    use DBaseClass;
    use DDecoratorCache;

    /**
     * The database connection resource.
     *
     * @var        resource
     */
    protected $connection;

    /**
     * The name of the database to be used, or the DSN for ODBC connections.
     *
     * @var        string
     */
    protected $dbname;

    /**
     * The hostname of the database server.
     *
     * @var        string
     */
    protected $hostname;

    /**
     * The password to log into the database with.
     *
     * @var        string
     */
    protected $password;

    /**
     * The username to log into the database with.
     *
     * @var        string
     */
    protected $username;

    /**
     * The database controller for this application.
     *
     * @var    DDatabase
     */
    private static $database;

    /**
     * Creates a new database controller.
     *
     * The constructor does not initiate a connection to the database,
     * {@link DDatabase::connect} must be called to do this.
     *
     * @param    string $username The username to log into the database with.
     * @param    string $password The password to log into the database with.
     * @param    string $dbname   The name of the database to be used, or the DSN for ODBC connections.
     * @param    string $hostname The hostname of the database server.
     *
     * @return    static
     */
    public function __construct($username, $password, $dbname, $hostname = '')
    {
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->hostname = $hostname;
    }

    /**
     * Closes the database connection and cleans up any resources.
     *
     * @return    void
     */
    public function __destruct()
    {
        // Close the connection.
        $this->close();
    }

    /**
     * Closes the database connection.
     *
     * This function is called automatically by the destructor.
     *
     * @return    void
     */
    abstract public function close();

    /**
     * Opens a connection to the database.
     *
     * This function is called automatically by the constructor.
     *
     * @return    void
     * @throws    DDatabaseConnectionException    If database server could not
     *                                            be connected to.
     * @throws    DDatabaseSelectionException        If database could not be selected.
     */
    abstract public function connect();

    /**
     * Returns true if the database is connected, false otherwise.
     *
     * @return    bool
     */
    public function connected()
    {
        return (bool)$this->connection;
    }

    /**
     * Escapes data for insertion into a query.
     *
     * @param    string $data The data to escape.
     *
     * @return    string    The escaped data.
     */
    abstract public function escape($data);

    /**
     * Frees any memory associated with a database result resource.
     *
     * @param    resource $result The result set resource.
     *
     * @return    void
     */
    abstract public function freeResult($result);

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array(
            'hostname' => $this->hostname,
            'dbname'   => $this->dbname,
            'username' => $this->username,
            'password' => !empty($this->password),
        );
    }

    /**
     * Returns the number of rows affected by the previous query.
     *
     * @return    integer
     */
    abstract public function getAffectedRows();

    /**
     * Returns a connection to the application database.
     *
     * @return    DMySQL
     * @throws    DDatabaseConnectionException    If database server could not
     *                                            be connected to.
     */
    public static function getDatabase()
    {
        if (self::$database === null) {
            DProfiler::startProfiling('app\\decibel\\database\\DDatabase::connect');
            // Connect to the database.
            self::$database = new DMySQL(
                env('DB_USERNAME'),
                env('DB_PASSWORD'),
                env('DB_DATABASE'),
                env('DB_HOSTNAME')
            );
            self::$database->connect();
            DProfiler::stopProfiling('app\\decibel\\database\\DDatabase::connect');
        }

        return self::$database;
    }

    /**
     * Returns the name of the database being used.
     *
     * @return    string
     */
    public function getDatabaseName()
    {
        return $this->dbname;
    }

    /**
     * Returns the last error to occur for this database connection.
     *
     * @return    DDatabaseException    An exception object describing the error,
     *                                or <code>null</code> if no error occurred.
     */
    public function getError()
    {
        $code = $this->getErrorCode();
        if ($code) {
            $error = new DQueryExecutionException(
                $code,
                $this->getErrorMsg()
            );
        } else {
            $error = null;
        }

        return $error;
    }

    /**
     * Returns a code describing the last error produced by the database.
     *
     * @return    integer
     */
    abstract protected function getErrorCode();

    /**
     * Returns a message describing the last error produced by the database.
     *
     * @return    string
     */
    abstract protected function getErrorMsg();

    /**
     * Returns the hostname of the database server.
     *
     * @return    string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Returns the ID generated for an AUTO_INCREMENT column by the
     * previous INSERT query.
     *
     * @return    integer
     */
    abstract public function getInsertId();

    /**
     * Return the next row of results from the query.
     *
     * @param    resource $result The result set resource.
     *
     * @return    array
     */
    abstract public function getNextRow($result);

    /**
     * Return the number of rows in a result set.
     *
     * @param    resource $result The result set resource.
     *
     * @return    integer
     */
    abstract public function getNumRows($result);

    /**
     * Returns the username used to connect to the database server.
     *
     * @return    string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Queries the database with the given statement.
     *
     * @param    string $statement The statement to query the database with.
     *
     * @return    resource    The result set resource, or <code>null</code> on failure.
     */
    abstract public function query($statement);
}
