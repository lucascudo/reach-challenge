#!/usr/bin/env php
<?php

require_once 'bucket-reader.class.php';

global $argv;
$arguments = $argv;
array_shift($arguments); //removes filename
echo "\n";
try {
	$bucketReader = new BucketReader();
	if ($bucketReader->setParams($arguments)) {
		echo $bucketReader->listBuckets()->getTable();
	}
} catch (Exception $e) {
	echo $e->getMessage();
}
die("\n\n");

?>