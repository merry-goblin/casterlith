<?php

namespace Monolith\Casterlith\Composer;

use Doctrine\DBAL\Query\QueryBuilder;
use Monolith\Casterlith\Schema\Builder as SchemaBuilder;

abstract class AbstractComposer
{
	protected $queryBuilder       = null;
	protected static $mapperName  = null;
	protected $mapper             = null;

	protected $schemaBuilder      = null;
	protected $selectionReplacer  = null;

	protected $yetToSelectList    = null;

	/**
	 * @param Doctrine\DBAL\Query\QueryBuilder $queryBuilder
	 */
	public function __construct(QueryBuilder $queryBuilder, $selectionReplacer)
	{
		$this->queryBuilder       = $queryBuilder;
		$this->selectionReplacer  = $selectionReplacer;

		if (is_null($this::$mapperName)) {
			throw new \Exception("mapperName property have to be inialize in current Composer's class");
		}

		$this->mapper = new $this::$mapperName();

		$this->yetToSelectList = array();
	}

	/**
	 * One or more aliases to select
	 * First one must be the one related to the composer
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function select()
	{
		$this->reset();

		//	One or more aliases
		$args = func_get_args();
		if (count($args) == 0) {
			throw new \Exception("At least one alias is needed");
		}

		//	Alias of the current composer's entity
		$rootEntityAlias = $args[0];

		//	Schema builder
		$this->schemaBuilder->select($rootEntityAlias);
		$this->schemaBuilder->from($rootEntityAlias, $this->mapper);

		//	Query builder
		//		Select of root entity
		$this->queryBuilder
			//->select($this->mapper->selectAll($rootEntityAlias, $replacer));
			->select($this->schemaBuilder->getAUniqueSelection($rootEntityAlias));
		//		From
		$this->queryBuilder
			->from($this->mapper->getTable(), $rootEntityAlias);

		//	Any entity to select other than the main one
		if (count($args) > 1) {
			array_shift($args);
			$this->addSelect($args);
		}

		return $this;
	}

	/**
	 * One or more aliases to select
	 *
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function addSelect()
	{
		//	One or more aliases
		$args = func_get_args();
		if (count($args) == 0) {
			throw new \Exception("At least one alias is needed");
		}

		if (is_array($args[0])) {
			$args = $args[0];
		}

		//	Aliases of future joints
		for ($i=0, $len=count($args); $i<$len; $i++) {

			//	Alias of joint entities to select
			$alias = $args[$i];
			$this->schemaBuilder->select($alias);
			$this->yetToSelectList[] = $alias;
		}

		return $this;
	}

	/**
	 * Alias of innerJoin
	 * 
	 * @param  string $fromAlias
	 * @param  string $toAlias
	 * @param  string $relName
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function join($fromAlias, $toAlias, $relName)
	{
		return $this->innerJoin($fromAlias, $toAlias, $relName);
	}

	/**
	 * @param  string $fromAlias
	 * @param  string $toAlias
	 * @param  string $relName
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function innerJoin($fromAlias, $toAlias, $relName)
	{
		list($table, $condition) = $this->schemaBuilder->join($fromAlias, $toAlias, $relName);

		$this->queryBuilder
			->innerJoin($fromAlias, $table, $toAlias, $condition);

		return $this;
	}

	/**
	 * @param  string $fromAlias
	 * @param  string $toAlias
	 * @param  string $relName
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function leftJoin($fromAlias, $toAlias, $relName)
	{
		list($table, $condition) = $this->schemaBuilder->join($fromAlias, $toAlias, $relName);

		$this->queryBuilder
			->leftJoin($fromAlias, $table, $toAlias, $condition);

		return $this;
	}

	/**
	 * @param  string $condition
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function where($condition)
	{
		$this->queryBuilder
			->where($condition);

		return $this;
	}

	/**
	 * @param  string $condition
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function andWhere($condition)
	{
		$this->queryBuilder
			->andWhere($condition);

		return $this;
	}

	/**
	 * @param  string $condition
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function orWhere($condition)
	{
		$this->queryBuilder
			->orWhere($condition);

		return $this;
	}

	/**
	 * @param  string|integer $key
	 * @param  mixed          $value
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function setParameter($key, $value)
	{
		$this->queryBuilder->setParameter($key, $value);

		return $this;
	}

	/**
	 * @param  string  $sort
	 * @param  string  $order
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function order($sort, $order = null)
	{
		$this->queryBuilder->orderBy($sort, $order);

		return $this;
	}

	/**
	 * @param  string  $sort
	 * @param  string  $order
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function addOrder($sort, $order = null)
	{
		$this->queryBuilder->addOrderBy($sort, $order);

		return $this;
	}

	/**
	 * This method must be called after setParameter's method
	 * It's not the as we use in SQL. 
	 * It will limit the elements of the current composer's entity
	 * instead of the number of rows returned by database.
	 * To do so, a sql requet is sent based on the current sql request 
	 * but with only the primary key on the current composer's entity 
	 * and with distinct function on it.
	 * 
	 * @return Monolith\Casterlith\Composer\ComposerInterface
	 */
	public function limit($first, $max = null)
	{
		$alias       = $this->schemaBuilder->getRootAlias();
		$mapperClass = get_class($this->mapper);
		$primaryKey  = $mapperClass::getPrimaryKey();

		//	Clone current dbal's request
		$limitQueryBuilder = clone($this->queryBuilder);
		$limitQueryBuilder
			->select("distinct(".$alias.".".$primaryKey.")")
			->setFirstResult($first)
			->setMaxResults($max);

		//	Get id list in the the range
		$idList = "";
		$statement  = $limitQueryBuilder->execute();
		while ($row = $statement->fetch()) {
			if (!empty($idList)) {
				$idList .= ",";
			}
			$idList .= $row[$primaryKey];
		}

		//	Build a condition to limit the full dbal request
		$condition = null;
		if (count($idList) > 0) {
			$condition  = $alias.".".$primaryKey." IN (".$idList.")";
		}
		$this->queryBuilder->andWhere($condition);

		return $this;
	}

