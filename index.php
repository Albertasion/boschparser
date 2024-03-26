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


// pagination_page_create('https://bsc-portal.com.ua/search?searchkeywords=%D1%82%D1%80%D0%B8%D0%BC%D0%B5%D1%80', 45);
// download_all_products_pages();
parser();

