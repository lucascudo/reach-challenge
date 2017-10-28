#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

/**
  * A Amazon Web Service S3 bucket listing tool developed for Rea.ch Challenge.
  *
  * @author Lucas Teixeira Rocha <lucasrochabr@outlook.com>
  */
class ReachChallenge {
	protected $version;
	protected $options;
	
	function __construct() {
		$iniConfig = parse_ini_file('config.ini');
		$this->version = $iniConfig['version'];
		$this->options = [
			'filter' => NULL,
			'group-by-region' => NULL,
			'organize-by-storage' => NULL,
			'size-format' => 'KB',
		];
	}

	public function main() {
		try {
			global $argv;
			echo "\n";
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
		if (in_array('-h', $arguments) || in_array('--help', $arguments)) {
			$this->showManual();
		} else if (in_array('-v', $arguments) || in_array('--version', $arguments)) {
			$this->showVersion();
		}
		foreach ($arguments as $arg) {
			$explodedArg = explode('=', $arg);
			$argName = str_replace('--', '', array_shift($explodedArg));
			$argValue = array_pop($explodedArg);
			if (!array_key_exists($argName, $this->options)) {
				throw new Exception("Unrecognized argument: '$argName'", 1);
			}
			if (in_array($argName, ['group-by-region', 'organize-by-storage'])) {
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
			$storages = [];
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
				if ($this->options['filter']) {
					$found = preg_match($this->options['filter'], $file['Key']) ? TRUE : FALSE;
					if (!$found) continue;
				}
				if ($this->options['organize-by-storage']) {
					if (!isset($storages[$file['StorageClass']])) {
						$storages[$file['StorageClass']] = [
							'numberOfObjects' => 0,
							'bucketSize' => 0,
							'creationDate' => $bucket['CreationDate'],
							'lastModified' => new DateTime('1900-01-01'),
						];
					}
					if ($file['LastModified'] > $storages[$file['StorageClass']]['lastModified']) {
						$storages[$file['StorageClass']]['lastModified'] = $file['LastModified'];
					}
					$storages[$file['StorageClass']]['numberOfObjects']++;
					$storages[$file['StorageClass']]['bucketSize'] += $file['Size'];
				} else {
					if ($file['LastModified'] > $lastModified) {
						$lastModified = $file['LastModified'];
					}
					$numberOfObjects++;
					$bucketSize += $file['Size'];
				}
			}
			if ($this->options['group-by-region']) {
				if ($lastModified > $regionalData[$bucketRegion]['lastModified']) {
					$regionalData[$bucketRegion]['lastModified'] = $lastModified;
				}
				$regionalData[$bucketRegion]['numberOfObjects'] += $numberOfObjects;
				$regionalData[$bucketRegion]['bucketSize'] += $bucketSize;
				$regionalData[$bucketRegion]['buckets'][] = $bucket['Name'];
			} else {
				if ($this->options['organize-by-storage']) {
					foreach ($storages as $storageClass => $data) {
						$tbl->addRow([
					    	$bucket['Name'] . " ($storageClass)",
					    	$data['numberOfObjects'],
					    	$this->printSize($data['bucketSize'], $this->options['size-format']),
					    	$bucket['CreationDate']->format('r'),
					    	$data['lastModified']->format('r'),
				    	]);
					}
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
		echo
			"Just type ./reach-challenge.php to list your buckets or use some of the awesome options described below:\n"
			."	-h, --help 				Prints this manual.\n"
			."	-v, --version 				Shows the program`s version.\n"
			."	--group-by-region			Group the results by AWS regions.\n"
			."	--organize-by-storage			Organize the results by AWS Storage Classes.\n"
			."	--size-format=[value]			Uses one of the supported size formats (bytes, KB, MB or GB). Ex: ./reach-challenge.php --size-format=\"GB\"\n"
			."	--filter=[value]			Uses a regular expression to filter the accounted files by its names. Ex (consider only files wich names begins with \"UFO\"): ./reach-challenge.php --file=\"/^UFO/\"";
		$this->finish();
	}

	protected function showVersion() {
		echo "reach-challenge {$this->version}";
		$this->finish();
	}

	protected function finish() {
		die("\n\n");
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