	/**
	 * Initialize statement and return the first entity
	 * This method does no optimization. Optimization is up to the caller
	 * 
	 * @return Monolith\Casterlith\Entity\EntityInterface
	 */
	public function first()
	{
		$this->finishSelection();

		//$sql = $this->queryBuilder->getSQL();
		$statement  = $this->queryBuilder->execute();

		$entity = $this->schemaBuilder->buildFirst($statement);

		return $entity;
	}

	/**
	 * Initialize statement and return an array of entities
	 * 
	 * @return array(Monolith\Casterlith\Entity\EntityInterface)
	 */
	public function all()
	{
		$this->finishSelection();

		//$sql = $this->queryBuilder->getSQL();
		$statement  = $this->queryBuilder->execute();

		$entities = $this->schemaBuilder->buildAll($statement);

		return $entities;
	}

	/**
	 * Reset occure when select method is called
	 * @return [type] [description]
	 */
	protected function reset()
	{
		$this->schemaBuilder    = new SchemaBuilder($this->selectionReplacer);
		$this->yetToSelectList  = array();
	}

	/**
	 * Select of other entities than the one related to the current composer
	 * 
	 * @return null
	 */
	protected function finishSelection()
	{
		foreach ($this->yetToSelectList as $key => $alias) {

			$selection = $this->schemaBuilder->getAUniqueSelection($alias);
			$this->queryBuilder->addSelect($selection);
			unset($this->yetToSelectList[$key]);
		}
	}

	/**
	 * @return Doctrine\DBAL\Query\QueryBuilder
	 */
	public function getQueryBuilder()
	{
		return $this->queryBuilder;
	}
}
