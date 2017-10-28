#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

class ReachChallenge {
	protected $version;
	protected $options;
	
	function __construct() {
		$iniConfig = parse_ini_file('config.ini');
		$this->version = $iniConfig['version'];
		$this->options = [
			'filter' => NULL,
			'group-by-region' => NULL,
			'size-format' => 'KB',
		];
	}

	public function main() {
		try {
			global $argv;
			$this->setParams($argv);
			$this->listBuckets();
			$this->finish();
		} catch (Exception $e) {
			echo $e->getMessage();
		} finally {
			$this->finish();
		}
	}

	protected function setParams($arguments) {
		array_shift($arguments); //removes filename
		if (in_array('--help', $arguments)) {
			$this->showManual();
		} else if (in_array('--version', $arguments)) {
			$this->showVersion();
		}
		foreach ($arguments as $arg) {
			$explodedArg = explode('=', $arg);
			$argName = str_replace('--', '', array_shift($explodedArg));
			$argValue = array_pop($explodedArg);
			if (!array_key_exists($argName, $this->options)) {
				throw new Exception("Unrecognized argument: '$argName'", 1);
			}
			if ($argName == 'group-by-region') {
				$argValue = TRUE;
			}
			$this->options[$argName] = $argValue;
		}
	}

	protected function listBuckets() {
		$regionalData = [];
		$tbl = new Console_Table();
		$tbl->setHeaders([
			'Name',
			'Number of Objects',
			'Total Size',
			'Creation Date',
			'Last Modified',
		]);
		$s3Client = (new \Aws\Sdk)->createMultiRegionS3([ 'version' => 'latest' ]);
		$result = $s3Client->listBuckets();
		foreach ($result['Buckets'] as $bucket) {
			$numberOfObjects = 0;
			$bucketSize = 0;
			$lastModified = new DateTime('1900-01-01');
		    $objects = $s3Client->listObjects([ 'Bucket' => $bucket['Name'] ]);
		    $bucketRegion = $objects['@metadata']['headers']['x-amz-bucket-region'];
			if ($this->options['group-by-region'] && !isset($regionalData[$bucketRegion])) {
				$regionalData[$bucketRegion] = [
					'buckets' => [],
					'numberOfObjects' => 0,
					'bucketSize' => 0,
					'creationDate' => $bucket['CreationDate'],
					'lastModified' => new DateTime('1900-01-01'),
				];
			}
			foreach ($objects['Contents'] as $file) {
				if ($file['LastModified'] > $lastModified) {
					$lastModified = $file['LastModified'];
				}
				$numberOfObjects++;
				$bucketSize += $file['Size'];
			}
			if ($this->options['group-by-region']) {
				if ($lastModified > $regionalData[$bucketRegion]['lastModified']) {
					$regionalData[$bucketRegion]['lastModified'] = $lastModified;
				}
				$regionalData[$bucketRegion]['numberOfObjects'] += $numberOfObjects;
				$regionalData[$bucketRegion]['bucketSize'] += $bucketSize;
				$regionalData[$bucketRegion]['buckets'][] = $bucket['Name'];
			} else {
				$tbl->addRow([
			    	$bucket['Name'],
			    	$numberOfObjects,
			    	$this->printSize($bucketSize, $this->options['size-format']),
			    	$bucket['CreationDate']->format('r'),
			    	$lastModified->format('r'),
		    	]);
			}
		}
		if ($this->options['group-by-region']) {
			foreach ($regionalData as $name => $data) {
				$tbl->addRow([
			    	$name . ' (' . implode(', ', $data['buckets']) . ')',
			    	$data['numberOfObjects'],
			    	$this->printSize($data['bucketSize'], $this->options['size-format']),
			    	$data['creationDate']->format('r'),
			    	$data['lastModified']->format('r'),
		    	]);
			}
		}
		echo $tbl->getTable();
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

	protected function printSize($kbytes, $format = 'bytes') {
		$bytes = $kbytes * 1024;
		switch (strtoupper($format))
		{
			case 'GB':
				$result = number_format($bytes / 1073741824, 2) . ' GB';
				break;
			case 'MB':
				$result = number_format($bytes / 1048576, 2) . ' MB';
				break;
			case 'KB':
				$result = number_format($bytes / 1024, 0) . ' KB';
				break;
			case 'BYTES':
				$result = $bytes . ' bytes';
				break;
			default:
				throw new Exception("Unrecognized size format: $format", 1);
 		}
 		return $result;
	}

}

$reachChallenge = new ReachChallenge();
$reachChallenge->main();

?>
