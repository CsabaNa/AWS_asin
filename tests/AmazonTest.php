<?php

	class AmazonTest extends PHPUnit_Framework_TestCase
	{
            public function testExistingGbExtract()
	    {
	        $crawler 	= new Amazon\Amazon('GB');
	        $item 		= $crawler->getItem('B00KDRUCJY');

	        $this->assertInternalType('array',$item);

	        $this->assertArrayHasKey('title', $item);
	        $this->assertArrayHasKey('price', $item);
	        $this->assertArrayHasKey('imageUrl', $item);
	    }

	    public function testExistingUsExtract()
	    {
	        $crawler 	= new Amazon\Amazon('US');
	        $item 		= $crawler->getItem('B00KDRUCJY');

	        $this->assertInternalType('array',$item);

	        $this->assertArrayHasKey('title', $item);
	        $this->assertArrayHasKey('price', $item);
	        $this->assertArrayHasKey('imageUrl', $item);
	    }

	    public function testNotExistingGbExtract()
	    {
	        $crawler 	= new Amazon\Amazon('GB');

	        $this->setExpectedException('Exception');
	       	$item 		= $crawler->getItem('B000BEYEL8');
	    }

	    public function testNotExistingUsExtract()
	    {
	        $crawler 	= new Amazon\Amazon('US');

	        $this->setExpectedException('Exception');
	       	$item 		= $crawler->getItem('B000BEYEL8');
	    }
	}

?>