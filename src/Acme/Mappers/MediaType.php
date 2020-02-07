<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Track as TrackMapper;

class MediaType extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'media_types';
	protected static $entity     = 'Acme\Entities\MediaType';
	protected static $fields     = array(
		'MediaTypeId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'Name'         => array('type' => 'string'),
	);
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'MediaTypeId';
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'tracks' => new OneToMany(new TrackMapper(), 'm', 't', '`m`.MediaTypeId = `t`.MediaTypeId', 'mediaType'),
			);
		}

		return self::$relations;
	}
}
