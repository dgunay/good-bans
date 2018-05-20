<?php

namespace GoodBans;

use Psr\Log\AbstractLogger;

/**
 * Lets us log messages wherever we like.
 */
class Logger extends AbstractLogger
{
	/** @var resource */
	protected $stream;

	public function __construct($stream) {
		if (!is_resource($stream)) {
			throw new \InvalidArgumentException('Logger must use a valid stream resource.');
		}

		$this->stream = $stream;
	}

	public function log($level, $message, array $context = [])
	{
		if (!empty($context)) {
			$message .= ' (context: ' . json_encode($context);
		}

		fwrite($this->stream, $message);
	}
}