<?php
namespace Facebook\WebDriver;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
define('BASE_URL', 'https://bsc-portal.com.ua');
require_once('vendor/autoload.php');
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('max_execution_time', 0);
    include_once('phpQuery.php');
    require_once('functions.php');


pagination_page_create('https://bsc-portal.com.ua/search?searchkeywords=%D1%82%D1%80%D0%B8%D0%BC%D0%B5%D1%80', 45);
download_all_products_pages();

$conn = connect_to_db();


//масыви для складання для вигрузки в ексель
$products_for_export=[];
$all_images = [];
$product_description_arr = [];
$sku_for_export = [];

//перебирает все деталировки из категории products и парсит данные
$dir_files_pages = 'products';
$files_in_directory = scandir($dir_files_pages);
foreach ($files_in_directory as $keys=>$files) {
if ($files[0]!=='.' && $files[1]!=='.') {
$doc = file_get_contents($dir_files_pages.'/'.$files);
$document = \phpQuery::newDocument($doc);
//сырое название 
$product_name = $document->find('h1');
$product_name = pq($product_name)->text();
$product_name_withoutvoltage = preg_replace("/(\S+?) V/", "", $product_name);
$product_name_explode = explode(" | ", $product_name_withoutvoltage);

//назва моделі!!
$name_model = $product_name_explode[0];
$name_model = preg_replace('/\p{Cyrillic}+/u', '', $name_model);
//пропускаем пустие модели
if ($name_model==' - '){
    continue;
}


//арткул деталировки
$sku_diagram = $product_name_explode[1];
$sku_diagram = str_replace(' ', '', $sku_diagram);
$sku_diagram=trim($sku_diagram);
//убираем все спецсимволы
$sku_diagram = preg_replace('/[^\p{L}\p{N}]/u', '', $sku_diagram);
//арткул деталировки чистий
$sku_diagram = str_replace('EU', '', $sku_diagram);
//проверяем есть ли уже такая модель на сайте
$sql = "SELECT sp.name_ru FROM sc_products as sp LEFT JOIN sc_categories as sc ON sp.categoryID=sc.categoryID WHERE sc.parts_view = 1 AND sp.name_ru LIKE '%" . $sku_diagram . "%'";
$result = $conn->query($sql);
if ($result->num_rows !== 0) {
    continue;
} 
else {
    if(!in_array($sku_diagram, $sku_for_export)) {
    $sku_for_export[] = $sku_diagram;
}
else {
    continue;
}
}


//собираем полную строку для названия
$product_name_ru = 'Запчасти для ' .'Bosch'. $name_model. ' ' .'('.$sku_diagram.')';
//собираем полные названия
$products_for_export[] = $product_name_ru;
//картинки
$images_arr = [];
$product_image_str = [];

$images = $document->find('.scheme');
$images = $images->find('img');
foreach($images as $key =>$value) {
    $img = pq($value);
    $img = $img->attr('src');
    $images_arr[] = $img;
}
$imge_str = implode(';', $images_arr);
$all_images[] = $imge_str;






$product_desc = [];


// описание
$table = $document->find('.trrow');
foreach ($table as $key =>$value){
    //пропускаем первую итерацию. там где заголовки таблицы
    if ($key === 0) {
        $product_desc[$key] = '<p>Первая строка</p>';
    }
    else {
    $_table = pq($value);
    $pos = $_table->find('.poscol')->text();
    $sku = $_table->find('.artcol')->text();
    $sku = str_replace(' ', '', $sku);
$row_string_product = '<p>'.$pos. '|'. $sku.'|'.'</p>';
$product_desc[$key] = $row_string_product;
}
}
$product_description = implode("\n", $product_desc);
$product_description_arr[] = $product_description;
        }
    }
    
$conn->close();

    $spreadsheet = new Spreadsheet();
    $products_for_export = array_chunk($products_for_export, 1);
    $images_for_export = array_chunk($all_images, 1);
    $description_for_export = array_chunk($product_description_arr, 1);
    $sku_for_export = array_chunk($sku_for_export, 1);

$sheet = $spreadsheet->getActiveSheet()->fromArray($sku_for_export, NULL, 'A1');
$sheet = $spreadsheet->getActiveSheet()->fromArray($products_for_export, NULL, 'B1');
$sheet = $spreadsheet->getActiveSheet()->fromArray($images_for_export, NULL, 'C1');
$sheet = $spreadsheet->getActiveSheet()->fromArray($description_for_export, NULL, 'D1');

$writer = new Xlsx($spreadsheet);
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

$writer->save('export_product.xlsx');