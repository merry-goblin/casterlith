<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Album as AlbumMapper;
use Acme\Mappers\InvoiceItem as InvoiceItemMapper;
use Acme\Mappers\Genre as GenreMapper;
use Acme\Mappers\MediaType as MediaTypeMapper;
use Acme\Mappers\PlaylistTrack as PlaylistTrackMapper;

class Track extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'tracks';
	protected static $entity     = 'Acme\Entities\Track';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'TrackId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'TrackId'       => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'Name'          => array('type' => 'string'),
				'AlbumId'       => array('type' => 'integer'),
				'MediaTypeId'   => array('type' => 'integer'),
				'GenreId'       => array('type' => 'integer'),
				'Composer'      => array('type' => 'string'),
				'Milliseconds'  => array('type' => 'integer'),
				'Bytes'         => array('type' => 'integer'),
				'UnitPrice'     => array('type' => 'decimal'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'album'           => new ManyToOne(new AlbumMapper(), 't', 'a', '`t`.AlbumId = `a`.AlbumId', 'tracks'),
				'invoiceItems'    => new OneToMany(new InvoiceItemMapper(), 't', 'item', '`t`.TrackId = `item`.TrackId', 'track'),
				'genre'           => new ManyToOne(new GenreMapper(), 't', 'g', '`t`.GenreId = `g`.GenreId', 'tracks'),
				'mediaType'       => new ManyToOne(new MediaTypeMapper(), 't', 'm', '`t`.MediaTypeId = `m`.MediaTypeId', 'tracks'),
				'playlistTracks'  => new OneToMany(new PlaylistTrackMapper(), 't', 'pt', '`t`.TrackId = `pt`.TrackId', 'track'),
			);
		}

		return self::$relations;
	}
}
