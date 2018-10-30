<?php declare(strict_types=1);

namespace GoodBans;

use SebastianBergmann\CodeCoverage\Report\PHP;
use Prophecy\Argument\Token\ExactValueToken;


// TODO: can I make this work with JSON encode?

/**
 * Represents a Champion and can calculate the value of banning them in champ
 * select as a function of pickrate and winrate. 
 */
class Champion
{
	/** @var string */
	private $championId;

	/** @var float */
	private $winRate;

	/** @var float */
	private $playRate;

	/** @var float */
	private $banRate;

	/** @var string */
	private $elo;

	/** @var string */
	private $patch;

	/** @var string */
	private $name;

	/** @var float */
	private $banValue = null;

	/** @var float */
	private $adjustedPickRate = null;

	/** @var REQUIRED Required keys in the array passed to the constructor.  */
	protected const REQUIRED = [ 'winRate', 'playRate', 'banRate', 'elo', 'patch', 'name' ];

	/**
	 * Pass an associative array to construct the Champion. Required keys are in
	 * Champion::REQUIRED.
	 *
	 * @param array $champion
	 */
	public function __construct(array $champion) {
		foreach (self::REQUIRED as $field) {
			if (!array_key_exists($field, $champion)) {
				throw new \BadMethodCallException("Field '$field' required to construct " . __CLASS__);
			}
		}

		$this->championId = (string) isset($champion['championId']) ? $champion['championId'] : null;

		// TODO: throw exception if these aren't normalized to 0.0 - 1.0 scale
		$floats = ['winRate', 'playRate', 'banRate'];
		foreach ($floats as $float_field) {
			if ($this->$float_field > 1.0 || $this->$float_field < 0.0) {
				throw new \UnexpectedValueException("Must use value between 0.0 and 1.0");
			}

			$this->$float_field = (float) $champion[$float_field];
		}

		$this->elo      = (string) $champion['elo'];
		$this->patch    = (string) $champion['patch'];

		$this->name = Champion::fixName($champion['name']);
	}

	/**
	 * Replaces names like AurelionSol with Aurelion Sol. First a lookup table is
	 * checked for common cases (especially void champions). If there are no hits, 
	 * it spaces the words out where the capital letters are. If that can't be
	 * done, it just returns the 
	 *
	 * @param string $name
	 * @return string
	 */
	public static function fixName(string $name) : string {
		// weird one-offs 
		$oddities = [
			'Chogath'    => 'Cho\'Gath',
			'KogMaw'     => 'Kog\'Maw',
			'Kaisa'      => 'Kai\'Sa',
			'Khazix'     => 'Kha\'Zix',
			'RekSai'     => 'Rek\'Sai',
			'Velkoz'     => 'Vel\'Koz',
			'MonkeyKing' => 'Wukong',
			'Dr Mundo'   => 'Dr. Mundo',
			'DrMundo'    => 'Dr. Mundo',
			'Le Blanc'   => 'LeBlanc',
			'Leblanc'    => 'LeBlanc',
			'LeBlanc'    => 'LeBlanc',
			'Nunu'       => 'Nunu & Willump',
		];

		if (array_key_exists($name, $oddities)) {
			return $oddities[$name];
		}

		if (preg_match('/[a-z][A-Z]/', $name, $match)) {
			return str_replace($match[0], "{$match[0][0]} {$match[0][1]}", $name);
		}

		return $name;
	}

	public function getChampionId() : ?string { return $this->championId; }
	public function getWinRate()    : float   { return $this->winRate;    }
	public function getPlayRate()   : float   { return $this->playRate;   }
	public function getName()       : string  { return $this->name;       }
	public function getElo()        : string  { return $this->elo;        }
	public function getPatch()      : string  { return $this->patch;      }
	public function getBanRate()    : float   { return $this->banRate;    }

	/**
	 * Calculates champion ban value as a ratio of winrate to adjusted pick rate.
	 * Cached after the first call for the lifetime of the Champion.
	 *
	 * @return float
	 */
	public function banValue() : float {
		if ($this->banValue) {
			return $this->banValue;
		}

		return ($this->winRate - 0.5) * $this->adjustedPickRate();
	}

	/**
	 * Calculates adjusted pick rate as a ratio of pickrate to banrate.
	 *
	 * @return float
	 */
	public function adjustedPickRate() : float {
		if ($this->adjustedPickRate) {
			return $this->adjustedPickRate;
		}

		return (1.0 * $this->playRate) / (1.0 - $this->banRate);
	}
}