<?php
define('BASE_URL', 'LINK DO WEBSITE');
error_reporting(E_ALL);
ini_set('display_errors', '1');
$config['database']['host'] = "localhost";
$config['database']['user'] = "";
$config['database']['pass'] = "";
$config['database']['db'] = "";

/*==========================  CUSTOM-PHP EDIT XML  ============================== */
/*==========================     Version 3.0     ============================= */

/*
        Developed by Gonçalo M
 */

$connect = new mysqli($config['database']['host'],$config['database']['user'],$config['database']['pass'],$config['database']['db']);  


// Verificar inputs que tenham 0 em XML_ADD

$check = $connect->query("SELECT * FROM issueslist WHERE xml_add = 0");
while($fetch = $check->fetch_array(MYSQLI_ASSOC)){


$titulo = $fetch['titulo_pt'];
$titulo_en = $fetch['titulo_en'];
$ficheiro_link = $fetch['link'];
$doi_link = $fetch['doi'];
$first_page = $fetch['first_page'];
$last_page = $fetch['last_page'];

$xml_file = $fetch['link'];


$url = BASE_URL."/uploads/";
$file_url = $xml_file; // vem da db
$original = $url;
$replace = str_replace(array($url,".xml"),array("",""),$xml_file);
$doi = $replace;




//trim off excess whitespace off the whole
$text = trim($fetch['autorxml']);
//explode all separate lines into an array
$textAr = explode("\n", $text);
//trim all lines contained in the array.
$textAr = array_filter($textAr, 'trim');
//loop through the lines
//var_dump($textAr);

$linhas = preg_split('/\n|\r/',$text);
$total_lines = count($linhas); 

$xml_doc = new DomDocument;
$xml_doc->formatOutput = true;
$xml_doc->preserveWhiteSpace = false;
$xml_doc->Load($xml_file);

$journal = $xml_doc->getElementsByTagName('journal')->item(0);
$journal_article = $xml_doc->createElement('journal_article');
$journal_atr = $xml_doc->createAttribute('publication_type');
// Value for the created attribute
$journal_atr->value = 'full_text';


$titles = $xml_doc->createElement('titles');
$title = $xml_doc->createElement('title', $titulo_en);
$original_language_title = $xml_doc->createElement('original_language_title',$titulo);


$contributors = $xml_doc->createElement('contributors');

$i = 0;
foreach($textAr as $line) {
 

    // Loop para diferenciar nomes para add childs
    $arr = array('a','b','c','d','e','f','g','h','i','j','l','m','n','o','p','q','r','s','t','u','x','y','z');  
    $contrib = explode(",", $line);

    //echo $contrib[0]."<br>";
    //echo $contrib[1]."<br>";
    //echo $contrib[2]."<br>";
    //echo $contrib[3]."<br>";

    $var = $arr[$i];
   
    ${"person_name_$i"} = $xml_doc->createElement('person_name');
    ${"given_name_$i"} = $xml_doc->createElement('given_name',$contrib[0]);
    ${"sur_name_$i"} = $xml_doc->createElement('sur_name',$contrib[1]);
    ${"orcid_$i"} = $xml_doc->createElement('ORCID',$contrib[2]);
    ${"organization_$i"} = $xml_doc->createElement('organization',$contrib[3]);
    $i++; 

}


$mes = date('m');
$ano = date("Y");

$publication_date = $xml_doc->createElement('publication_date');
$month = $xml_doc->createElement('month',$mes);
$year = $xml_doc->createElement('year',$ano);
$publication_date_o = $xml_doc->createElement('publication_date');
$month_b = $xml_doc->createElement('month',$mes);
$year_b = $xml_doc->createElement('year',$ano);
$pub_Attribute = $xml_doc->createAttribute('media_type');
// Value for the created attribute
$pub_Attribute->value = 'print';
$pub_Attribute_b = $xml_doc->createAttribute('media_type');
// Value for the created attribute
$pub_Attribute_b->value = 'online';
$pages = $xml_doc->createElement('pages');
$first_page = $xml_doc->createElement('first_page',$first_page);
$last_page = $xml_doc->createElement('last_page',$last_page);


$count = 1;
$journalx = $xml_doc->getElementsByTagName('journal_article');
foreach($journalx as $item){
       
        $count++; 


}


	
$num = sprintf("%02d", $count);
$cc = $num;






$dd = "".$doi.".".$cc;
$dd = preg_replace('/\s+/', '', $dd);




$doi_link = "10.24849/j.geot.".$dd;
$doi_data = $xml_doc->createElement('doi_data');
$doi = $xml_doc->createElement('doi',$doi_link);
$resource = $xml_doc->createElement('resource',$ficheiro_link);


######## COMEÇA AQUI

$journal->appendChild($journal_article);
$journal_article->appendChild($titles);
$journal_article->appendChild($journal_atr);
$titles->appendChild($title);
$titles->appendChild($original_language_title);
$journal_article->appendChild($contributors);

// Aqui tenho de geral um loop para possibilitar vários contributors e usar explode para separar paramentros
$x = 0;
foreach($textAr as $line) {
 
    // Loop para diferenciar nomes para add childs
    $arr = array('a','b','c','d','e','f','g','h','i','j','l','m','n','o','p','q','r','s','t','u','x','y','z');

    //echo $contributors[0];
    //echo $contributors[1];
    //echo $contributors[2];
    $i = $x;

    $var = $arr[$i];
    
    $contributors->appendChild(${"person_name_$i"});
    ${"person_name_$i"}->appendChild(${"given_name_$i"});
	${"person_name_$i"}->appendChild(${"sur_name_$i"});
	$contributors->appendChild(${"orcid_$i"});
	$contributors->appendChild(${"organization_$i"});
    $x++; 

}

///

$journal_article->appendChild($publication_date);
$publication_date->appendChild($pub_Attribute);
$publication_date->appendChild($month);
$publication_date->appendChild($year);
$journal_article->appendChild($publication_date_o);
$publication_date_o->appendChild($pub_Attribute_b);
$publication_date_o->appendChild($month_b);
$publication_date_o->appendChild($year_b);

$journal_article->appendChild($pages);
$pages->appendChild($first_page);
$pages->appendChild($last_page);

$journal_article->appendChild($doi_data);
$doi_data->appendChild($doi);
$doi_data->appendChild($resource);

$formatx = ".xml";
$s = $replace.$formatx;
$done = $xml_doc->save($s);



// Atualizar db pois ja adicionamos este artigo ao XML
$docid = $fetch['id'];
$update = $connect->query("UPDATE issueslist SET xml_add = 1 WHERE id = '$docid'");

}
