<?php declare(strict_types=1);

namespace GoodBans;

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

	public function __construct(array $champion) {
		$this->id       = (string) $champion['championId'];

		// TODO: throw exception if these aren't normalized to 0.0 - 1.0 scale
		$this->winRate  = (float) $champion['winRate'];
		$this->playRate = (float) $champion['playRate'];
		$this->banRate  = (float) $champion['banRate'];

		$this->elo      = $champion['elo'];
		$this->patch    = $champion['patch'];
		$this->name     = $champion['name'] ?? null;
	}

	public function getId() : string {
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
		return (1.0 * $this->playRate) / (1.0 - $this->banRate);
	}

	public function getBanRate() : float {
		return $this->banRate;
	}
}