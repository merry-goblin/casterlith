<?php

namespace Monolith\Casterlith\Mapper;

use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractMapper
{
	public function getTable()
	{
		return $this::$table;
	}

	public function getEntity()
	{
		return $this::$entity;
	}
}
