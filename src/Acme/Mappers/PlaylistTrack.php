<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Playlist as PlaylistMapper;
use Acme\Mappers\Track as TrackMapper;

class PlaylistTrack extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'playlist_track';
	protected static $entity     = 'Acme\Entities\PlaylistTrack';
	protected static $fields     = array(
		'PlaylistTrackId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'PlaylistId'       => array('type' => 'integer'),
		'TrackId'          => array('type' => 'integer'),
	);
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'PlaylistTrackId';
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'playlist' => new ManyToOne(new PlaylistMapper(), 'pt', 'p', 'pt.PlaylistId = p.PlaylistId', 'playlistTracks'),
				'track'    => new ManyToOne(new TrackMapper(), 'pt', 't', '`pt`.TrackId = `t`.TrackId', 'playlistTracks'),
			);
		}

		return self::$relations;
	}
}
