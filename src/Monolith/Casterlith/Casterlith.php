<?php

namespace Monolith\Casterlith;

use Monolith\Casterlith\Composer\ComposerInterface;

use Doctrine\DBAL\DriverManager;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DBALException;

class Casterlith
{
	const NOT_LOADED = 0;

	protected $connection = null;

	protected $selectionReplacer = null;

	/**
	 * @param  array                                             $params         [The database connection parameters]
	 * @param  Monolith\Casterlith\Configuration  $configuration  [The configuration to use]
	 * @param  Doctrine\Common\EventManager                      $eventManager   [The event manager to use]
	 * 
	 * @return Monolith\Casterlith
	 * @throws Doctrine\DBAL\DBALException
	 */
	public function __construct(array $params, Configuration $configuration, EventManager $eventManager = null)
	{
		$this->connection = DriverManager::getConnection($params, $configuration);

		$this->selectionReplacer = $configuration->getSelectionReplacer();

		return $this;
	}

	/**
	 * @param  string $className
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 * @throws Exception
	 */
	public function getComposer($className)
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$composer = new $className($queryBuilder, $this->selectionReplacer);
		if (!($composer instanceof ComposerInterface)) {
			throw new \Exception("className parameter must be a Composer");
		}

		return $composer;
	}

	/**
	 * @return Doctrine\DBAL\Query\QueryBuilder
	 */
	public function getQueryBuilder()
	{
		$queryBuilder = $this->connection->createQueryBuilder();

		return $queryBuilder;
	}
}
