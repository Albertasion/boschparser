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
   <link rel="stylesheet" type="text/css" href="style.css"/>
    <h1>Парсер деталювань Bosch</h1>
    <p>https://bsc-portal.com.ua/</p>
    <form action="index.php" method="POST">
    <label for="url_diagram">Вкажіть посиланння на деталювання:</label>
    <input type="text" name='url_diagram' id="url_diagram"></br>
    <label for="page_qnt">Кількість сторінок пагінації:</label>
    <input type="text" name='page_qnt' id="page_qnt"></br>
    <label for="product_type_ru">Назва на рус:</label>
        <input type="text" name='product_type_ru' id="product_type_ru"></br>
        <label for="product_type_ua">Назва на укр:</label>
        <input type="text" name='product_type_ua' id="product_type_ua"></br>
        <label for="category">Категория</label>
        <input type="text" name='category' id="category"></br>
        <input type="submit" name='start_parser'></br>
     </form>




<?php  
    if (isset($_POST["url_diagram"])) {
        $url_diagram = $_POST["url_diagram"];
    }
    if (isset($_POST["page_qnt"])) {
        $page_qnt = $_POST["page_qnt"];
    }



if(isset($_POST['start_parser'])) { 
delete_all_files(); 
pagination_page_create($url_diagram, $page_qnt);
download_all_products_pages();
parser();
}
?>