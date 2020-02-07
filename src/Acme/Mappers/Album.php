<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Track as TrackMapper;
use Acme\Mappers\Artist as ArtistMapper;

class Album extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'albums';
	protected static $entity     = 'Acme\Entities\Album';
	protected static $fields     = array(
		'AlbumId'   => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'Title'     => array('type' => 'string'),
		'ArtistId'  => array('type' => 'integer'),
	);
	protected static $relations   = null;

	public static function getPrimaryKey()
	{
		return 'AlbumId';
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'tracks' => new OneToMany(new TrackMapper(), 'album', 'track', '`album`.AlbumId = `track`.AlbumId', 'album'),
				'artist' => new ManyToOne(new ArtistMapper(), 'album', 'artist', '`album`.ArtistId = `artist`.ArtistId', 'albums'),
			);
		}

		return self::$relations;
	}
}
