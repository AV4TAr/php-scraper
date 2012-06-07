<?php
include('simple_html_dom.php');

$base_url = 'http://www.di.net';
$field_separator = ';';

/**
* Cleans a string and remove unwanted characters
* @param string $text
*/
function sanitaize($text){
	global $field_separator; //i know i know.. it was kiss

	$text = html_entity_decode($text); //decode html to actual stuff
	$text = preg_replace("/[\n\r]/"," - ",$text); //remove line breaks
	$text = str_replcae('&#039;"', '\'', $text);
	$text = str_replace('&amp;', '&', $text);
	$text = str_replace($field_separator, ',', $text); 
	return (string) $text;
}

//FIRST GET THE urls of all the companies
$initial_url = $base_url.'/almanac/firms/page';
$pager = 1; //used to change the url to fetch other pages

$selector = 'table.firm_list tbody tr td a'; //CSS selector of the link
$continue = true; //flag to stop scrapping
$html = file_get_html($initial_url.$pager);
$records_urls = array();
while($continue){
	//echo "Pagina: ".$pager."\n";
	$find_dom = $html->find($selector); //select the DOM of the link
	if(count($find_dom) > 0 ){
		foreach ($find_dom as $e) {
			$records_urls[] = $e->href;  //get the link
		}
		//will try next page
		$pager++;
		$html = file_get_html($initial_url.$pager);
	} else {
		//no dom to read stop scraping links
		$continue = false;
	}
}

//GET the recod of each one of those links
$company_records = array();	
$record_keys = array("url"=>"Url", "title"=>"Title");
$i = 1;
if(count($records_urls)){
	foreach($records_urls as $record_url){
		$final_record_url = $base_url.$record_url;
		//echo $i."-> ".$final_record_url."\n";
		$i++;
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
