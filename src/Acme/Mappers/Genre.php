<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Track as TrackMapper;

class Genre extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'genres';
	protected static $entity     = 'Acme\Entities\Genre';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'GenreId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'GenreId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'Name'     => array('type' => 'string'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'tracks' => new OneToMany(new TrackMapper(), 'g', 't', '`g`.GenreId = `t`.GenreId', 'genre'),
			);
		}

		return self::$relations;
	}
}
