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
    ?>
    <form action="index.php" method="POST">
    <input type="text" name='url_diagram' value="Посилання">
    <input type="text" name='page_qnt' value="Кількість сторінок">
        <input type="text" name='product_type_ru' value="Название на русс">
        <input type="text" name='product_type_ua' value="Название на укр">
        <input type="submit" name='start_parser'>
     </form>




<?php  
    if (isset($_POST["url_diagram"])) {
        $url_diagram = $_POST["url_diagram"];
    }
    if (isset($_POST["page_qnt"])) {
        $page_qnt = $_POST["page_qnt"];
    }


if(isset($_POST['start_parser'])) {  
pagination_page_create($url_diagram, $page_qnt);
download_all_products_pages();
parser();
}
?>