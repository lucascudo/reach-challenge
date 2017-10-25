#!/usr/bin/env php
<?php

class ReachChallenge {
	protected $version;

	function __construct() {
		$iniConfig = parse_ini_file('config.ini');
		$this->version = $iniConfig['version'];
	}
	
	public function main() {
		global $argc, $argv;
		if ($argc > 1) {
			$this->setParams($argv);
		} else {
			$this->printManual();
		}
		echo "\n";
	}

	protected function setParams($arguments) {
		print_r($arguments);
	}

	protected function printManual() {
		echo "reach-challenge {$this->version}";
	}
}

$reachChallenge = new ReachChallenge();
$reachChallenge->main();

?>