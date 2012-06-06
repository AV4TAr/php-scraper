<?php
error_reporting(0);
include('simple_html_dom.php');
$base_url = 'http://www.di.net';


/**
* Cleans a string
* @param string $text
*/
function sanitaize($text){
	$text = html_entity_decode($text); //decode html to actual stuff
	$text = preg_replace("/[\n\r]/"," - ",$text); //remove line breaks
	$text = str_replace(";", ",", $text); //remove ;
	return (string) $text;
}

//FIRST GET THE urls of all the companies
$initial_url = $base_url.'/almanac/firms/page';
$selector = 'table.firm_list tbody tr td a';
$pager = 1;
$continue = true;
$html = file_get_html($initial_url.$pager);
$records_urls = array();
while($continue){
	$find_dom = $html->find($selector);
	if(count($find_dom) > 0 ){
		foreach ($find_dom as $e) {
			$records_urls[] = $e->href;
		}
		//intento la siguiente pagina
		$pager++;
		$html = file_get_html($initial_url.$pager);
	} else {
		//no dom to read
		$continue = false;
	}
}

//GET the recod of each one
$company_records = array();	
$record_keys = array("url"=>"Url", "title"=>"Title");
if(count($records_urls)){
	foreach($records_urls as $record_url){
		$final_record_url = $base_url.$record_url;
		$html = file_get_html($final_record_url);
		if($html){
			//Get the name of the company
			$find_dom = $html->find("#title");
			$company_title = $find_dom[0]->plaintext;

			//Get all the info
			$find_dom = $html->find('table.firm_info tbody tr');
			$company_record = array("url"=>$final_record_url, "title"=>$company_title);
			foreach($find_dom as $e){
				$company_record[strtolower($e->children(0)->plaintext)] = sanitaize($e->children(1)->plaintext);
				if(!isset($record_keys[$e->children(0)->plaintext])){
					$record_keys[strtolower($e->children(0)->plaintext)]=$e->children(0)->plaintext;
				}
			}
			$company_records[] = $company_record;
		}
	}
} else {
	echo 'No URLs to scrape';
}

echo implode(";",$record_keys)."\n";

foreach($company_records as $company_record){
	foreach($record_keys as $key => $value){
		if(isset($company_record[strtolower($key)])){
			$print_record[strtolower($key)] = $company_record[strtolower($key)];
		} else {
			$print_record[strtolower($key)] = '';
		}
	}
	echo implode(";", $print_record)."\n";
}
