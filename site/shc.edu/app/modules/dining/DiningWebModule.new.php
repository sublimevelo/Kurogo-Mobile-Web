<?php

class DiningWebModule extends WebModule
{
	protected $id='dining';
	protected function initializeForPage()
	{
		
		// FIXME!!
		$url = 'http://www.campusdish.com/en-US/CSS/SpringHill';
		$service_details = array(
			"Breakfast"=>array("days"=>"12345","timest"=>"0700","timeend"=>"1000"),
			"Brunch"=>array("days"=>"67","timest"=>"0800","timeend"=>"1000"),
			"Lunch"=>array("days"=>"12345","timest"=>"1100","timeend"=>"1330"),
			"Dinner"=>array("days"=>"1234567","timest"=>"1730","timeend"=>"1900")
		);
		
		// instantiate controller
		$controller = DataRetriever::factory('DiningDataRetriever', array());
		$controller->setURL($url);
		$controller->setService($service_details);
		$controller->makeMenu();
		
		switch ($this->page)
		{
			case 'index':
			
				if ($controller->menu->valid) {
			
					$mealData = $controller->valid_meals();
					//KurogoDebug::debug($mealData, $halt=false);

					//prepare the list
					$mealList = array();
					foreach ($mealData as $meal) {
						
						//KurogoDebug::debug($controller->menu->getMeal("Lunch"), $halt=false);
						$sub = "served " . $meal['start'] . " - " . $meal['end'];
						if ($meal['status'] == "now") {
							$sub .= " now serving!";
						}
						if ($meal['status'] == "panic") {
							$sub .= " (better hurry)";
						}
						$mealItem = array(
							'title'=> $meal['name'],
							'subtitle'=> $sub,
							'url'=> $this->buildBreadcrumbURL('stations', array('meal'=>$meal['name']))
						);
						$mealList[] = $mealItem;
					}
					
					//assign the list to the template
					$this->assign('mealList', $mealList);
				} else {
					//assign the list to the template
					$this->assign('mealList', null);
				}
				
				break;
				
			case 'stations':
				$meal = $this->getArg('meal');
				//KurogoDebug::debug($controller->menu->getMeal($name), $halt=false);
				if ($stationData = $controller->menu->getMeal($meal)) {
					$stationData = $stationData['stations'];
					//KurogoDebug::debug($stationData, $halt=false);
					$stationList = array();
					foreach($stationData as $name=>$station) {
						//KurogoDebug::debug($station, $halt=false);
						$stationItem = array(
							'title'=> $station['name'],
							'subtitle'=>"",
							'url'=> $this->buildBreadcrumbURL('station', array('meal'=>$meal, 'station'=>$station['name']))
						);
						$stationList[] = $stationItem;
					}
				} else
				{
					$this->redirectTo('index');
				}
				
				// add an "all" station
				$stationList[] = array(
					'title'=>'Show me everything!',
					'subtitle'=>'',
					'url'=>$this->buildBreadcrumbURL('all', array('meal'=>$meal))
				);
				
				//assign the list to the template
				$this->assign('page', array('title'=>'Stations'));
				$this->assign('stationList', $stationList);
				
				break;			
				
			case 'station':
				$meal = $this->getArg('meal');
				$station = $this->getArg('station');
				$station_name = "";
				//KurogoDebug::debug($station, $halt=true);
				//KurogoDebug::debug($controller->menu->getStation($meal, $station), $halt=true);
				if ($itemData = $controller->menu->getStation($meal, $station)) {
					$station_name = $itemData['name'];
					$itemData = $itemData['items'];
					//KurogoDebug::debug($stationData, $halt=false);
					$itemList = array();
					foreach($itemData as $item) {
						//KurogoDebug::debug($item, $halt=true);
						$itemItem = array(
							'title'=> $item['name'],
							'subtitle'=>"",
							'url'=> $this->buildBreadcrumbURL('detail', array('meal'=>$meal, 'station'=>$station, 'item'=>$item['name']))
						);
						$itemList[] = $itemItem;
					}
					
				} else
				{
					$this->redirectTo('index');
				}
				
				//assign the list to the template
				$this->assign('page', array('title'=>$station_name));
				$this->assign('itemList', $itemList);
				
				break;
				
			case 'all':
				$meal = $this->getArg('meal');
				//KurogoDebug::debug($controller->menu->getAllItemsByMeal($meal), $halt=true);
				if ($itemData = $controller->menu->getAllItemsByMeal($meal)) {
					//$stationData = $stationData['stations'];
					//KurogoDebug::debug($stationData, $halt=false);
					$itemList = array();
					foreach($itemData as $item) {
						//KurogoDebug::debug($station, $halt=false);
						$itemItem = array(
							'title'=> $item['name'],
							'subtitle'=> $item['station'],
							'url'=> $this->buildBreadcrumbURL('detail', array('meal'=>$meal, 'station'=>$item['station'], 'item'=>$item['name']))
						);
						$itemList[] = $itemItem;
					}
					
				} else
				{
					$this->redirectTo('index');
				}
				
				//assign the list to the template
				$this->assign('page', array('title'=>'All Stations'));
				$this->assign('itemList', $itemList);
				
				break;
			
			case 'detail':
				$meal = $this->getArg('meal');
				$station = $this->getArg('station');
				$item = $this->getArg('item');
				//KurogoDebug::debug($controller->menu->getItem($meal, $station, $item), $halt=true);
				if ($itemData = $controller->menu->getItem($meal, $station, $item)) {
					//$itemData = $itemData->items;
					//KurogoDebug::debug($stationData, $halt=false);
					//$itemList = array();
					
					$this->assign('item', array(
						'title'=> $itemData['name'],
						'subtitle'=>"",
						'content'=>$itemData['nutrition']
					));
					
				} else
				{
					$this->redirectTo('index');
				}
				
				break;
		}
	}
}
