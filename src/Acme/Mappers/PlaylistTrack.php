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
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'PlaylistTrackId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'PlaylistTrackId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'PlaylistId'       => array('type' => 'integer'),
				'TrackId'          => array('type' => 'integer'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'playlist' => new OneToMany(new PlaylistMapper(), 'pt', 'p', 'pt.PlaylistId = p.PlaylistId', 'playlistTracks'),
				'track'    => new OneToMany(new TrackMapper(), 'pt', 't', '`pt`.TrackId = `t`.TrackId', 'playlistTracks'),
			);
		}

		return self::$relations;
	}

	/**
	 * @param  string  $relName
	 * @return Merry\Core\Services\Orm\Casterlith\Relations\RelationInterface
	 */
	public static function getRelation($relName)
	{
		if (is_null(self::$relations)) {
			self::getRelations();
		}

		return self::$relations[$relName];
	}
}
