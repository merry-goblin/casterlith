<?php

namespace Monolith\Casterlith\tests\units\Composer;

require_once(__DIR__."/../../../vendor/autoload.php");
require_once(__DIR__."/../config/utils.php");
require_once(__DIR__ ."/../../../vendor/merry-goblin/casterlith/Monolith/Casterlith/Composer/AbstractComposer.php");

use atoum;

class AbstractComposer extends atoum
{
	/*** __construct ***/

	public function testContructor()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->object($composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer'))
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\ArtistComposer')
		;
	}

	public function testContructorWithoutComposerInterface()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->exception(
				function() use($orm) {
					$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposerWithoutComposerInterface');
				}
			)
		;
	}

	public function testContructorWithoutAValidMapper()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->exception(
				function() use($orm) {
					$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposerWithoutAValidMapper');
				}
			)
		;
	}

	/*** select ***/

	public function testSelectWithOneEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');
		$art = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art")
		;
		$this
			->object($art)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->string($art->Name)
				->isEqualTo("AC/DC")
		;
	}

	public function testSelectWithTwoEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');
		$query->join('art', 'alb', 'albums');
		$query->all();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId")
		;
	}

	public function testSelectWithoutEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');

		$this
			->exception(
				function() use($composer) {
					$query = $composer->select();
				}
			)
		;
	}

	public function testSelectWithAnInteger()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');

		$this
			->exception(
				function() use($composer) {
					$query = $composer->select(12);
				}
			)
		;
	}

	public function testSelectWithOneEntityAliasAndACustomReplacer()
	{
		$orm = getAReadOnlyOrmInstance("_cr");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as art_cr1_ArtistId,art.Name as art_cr1_Name FROM artists art")
		;
	}

	public function testSelectWithEmptyEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');

		$this
			->exception(
				function() use($composer) {
					$composer->select('');
				}
			)
		;
	}

	/*** addSelect ***/

	public function testAddSelectWithEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');
		$query->addSelect('alb');

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art")
		;
	}

	public function testAddSelectWithoutEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');

		$this
			->exception(
				function() use($query) {
					$query->addSelect();
				}
			)
		;
	}

	public function testAddSelectWithAnInteger()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');

		$this
			->exception(
				function() use($query) {
					$query->addSelect(12);
				}
			)
		;
	}

	public function testAddSelectWithExistingEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');

		$this
			->exception(
				function() use($query) {
					$query->addSelect('art');
				}
			)
		;
	}

	public function testAddSelectWithEmptyEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');

		$this
			->exception(
				function() use($query) {
					$query->addSelect('');
				}
			)
		;
	}

	/*** selectAsRaw ***/

	public function testSelectAsRawWithOnlyAnEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->selectAsRaw('art');

		$this
			->exception(
				function() use($query) {
					$query->all();
				}
			)
		;
	}

	public function testSelectAsRawWithAnEntityAliasAndACounter()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb');
		$query->all();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb FROM artists art")
		;
	}

	public function testSelectAsRawWithAnEntityAliasAndTwoSelectors()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb', 'count(distinct(art.ArtistId)) as nb2');
		$query->all();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2 FROM artists art")
		;
	}

	public function testSelectAsRawWithAnEntityAliasAndOneSelectorOfTwoCounters()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2');
		$query->all();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2 FROM artists art")
		;
	}

	/*** addSelectAsRaw ***/

	public function testAddSelectAsRawWithACounter()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->selectAsRaw('art');
		$query->addSelectAsRaw('count(distinct(art.ArtistId)) as nb');
		$query->all();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb FROM artists art")
		;
	}

	public function testAddSelectAsRawWithAnEntityAliasAndTwoSelectors()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->selectAsRaw('art');
		$query->addSelectAsRaw('count(distinct(art.ArtistId)) as nb', 'count(distinct(art.ArtistId)) as nb2');
		$query->all();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2 FROM artists art")
		;
	}

	public function testAddSelectAsRawWithAnEntityAliasAndOneSelectorOfTwoCounters()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->selectAsRaw('art');
		$query->addSelectAsRaw('count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2');
		$query->all();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2 FROM artists art")
		;
	}

	/*** join ***/

	public function testJoinWithOneArtist()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');
		$query->join('art', 'alb', 'albums');
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albums)
			 	->hasSize(2)
			 	->hasKeys(array(1, 4))
		;
		$this
			->string($artist->albums[4]->Title)
				->isEqualTo("Let There Be Rock")
		;
	}

	public function testJoinWithAllArtists()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');
		$query->join('art', 'alb', 'albums');
		$artists = $query->all();
		$artist = $artists[227];

		$this
			->array($artists)
				->hasSize(204)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albums)
			 	->hasSize(1)
			 	->hasKeys(array(293))
		;
		$this
			->string($artist->albums[293]->Title)
				->isEqualTo("Pavarotti's Opera Made Easy")
		;
		$this
			->integer($artist->albums[293]->AlbumId)
				->isEqualTo(293)
		;
	}

	public function testJoinWithTheSameEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');

		$this
			->exception(
				function() use($query) {
					$query->join('art', 'art', 'albums');
				}
			)
		;
	}

	public function testJoinWithTheAnEmptyEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');

		$this
			->exception(
				function() use($query) {
					$query->join('art', '', 'albums');
				}
			)
		;
		$this
			->exception(
				function() use($query) {
					$query->join('', 'alb', 'albums');
				}
			)
		;
	}

}

//	Valid

class ArtistComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistMapper';
}

class ArtistMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'artists';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'ArtistId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'ArtistId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'Name'      => array('type' => 'string'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'albums' => new \Monolith\Casterlith\Relations\OneToMany(new \Monolith\Casterlith\tests\units\Composer\AlbumMapper(), 'artist', 'album', '`artist`.ArtistId = `album`.ArtistId', 'artist'),
			);
		}

		return self::$relations;
	}
}

class AlbumMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'albums';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumEntity';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'AlbumId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'AlbumId'   => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'Title'     => array('type' => 'string'),
				'ArtistId'  => array('type' => 'integer'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				//'tracks' => new \Monolith\Casterlith\Relations\OneToMany(new TrackMapper(), 'album', 'track', '`album`.AlbumId = `track`.AlbumId', 'album'),
				'artist' => new \Monolith\Casterlith\Relations\ManyToOne(new \Monolith\Casterlith\tests\units\Composer\ArtistMapper(), 'album', 'artist', '`album`.ArtistId = `artist`.ArtistId', 'albums'),
			);
		}

		return self::$relations;
	}
}

class ArtistEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $ArtistId  = null;
	public $Name      = null;

	public $albums  = \Monolith\Casterlith\Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->ArtistId;
	}
}

class AlbumEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $AlbumId   = null;
	public $Title     = null;
	public $ArtistId  = null;

	public $tracks  = \Monolith\Casterlith\Casterlith::NOT_LOADED;
	public $artist  = \Monolith\Casterlith\Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->AlbumId;
	}
}

//	Invalid

class ArtistComposerWithoutComposerInterface extends \Monolith\Casterlith\Composer\AbstractComposer
{
}

class ArtistComposerWithoutAValidMapper extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistMapperWithoutMapperInterface';
}

class ArtistMapperWithoutMapperInterface extends \Monolith\Casterlith\Mapper\AbstractMapper
{
}
