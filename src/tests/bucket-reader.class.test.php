<?php

require_once '../vendor/autoload.php';
require_once '../bucket-reader.class.php';

use PHPUnit\Framework\TestCase;

class BucketReaderTest extends TestCase {
	
	public function testSetParamsWithEmptyArgs() {
		$bucketReader = new BucketReader();
		$this->assertTrue($bucketReader->setParams([]));
	}
	
	public function testSetParamsWithVersion() {
		ob_start();
		$bucketReader = new BucketReader();
		$this->assertFalse($bucketReader->setParams(['--version']));
		ob_end_clean();
	}

	public function testSetParamsWithHelp() {
		ob_start();
		$bucketReader = new BucketReader();
		$this->assertFalse($bucketReader->setParams(['--help']));
		ob_end_clean();
	}

	public function testSetParamsWithValidArgs() {
		$bucketReader = new BucketReader();
		$this->assertTrue($bucketReader->setParams([
			'--filter=/validRegex/',
			'--group-by-region',
			'--organize-by-storage',
			'--size-format=bytes',
		]));
	}

	public function testSetParamsWithInvalidArgs() {
		ob_start();
		$bucketReader = new BucketReader();
		try {
			$bucketReader->setParams(['--invalid-argument']);
			throw new Exception("Something did wrong", 1);
		} catch (Exception $e) {
			$this->assertTrue(is_int(strpos($e->getMessage(), 'Unrecognized argument')));
		}
		ob_end_clean();
	}

	public function testListBucketsWithoutParams() {
		ob_start();
		$bucketReader = new BucketReader();
		$this->assertTrue($bucketReader->listBuckets() instanceof Console_Table);
		ob_end_clean();
	}

	public function testListBucketsGroupedByRegion() {
		ob_start();
		$bucketReader = new BucketReader();
		$bucketReader->setParams([ '--group-by-region' ]);
		$this->assertTrue($bucketReader->listBuckets() instanceof Console_Table);
		ob_end_clean();
	}
		
	public function testListBucketsOrganizedByStorage() {
		ob_start();
		$bucketReader = new BucketReader();
		$bucketReader->setParams([ '--organize-by-storage' ]);
		$this->assertTrue($bucketReader->listBuckets() instanceof Console_Table);
		ob_end_clean();
	}

	public function testListBucketsWithValidRegex() {
		ob_start();
		$bucketReader = new BucketReader();
		$bucketReader->setParams([ '--filter=/validRegex/' ]);
		$this->assertTrue($bucketReader->listBuckets() instanceof Console_Table);
		ob_end_clean();
	}

	public function testListBucketsWithValidSizeFormat() {
		ob_start();
		$bucketReader = new BucketReader();
		$bucketReader->setParams([ '--size-format=MB' ]);
		$this->assertTrue($bucketReader->listBuckets() instanceof Console_Table);
		ob_end_clean();
	}

	public function testListBucketsWithInvalidRegex() {
		ob_start();
		$bucketReader = new BucketReader();
		$bucketReader->setParams([ '--filter=invalidRegex' ]);
		try {
			$bucketReader->listBuckets();
			throw new Exception("Something did wrong", 1);
		} catch (Exception $e) {
			$this->assertTrue((
				is_int(strpos($e->getMessage(), 'Invalid regex filter'))
				|| is_int(strpos($e->getMessage(), 'preg_match()'))
			));
		}
		ob_end_clean();
	}
	
	public function testListBucketsWithInvalidSizeFormat() {
		ob_start();
		$bucketReader = new BucketReader();
		$bucketReader->setParams([ '--size-format=InvalidSizeFormat' ]);
		try {
			$bucketReader->listBuckets();
			throw new Exception("Something did wrong", 1);
		} catch (Exception $e) {
			$this->assertTrue(is_int(strpos($e->getMessage(), 'Unrecognized size format')));
		}
		ob_end_clean();
	}

}

?>