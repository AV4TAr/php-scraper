<?php
//error_reporting(0);
include('simple_html_dom.php');

$initial_url = 'http://www.di.net/almanac/firms/page';
$columns = array(0=>"Firm", 4=>"Headquarters", 5=>"Revenue", 6=>"Staff", 7=>"Offices", 8=>"Year");
$selector = 'table.firm_list tbody tr';


/**
* Cleans a string
* @param string $text
*/
function sanitaize($text){
	$text = html_entity_decode($text);
	return $text;
}

$pager = 1;
$continue = true;
$html = file_get_html($initial_url.$pager);
while($continue){
	$find_dom = $html->find($selector);
	if(count($find_dom) > 0 ){
		foreach ($find_dom as $e) {
			$record = array();
			foreach($columns as $col_index => $col_name) {
				$record[]=sanitaize($e->children($col_index)->plaintext);
			}
			$csv_record = implode(';', $record);;
			echo $csv_record."\n";
		}

		//intento la siguiente pagina
		$pager++;
		$html = file_get_html($initial_url.$pager);

	} else {
		//no dom to read
		$continue = false;
	}

}
//Genero el csv
//echo implode("\n", $csv_records);
