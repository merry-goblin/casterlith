<?php

namespace Acme\Composers;

use Monolith\Casterlith\Entities\Album as AlbumEntity;

use Monolith\Casterlith\Composer\ComposerInterface;
use Monolith\Casterlith\Composer\AbstractComposer;

class Album extends AbstractComposer implements ComposerInterface
{
	protected static $mapperName  = 'Acme\\Mappers\\Album';
}
