<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Album as AlbumMapper;

class Artist extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'artists';
	protected static $entity     = 'Acme\Entities\Artist';
	protected static $fields     = array(
		'ArtistId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'Name'      => array('type' => 'string'),
	);
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'ArtistId';
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'albums' => new OneToMany(new AlbumMapper(), 'artist', 'album', '`artist`.ArtistId = `album`.ArtistId', 'artist'),
			);
		}

		return self::$relations;
	}
}
