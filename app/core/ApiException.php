<?php

/**
 * Class ApiException
 *
 * A custom exception class that carries additional information for API error responses.
 */
class ApiException extends Exception
{
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected int $status;

    /**
     * Application-specific error code.
     *
     * @var string
     */
    protected string $codeName;

    /**
     * ApiException constructor.
     *
     * @param int $status The HTTP status code (e.g. 401, 404, 500).
     * @param string $codeName Application-specific error code (e.g. "UNAUTHORIZED", "NOT_FOUND").
     * @param string $message The error message.
     */
    public function __construct(int $status, string $codeName, string $message)
    {
        $this->status = $status;
        $this->codeName = $codeName;
        parent::__construct($message, $status);
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get the application-specific error code.
     *
     * @return string
     */
    public function getCodeName(): string
    {
        return $this->codeName;
    }
}
