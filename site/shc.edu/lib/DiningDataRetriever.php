<?php

require_once('simple_html_dom.php');

function filter_by_class($items, $class) {
	$returns = array();
	foreach($items as $item) {
		//echo $item->class;
		if ($item->class==$class) {
			$returns[] = $item;
		}
	}
	return $returns;
}

function split_time($int_time) {
	$h = (int) substr($int_time, 0, 2);
	$m = substr($int_time, 2, 2);
	return array($h, $m);
}

function nice_time($int_time) {
	$int_time = split_time($int_time);
	$h = $int_time[0];
	$m = $int_time[1];
	
	if ($h > 12) {
		$h = $h - 12;
		$ap = "pm";
	}
	else if ($h == 0) {
		$h = 12;
		$ap = "am";
	}
	else {
		$ap = "am";
	}
	
	return $h . ":" . $m . $ap;
}

function subtract_time_in_minutes($t1, $t2) {
	$t1 = split_time($t1);
	$h1 = $t1[0];
	$m1 = $t1[1];
	$t2 = split_time($t2);
	$h2 = $t2[0];
	$m2 = $t2[1];

	$minutes = ($h1 - $h2) * 60;
	$minutes += $m1 - $m2;

	return $minutes;
}

class MenuItem {
	public $name;
	public $station;
	public $nutrition;
}

class MenuStation {
	public $name;
	public $items = array();

	public function addItem($item) {
		$this->items[$item->name] = $item;
	}
}

class MenuMeal {
	public $name;
	public $stations = array();

	public function addStation($station) {
		$this->stations[$station->name] = $station;
	}
}

class Menu {
	public $meals;
	private $menu_url;
	private $dt;
	private $service_details;
	private $cache_key_menu = "dining-daily-menu_marketplace";
	private $cache_ttl; // TODO: Get this from config file
	
	function __construct($menu_url, $service_details) {
		$this->meals = array();
		$this->menu_url = $menu_url;
		$this->service_details = $service_details;
		$this->dt = strftime('%Y%m%d');
		$this->cache_ttl = 60 * 15; // 15 minutes
	}
	
	public function addMeal($name, $meal) {
		$this->meals[$name] = $meal;
	}
	
	public function getMeal($meal_name) {
		return $this->meals[$meal_name];
	}
	
	public function getStation($meal_name, $station_name) {
		return $this->meals[$meal_name]['stations'][$station_name];
	}
	
	public function getAllItemsByMeal($meal_name) {
		$returns = array();
		foreach($this->meals[$meal_name]['stations'] as $station) {
			foreach($station['items'] as $item) {
				$returns[$item['name']] = $item;
			}
		}
		return $returns;
	}
	
	public function getItem($meal_name, $station_name, $item_name) {
		return $this->meals[$meal_name]['stations'][$station_name]['items'][$item_name];
	}
	
	public function getItemNutrition($meal_name, $station_name, $item_name) {
		return $this->meals[$meal_name]['stations'][$station_name]['items'][$item_name]['nutrition'];
	}
	
	public function getMenu() {
		// look for cached menu
		//		if found, load and return
		//		if not, parse, cache, and return
		
		//KurogoDebug::debug("cache:" . (bool) Kurogo::getCache($cache_key_menu), $halt=false);
		
		if ($mealsData = Kurogo::getCache($this->cache_key_menu)) {
			$this->meals = json_decode($mealsData, true);
			return $this->meals;
		}
		else {
			$this->fetchMenuPage($this->menu_url);
			$json_data = json_encode($this->meals);
			Kurogo::setCache($this->cache_key_menu, $json_data, $ttl = $this->cache_ttl);
			$this->meals = json_decode($json_data, true);
			return $this->meals;
		}
	}
	
	public function getValidMeals() {
		$today = strftime('%u');
		$time = strftime('%H%M');
		
		// testing
		//$today = "1";
		//$time = '0931';
		
		// step through service_details array
		// if $today is in item['days']
		//	if $time is between item['timest'] and item['timeend']
		//		add it to return array
		
		$meals = array();
		
		foreach ($this->service_details as $service => $details) {
			
			if (strpos($details["days"],$today) !== false) {
				$t1 = $details["timest"];
				$t2 = $details["timeend"];
				// only show meals that are being served now or later in the day
				if ($t1 > $time | $time <= $t2) {
					$meal = array('name'=>$service, 'start'=>nice_time($t1), 'end'=>nice_time($t2), 'status'=>'normal');
					if ($time > $t1 && $time <= $t2) {
						$meal['status']='now';
					}
					if (subtract_time_in_minutes($t2, $time) <= 30) {
						$meal['status']='panic';
					}
					$meals[] = $meal;
				}
			}
		}
		return $meals;
	}
	
	public function fetchMenuPage() {
		$html = file_get_html($this->menu_url);
		$html = $html->find('div#WucChalkboard1_NewMenu table tbody', 0);
		
		// grab the rows in the table, these will end up being the main menu "meal" sections
		$menu_meals = $html->children();
		$num_sections = count($html->children());
		
		foreach ($menu_meals as $menu_meal) {
			$MenuMeal = new MenuMeal();
			$MenuMeal->name = $menu_meal->find('td#lcGrad a img',0)->alt;
			$stations = $menu_meal->find("td#lcGrad table.ddmx td", 0)->children();

			// get the first level children with class "section"
			$stations = filter_by_class($stations, "section");
			
			foreach ($stations as $station) {

				$station_header = filter_by_class($station->children(), "menuHeader");
				$MenuStation = new MenuStation();
				$MenuStation->name = $station_header[0]-> plaintext;
				//echo $station_header[0];

				$station_items = filter_by_class($station->children(), "section");

				foreach($station_items as $item) {
					$MenuItem = new MenuItem();
					$MenuItem->name = $item->find('.header',0)->plaintext;
					$nutrition = (string) $item->find('xml',0);

					$nutrition_data = new SimpleXMLElement($nutrition);
					$nutrition_items = $nutrition_data->items;
					
					foreach ($nutrition_items as $nutrition_item) {
						$nut = array();
						foreach ($nutrition_item as $item) {
							$nut[(string) $item["n"]] = (string) $item["v"];
						}
					}
					$MenuItem->station = $MenuStation->name;
					$MenuItem->nutrition = $nut;
					$MenuStation->addItem($MenuItem);
				}

				$MenuMeal->addStation($MenuStation);
			}
			$this->addMeal($MenuMeal->name,$MenuMeal);
		}
		
	} // /function
}

class DiningDataRetriever extends URLDataRetriever {

	public $menu;
	private $url;
	private $service_details;

	function setURL($url) {
		$this->url = $url;
	}
	
	function setService($service_details) {
		$this->service_details = $service_details;
	}
	
	function makeMenu() {
		$this->menu = new Menu($this->url, $this->service_details);
		$this->menu->getMenu();
	}
	
	function valid_meals() {
		
		return $this->menu->getValidMeals();
	}

}
