<?php 
namespace MIT\Pages\Api;

interface ContentManagementInterface {

	/**
	 * GET  Content for HowToBuy api
	 * @return array
	 */
	
	public function getContent() :array;
}