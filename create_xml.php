<?php
define('BASE_URL', 'http://acessospgeotecnia.w30.mycloud.pt');
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

$check = $connect->query("SELECT * FROM issues WHERE xml_add = 0");
while($fetch = $check->fetch_array(MYSQLI_ASSOC)){

$xml_file = $fetch['xml_file'];

$idx = $fetch['id'];


$url = "http://spgeotecnia.w30.mycloud.pt/uploads/";
$file_url = $xml_file; // vem da db
$original = $url;
$replace = str_replace(array($url,".pdf"),array("",""),$file_url);
//$doi = $replace;
$ano = date("Y");
// Adicionar ponto pois é removide quando adicionado aos uploads
$ano_a = date("Y").".";
$rx = str_replace($ano,$ano_a,$replace);
$doi = $rx;


$replace_b = substr_replace($doi, '', 8);
$volume = $replace_b;

$replace_c = substr_replace($doi, '', 0,-2);
$doi_sub = $replace_c;

$mes = date('m');
$ano = date("Y");
$timestamp = date('mdYHis');

$myfile = fopen( $doi.".xml", "w") or die("Unable to open file!");
$xmlstr = <<<XML
<!--?xml version="1.0" encoding="UTF-8"?-->
<getautomb_key>
<doi_batch version="4.3.7" xmlns="http://www.crossref.org/schema/4.3.7" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.crossref.org/schema/4.3.7 http://www.crossref.org/schema/deposit/crossref4.3.7.xsd">
<head>
<doi_batch_id>spggeot142</doi_batch_id>
<timestamp>$timestamp</timestamp>
<depositor>
  <depositor_name>portgeo</depositor_name> 
  <email_address>arquivo@revistageotecnia.com</email_address>
</depositor>
<registrant>portgeo</registrant> 
</head>
<body>
<journal>
  <journal_metadata>
    <full_title>Geotecnia</full_title>
    <abbrev_title>Geot</abbrev_title>
    <issn media_type='print'>03799522</issn>
    <issn media_type='electronic'>03799522</issn>
    <doi_data><doi>10.24849/j.geot</doi> <resource>http://spgeotecnia.w30.mycloud.pt/repository/$idx</resource> 
    </doi_data>
  </journal_metadata>
  <journal_issue>
    <publication_date media_type='print'>
      <month>$mes</month>
      <year>$ano</year>
    </publication_date>
    <publication_date media_type='online'>
      <month>$mes</month>
      <year>$ano</year>
    </publication_date>
    <journal_volume>
      <volume>$doi_sub</volume>
    </journal_volume>
    <doi_data>
      <doi>10.24849/j.geot.$volume</doi><resource>$file_url</resource> 
    </doi_data>
  </journal_issue>
  <!-- ============== -->
  
  <!-- ============== -->
</journal>
</body>
</doi_batch>
</getautomb_key>
XML;

$txt = $xmlstr;

fwrite($myfile, $txt);

fclose($myfile);


// Atualizar db pois ja adicionamos este artigo ao XML
$docid = $fetch['id'];
$update = $connect->query("UPDATE issues SET xml_add = 1 WHERE id = '$docid'");


$param = $doi.".xml";
$order = 0;


if($connect->query("INSERT INTO uploads(file) VALUES('$param')")){

  //echo "Completo";

}else{
  $error = $connect->error;
  echo $error;
}


}
