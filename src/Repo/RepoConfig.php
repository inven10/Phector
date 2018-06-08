<?php
declare(strict_types=1);

namespace Phector\Repo;

use Phector\Types\StringType;
use Phector\Types\JsonType;
use Phector\Types\DateType;
use Phector\Exceptions\InvalidConfigException;

/**
 * A configuration class for the repo object
 */
final class RepoConfig
{
    private $dbConfig;
    private $types;

    private function __construct(array $dbConfig, array $types)
    {
        $this->dbConfig = $dbConfig;
        $this->types= $types;
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
     * A getter for the types used.
     *
     * @return array Array whose keys point to type classes
     */
    public function getTypes() : array
    {
        return $this->types;
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
        $dbConfig = $config['db'] ?? [];

        $baseTypeConfig = [
            'string' => StringType::class,
            'date' => DateType::class,
            'json' => JsonType::class,
        ];
        $customTypes = $config['types'] ?? [];
        $mergedTypes= array_merge($baseTypeConfig, $customTypes);

        return new self($dbConfig, $mergedTypes);
    }
}
