<?php
declare(strict_types=1);

namespace Phector;

use Phector\InvalidConfigException;

/**
 * A configuration class for the repo object
 */
final class RepoConfig
{
    private $dbConfig;

    private function __construct(array $dbConfig)
    {
        $this->dbConfig = $dbConfig;
    }

    /**
     * A getter for the database config.
     *
     * @return string The column name
     */
    public function getDatabaseConfig() : array
    {
        return $this->dbConfig;
    }

    /**
     * Validates and creates a config instance
     *
     * @param  array $config Generic database configuration
     * @throws InvalidConfigException If the config is incorrect
     * @return RepoConfig A valid repo config
     */
    public static function create(array $config)
    {
        // TODO: Further process of config
        return new self($config);
    }
}
