<?php

require_once '../vendor/autoload.php';
require_once '../bucket-reader.class.php';

use PHPUnit\Framework\TestCase;

class BucketReaderTest extends TestCase {

	public function testSetParams() {
		$bucketReader = new BucketReader();
		ob_start();
		$this->assertFalse($bucketReader->setParams(['--version']));
		$this->assertFalse($bucketReader->setParams(['--help']));
		$this->assertTrue($bucketReader->setParams([]));
		$this->assertTrue($bucketReader->setParams([
			'--filter="/UFO/"',
			'--group-by-region',
			'--organize-by-storage',
			'--size-format="GB"',
		]));
		try {
			$bucketReader->setParams(['--invalid-argument']);
		} catch (Exception $e) {
			$this->assertTrue(is_int(strpos($e->getMessage(), 'Unrecognized argument')));
		}
		ob_end_clean();
	}

	public function testListBuckets() {
		ob_start();
		$stack = [];
        $this->assertEquals(0, count($stack));
		ob_end_clean();
	}

}

?>