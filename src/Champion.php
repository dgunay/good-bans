<?php declare(strict_types=1);

namespace GoodBans;

class Champion
{
	private $id;
	private $winRate;
	private $playRate;
	private $banRate;
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
	
	public function banValue() : float {
		if ($this->banValue) {
			return $this->banValue;
		}

		return ($this->winRate - 0.5) * $this->adjustedPickRate();
	}

	public function adjustedPickRate() : float {
		if ($this->adjustedPickRate) {
			return $this->adjustedPickRate;
		}

		return (100 * $this->playRate) / (100 - $this->banRate);
	}

}