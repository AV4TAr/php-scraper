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

		if($pager ==2 ){
			$continue = false;
		}

	} else {
		//no dom to read
		$continue = false;
	}
}

//GET the recod of each one
$company_records = array();	
$first_run = true; //used to get the titles and print them out;
if(count($records_urls)){
	foreach($records_urls as $record_url){
		//echo $base_url.$record_url."\n";
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
				//echo $e->plaintext."\n";
				//echo $e->children(0)->plaintext.' -> '.$e->children(1)->plaintext."\n";
				$company_record[strtolower($e->children(0)->plaintext)] = sanitaize($e->children(1)->plaintext);

			}
			if($first_run){
				$table_keys = array();
				foreach($company_record as $k => $v){
					$table_keys[] = $k;
				}
				echo implode(';', $table_keys)."\n";
				$first_run = false;
			}

			echo implode(';',$company_record)."\n";

			$company_records[] = $company_record;
		}
	}
} else {
	echo 'No URLs to scrape';
}

//Genero el csv
//echo implode("\n", $csv_records);
