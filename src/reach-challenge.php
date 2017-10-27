#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

class ReachChallenge {
	protected $version;
	protected $options;
	protected $s3client;

	function __construct() {
		$iniConfig = parse_ini_file('config.ini');
		$this->version = $iniConfig['version'];
		$this->options = [
			'filter' => NULL,
			'size' => 'bytes',
			'organize-by-storage-type' => FALSE,
			'group-by-regions' => FALSE,
		];
		$this->s3client = new Aws\S3\S3Client([
			'version' => 'latest',
			'region'  => 'us-east-1'
		]);
	}

	public function main() {
		global $argc, $argv;
		if ($argc > 1) {
			$this->setParams($argv);
		} else {
			$this->showManual();
		}
		$this->finish();
	}

	protected function setParams($arguments) {
		if (in_array('--help', $arguments)) {
			$this->showManual();
		} else if (in_array('--version', $arguments)) {
			$this->showVersion();
		}
		foreach ($arguments as $arg) {
			$explodedArg = explode('=', $arg);
			$argName = str_replace('--', '', array_shift($explodedArg));
			switch ($argName) {
				case 'size':
					$this->options['size'] = array_pop($explodedArg);
					break;
				case 'organize-by-storage-type':
					$this->options['organize-by-storage-type']  = TRUE;
					break;
				case 'group-by-regions':
					$this->options['group-by-regions'] = TRUE;
					break;
				case 'filter':
					$this->options['filter'] = array_pop($explodedArg);
					break;
				default:
					break;
			}
		}
		echo 'Args: ';
		print_r($arguments);
		echo 'Options: ';
		print_r($this->options);
	}

	protected function showManual() {
		echo 'TODO';
		$this->finish();
	}

	protected function showVersion() {
		echo "reach-challenge {$this->version}";
		$this->finish();
	}

	protected function finish() {
		die("\n");
	}

}

$reachChallenge = new ReachChallenge();
$reachChallenge->main();

?>
