<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

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