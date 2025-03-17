<?php

/**
 * Class Person
 *
 * Model class for handling person-related database operations.
 */
class Person
{
    /**
     * Database connection instance.
     *
     * @var Database
     */
    private Database $db;

    /**
     * Organization constructor.
     *
     * Initializes a new database connection instance.
     */
    public function __construct()
    {
        $this->db = new Database();
    }
}
