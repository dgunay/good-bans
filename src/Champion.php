<?php declare(strict_types=1);

namespace GoodBans;

use SebastianBergmann\CodeCoverage\Report\PHP;
use Prophecy\Argument\Token\ExactValueToken;


// TODO: can I make this work with JSON encode?
// TODO: should it extend RiotAPI\Objects\StaticData\StaticChampionDto?
class Champion
{
	/** @var string */
	private $id;

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

	protected const REQUIRED = [ 'winRate', 'playRate', 'banRate', 'elo', 'patch', 'name' ];

	public function __construct(array $champion) {
		foreach (self::REQUIRED as $field) {
			if (!array_key_exists($field, $champion)) {
				throw new \BadMethodCallException("Field '$field' required to construct " . __CLASS__);
			}
		}

		$this->id = (string) isset($champion['championId']) ? $champion['championId'] : null;

		// TODO: throw exception if these aren't normalized to 0.0 - 1.0 scale
		$floats = ['winRate', 'playRate', 'banRate'];
		foreach ($floats as $float_field) {
			if ($this->$float_field > 1.0 || $this->$float_field < 0.0) {
				throw new \UnexpectedValueException("Must use value between 0.0 and 1.0");
			}

			$this->$float_field = (float) $champion[$float_field];
		}

		$this->elo      = $champion['elo'];
		$this->patch    = $champion['patch'];

		$this->name = Champion::fixName($champion['name']);
	}

	/**
	 * Replaces names like AurelionSol with Aurelion Sol.
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

		// throw new \Exception("Failed to fix '$name'");
		return $name;
	}

	public function getId() {
		return $this->id;
	}

	public function getWinRate() : float {
		return $this->winRate;
	}

	public function getPlayRate() : float {
		return $this->playRate;
	}

	public function getName() : string {
		return $this->name;
	}

	public function getElo() : string {
		return $this->elo;
	}

	public function getPatch() : string {
		return $this->patch;
	}
	
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

		// TODO: should it be 1.0 or 100?
		// echo $this->name . PHP_EOL;
		// echo $this->elo . PHP_EOL;
		// echo $this->banRate . PHP_EOL;
		return (1.0 * $this->playRate) / (1.0 - $this->banRate);
	}

	public function getBanRate() : float {
		return $this->banRate;
	}
}