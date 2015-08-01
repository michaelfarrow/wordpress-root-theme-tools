<?php

class Google_Map{

	private $root_url = 'http://maps.googleapis.com/maps/api/staticmap';
	private $parts = array();
	private $info = array();
	private $markers = array();

	public function __construct()
	{
		$this->setKey(GOOGLE_MAPS_API_KEY);
	}

	private function addPart($key, $value)
	{
		$this->parts[$key] = $value;
	}

	private function addInfo($key, $value)
	{
		$this->info[$key] = $value;
	}

	public function setKey($key)
	{
		$this->addPart('key', $key);
		$this->addInfo('key', $key);
	}

	public function setSize($width, $height)
	{
		$this->addPart('size', $width . 'x' . $height);
		$this->addInfo('size', array('width' => $width, 'height' => $height));
	}

	public function retina(){
		$this->addPart('scale', 2);
	}

	public function setZoom($zoom)
	{
		$this->addPart('zoom', $zoom);
		$this->addInfo('zoom', $zoom);
	}

	public function setCenterAddress($address)
	{
		$this->addPart('center', $address);
		$this->addInfo('center', $address);
		$this->addInfo('center_address', $address);
	}

	public function setCenterLatLng($lat, $lng)
	{
		$this->addPart('center', $lat . ',' . $lng );
		$this->addInfo('center', array('lat' => $lat, 'lng' => $lng));
		$this->addInfo('center_ll', array('lat' => $lat, 'lng' => $lng));
	}

	public function addMarker($lat, $lng)
	{
		$this->markers[] = array('lat' => $lat, 'lng' => $lng);
	}

	public function getStaticSrc()
	{
		$parts = array();

		foreach ($this->parts as $key => $value) {
			$parts[] = urlencode($key) . '=' . urlencode($value);
		}

		$markers = $this->markers;

		foreach ($markers as &$marker) {
			$marker = $marker['lat'] . ',' . $marker['lng'];
		}

		if(count($markers)>0){
			$parts[] = 'markers=' . implode('|', $markers);
		}

		return $this->root_url . '?' . implode('&', $parts);
	}

	public function get($id){
		$query = array();
		if(array_key_exists('center_ll', $this->info)){
			$query[] = 'll='.$this->info['center_ll']['lat'].','.$this->info['center_ll']['lng'];
		}
		if(array_key_exists('center_address', $this->info)){
			$query[] = 'q='.urlencode($this->info['center_address']);
		}

		return '<div id="'.$id.'" class="google-map"><a target="_blank" href="http://maps.google.com/maps/?'.implode('&', $query).'"><img class="static" src="'.$this->getStaticSrc().'"/></a></div>'.$this->getDataScript($id);
	}

	public function output($id){
		print $this->get($id);
	}

	public function getJSON()
	{
		$data = array_merge($this->info, array('markers' => $this->markers));
		return json_encode($data);
	}

	public function JSON()
	{
		print $this->getJSON();
	}

	public function getDataScript($var){
		return '<script>var google_map_data_'.str_replace('-', '_', $var).' = '.$this->getJSON().';</script>';
	}

	public function dataScript($var){
		print $this->getDataScript($var);
	}

}
