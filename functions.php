<?php
namespace Facebook\WebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
function format ($expre) {
    echo "<pre>";
    print_r($expre);
    echo "</pre>";
  }
  ///підключення до бази
  function connect_to_db () {
    $servername = "localhost";
    $username = "strument_usr"; 
    $password = "Mqky4Crd";
    $dbname = "strument_str_test";
    $conn = new \mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}


//завантаження всіх сторінок пагінаційю
//обхід і загрузка всіх сторінок с пагінацією
function pagination_page_create($url, $page_qnt) {
    for ($i = 2; $i <= $page_qnt; $i++) {
        $url_with_page_arr= parse_url($url);
        $url_with_page =  $url_with_page_arr['scheme'].'://'.$url_with_page_arr['host'].'/search/page/'.$i.'?'.$url_with_page_arr['query'];
silenium_request_category($url_with_page);
}
}

//загрузка всых сторынок с пагынацією. Складання всі сторінок з пагинацією в папку downloads
function silenium_request_category($url) {
    $host = 'http://localhost:9515';
    $capabilities = DesiredCapabilities::chrome();
    $driver = RemoteWebDriver::create($host, $capabilities);
    $currentURL= parse_url($url);
    $driver->get($url);
            $pattern = '/\/(\d+)$/';
            if (preg_match($pattern, $currentURL['path'], $matches)) {
                $pageNumber = $matches[1]; // Выведет 2
            } else {
                echo "Число не найдено";
            }
            parse_str($currentURL['query'], $output);
            $pageName = $output['searchkeywords'];
            $output = $driver->getPageSource();
    file_put_contents('downloads/'.$pageName.$pageNumber.'.html', $output);
    $driver->close();
return;

}






//збір всіх посилань на деталювання
function collect_all_products () {
    $links = [];
    $dir_files_pages = 'downloads';
    $files_in_directory = scandir($dir_files_pages);
    foreach ($files_in_directory as $keys=>$files) {
        if ($files[0]!=='.' && $files[1]!=='.') {
      $doc = file_get_contents($dir_files_pages.'/'.$files);
      $document = \phpQuery::newDocument($doc);
      $link = $document->find('.nameDetail a');
      foreach($link as $key => $value){
        $pq = pq($value);
        $_link = $pq->attr('href');
        $pos = strpos($_link, '-eu-');
        if ($pos !== false) {
                    $links[] = $_link;
                } else {
                    continue;
                }
        }
    }
    }
    return $links;
    }

    //закачує всы товари з категорії в папку products
function download_all_products_pages () {
    $links = collect_all_products ();
    foreach ($links as $key=>$link){    
        $full_link = BASE_URL.$link;
        echo $full_link.'<br>';
        silenium_request($full_link, $key);
    }
    return;
    }
    //загрузка сторінки схемы і складання в папку products
function silenium_request($url, $name_file) {
    $directory_product_files = 'product';
    $host = 'http://localhost:9515';
    $capabilities = DesiredCapabilities::chrome();
    $driver = RemoteWebDriver::create($host, $capabilities);
    $driver->get($url);
    $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('trrow')));
    $html = $driver->getPageSource();
    file_put_contents('products/'.'product'.$name_file.'.html', $html);
    $driver->close();  
  return 1;
    } 

    function parser (){
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
        }