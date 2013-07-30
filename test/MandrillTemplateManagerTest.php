<?php

use \SeeWah\MandrillTemplateManager\MandrillTemplateManager;

class MandrillTemplateManagerTest extends PHPUnit_Framework_TestCase {

	/**
	 * Tests publishAsDraft - new template.
	 */
	public function testPublishAsDraftNewTemplate() {
		$manager = new MandrillTemplateManager();
		$manager->setHtml('<p>Read this now!</p>');
		$manager->setText('Read this now!');
		$service = $this->getMock('Mandrill_Templates', array(), array(), '', false);
		$service->expects($this->once())->method('getList')->will($this->returnValue(array(array('name' => 'old template'))));
		$service->expects($this->once())->method('add')->with(
			$this->equalTo('my fav template'),
			$this->equalTo('a@b.com'),
			$this->equalTo('Mr C'),
			$this->equalTo('Amazing subject...'),
			$this->equalTo('<p>Read this now!</p>'),
			$this->equalTo('Read this now!'),
			$this->equalTo(false));
		$service->expects($this->never())->method('update');
		$manager->setMandrillTemplatesService($service);
		$manager->publishAsDraft('my_key', 'my fav template', 'a@b.com', 'Mr C', 'Amazing subject...');
	}

	/**
	 * Tests publishAsDraft - existing template.
	 */
	public function testPublishAsDraftExistingTemplate() {
		$manager = new MandrillTemplateManager();
		$manager->setHtml('<p>Read this now!</p>');
		$manager->setText('Read this now!');
		$service = $this->getMock('Mandrill_Templates', array(), array(), '', false);
		$service->expects($this->once())->method('getList')->will($this->returnValue(array(array('name' => 'my fav template'))));
		$service->expects($this->once())->method('update')->with(
			$this->equalTo('my fav template'),
			$this->equalTo('a@b.com'),
			$this->equalTo('Mr C'),
			$this->equalTo('Amazing subject...'),
			$this->equalTo('<p>Read this now!</p>'),
			$this->equalTo('Read this now!'),
			$this->equalTo(false));
		$service->expects($this->never())->method('add');
		$manager->setMandrillTemplatesService($service);
		$manager->publishAsDraft('my_key', 'my fav template', 'a@b.com', 'Mr C', 'Amazing subject...');
	}
}
