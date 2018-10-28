<?php

namespace GoodBans;

class TopBans
{
	protected $champs_by_elo = [];

	protected $patch;

	public function __construct(array $champs_by_elo, string $patch) {
		$this->champs_by_elo = $champs_by_elo;
		$this->patch = $patch;
	}

	public function getTopBans() : array {
		return $this->champs_by_elo;
	}

	public function getPatch() : string {
		return $this->patch;
	}
}