<?php
use PrestaShop\PrestaShop\Adapter\Entity\Product;

require dirname(__FILE__).'/config/config.inc.php';
include(dirname(__FILE__).'/init.php');
$db = \Db::getInstance();

$request="SELECT * FROM `' . _DB_PREFIX_ . 'product`";
/** @var array $result */
$result = $db->executeS($request);

dump($result);

$fila = true;
if (($gestor = fopen(_PS_ROOT_DIR_ . "/miscsv/products.csv", "r")) !== FALSE) {
    while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
        $numero = count($datos);
        //echo "<p> $numero de campos en la línea $fila: <br /></p>\n";
        $fila++;
        if ($fila) {
            $fila = false;
            continue;
        } for ($c=0; $c < $numero; $c++) {
            echo $datos[$c] . "<br />\n";
        }
        
        //
        $product = new Product();
        $product->name=$datos[0];
        $product->reference=$datos[1];
        $product->ean13=$datos[2];
        $product->wholesale_price=$datos[3];
        $product->price=$datos[4];
        $product->ecotax=$datos[5];
        $product->quantity=$datos[6];
        $product->category=$datos[7];
        $product->manufacturer_name=$datos[8];
        $product->add();
        $product->addToCategories();
        //

    }
    fclose($gestor);
}

