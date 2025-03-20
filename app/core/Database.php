<?php

/**
 * Class Database
 *
 * A simple Database class to handle database operations using PDO.
 * Connection parameters are loaded from configuration constants.
 */
class Database
{
    /**
     * Database host.
     *
     * @var string
     */
    private $host = DB_HOST;

    /**
     * Database username.
     *
     * @var string
     */
    private $user = DB_USER;

    /**
     * Database password.
     *
     * @var string
     */
    private $password = DB_PASS;

    /**
     * Database name.
     *
     * @var string
     */
    private $dbname = DB_NAME;

    /**
     * Database port.
     *
     * @var int|string
     */
    private $dbport = DB_PORT;

    /**
     * PDO database handler.
     *
     * @var PDO
     */
    private $dbh;

    /**
     * PDO statement.
     *
     * @var PDOStatement
     */
    private $stmt;

    /**
     * Error message, if any.
     *
     * @var string|null
     */
    private $error;

    /**
     * Database constructor.
     *
     * Initializes a PDO connection using the provided configuration.
     */
    public function __construct()
    {
        // Build Data Source Name (DSN)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';port=' . $this->dbport . ';charset=utf8';

        // Set PDO options
        $options = [
            // Use persistent connection to avoid creating a new connection for each instance.
            PDO::ATTR_PERSISTENT => true,
            // Throw exceptions on errors.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        // Attempt to establish a connection using PDO.
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->password, $options);
        } catch (PDOException $e) {
            throw new Exception("Error creating city: " . $e->getMessage());
        }
    }

    /**
     * Prepares an SQL query.
     *
     * @param string $sql The SQL query to prepare.
     * @return void
     */
    public function query(string $sql): void
    {
        $this->stmt = $this->dbh->prepare($sql);
    }

    /**
     * Executes the prepared SQL statement.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function execute(): bool
    {
        return $this->stmt->execute();
    }

    /**
     * Fetches all results from the executed statement as an associative array.
     *
     * @return array The result set as an associative array.
     */
    public function results(): array
    {
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes the statement and fetches a single result as an associative array.
     *
     * @return array|null The single result as an associative array, or null if no result is found.
     */
    public function result(): ?array
    {
        $this->execute();
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Binds a value to a parameter in the SQL statement.
     *
     * @param string $param The parameter identifier.
     * @param mixed $value The value to bind to the parameter.
     * @return void
     */
    public function bind(string $param, $value): void
    {   
        $this->stmt->bindValue($param, $value);
    }

    /**
     * Begins a transaction.
     *
     * @return void
     */
    public function begin(): void
    {
        $this->dbh->beginTransaction();
    }

    /**
     * Commits the current transaction.
     *
     * @return void
     */
    public function commit(): void
    {
        $this->dbh->commit();
    }
}
