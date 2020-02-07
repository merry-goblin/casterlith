<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\PlaylistTrack as PlaylistTrackMapper;

class Playlist extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'playlists';
	protected static $entity     = 'Acme\Entities\Playlist';
	protected static $fields     = array(
		'PlaylistId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'Name'        => array('type' => 'string'),
	);
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'PlaylistId';
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'playlistTracks' => new OneToMany(new PlaylistTrackMapper(), 'p', 'pt', '`p`.PlaylistId = `pt`.PlaylistId', 'playlist'),
			);
		}

		return self::$relations;
	}
}
