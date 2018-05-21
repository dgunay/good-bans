<?php declare(strict_types=1);

namespace GoodBans;

class Champion
{
	private $id;
	private $winRate;
	private $playRate;
	private $banRate;
	private $percentRolePlayed;
	private $elo;
	private $patch;
	private $name;
	
	// computed the first time and then cached 
	private $banValue         = null;
	private $adjustedPickRate = null;

	public function __construct(array $champion_gg, string $name) {
		$this->id       = (string) $champion_gg['championId'];
		$this->winRate  = (float) $champion_gg['winRate'];
		$this->playRate = (float) $champion_gg['playRate'];
		$this->banRate  = (float) $champion_gg['banRate'];
		$this->percentRolePlayed = (float) $champion_gg['percentRolePlayed'];
		$this->elo      = $champion_gg['elo'];
		$this->patch    = $champion_gg['patch'];
		$this->name     = $name;
	}

	public function getId() : string {
		return $this->id;
	}

	public function getWinRate() : float {
		return $this->winRate;
	}

	public function getPercentRolePlayed() : float {
		return $this->percentRolePlayed;
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
		return (100 * $this->playRate) / (100 - $this->banRate);
	}

	public function weighted($value) {
		return $value * $this->percentRolePlayed;
	}
}