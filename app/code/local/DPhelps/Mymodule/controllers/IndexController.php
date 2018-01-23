<?php 
class DPhelps_Mymodule_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        echo "Hello Packers Fans";
    }
    
    public function testAction()
    {

        // category id for screws is 64
        $categoryId = 64;

        $products = Mage::getModel('catalog/category')->load($categoryId)
            ->getProductCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', 4);


        $productId = 91470;

        $myProduct = Mage::getModel('catalog/product')->load($productId);

        /* echo "<pre>";
            var_dump($myProduct);
        echo "</pre>"; */

        /*echo "<ul>";

        foreach ($products as $product) {

            echo "<li>" . $product->getSku() . "</li>";
        }

        echo "</ul>"; */

        /*
        echo "<pre>";
            var_dump($products);
        echo "</pre>"; */

        echo '<h3>' . $myProduct->getData('name') . '</h3>';
        echo '<p><strong>$' . $myProduct->getData('price') . '</strong></p>';
        echo '<img src="http://localhost/media/catalog/product/' . $myProduct->getData('image') . '" width="100px" />';

        $fave = '0.FSC10412UN27';
        $faveInfo = Mage::getModel('catalog/product')->loadByAttribute('sku', $fave);

        $count = 0;

        echo "<table style='text-align:center;border: 2px solid black;'>";
        echo "<tr><th>SKU</th><th>Length</th><th>Shaft Diameter</th><th>Head</th><th>Thread</th><th>Drive</th><th>Nibs</th><th>Self-Tapping</th><th>Finish</th><th>Price</th></tr>";

        foreach ($products as $product) {

            if ( $product->getPrice() < 3.00 ) {
                echo "<tr>";

                echo "<td>" . $product->getSku() . "</td><td>" . $product->getData('length_inches_value') . "</td><td>" . $product->getData('shaft_diameter_value') . "</td><td>" . $product->getData('head_value') . "</td><td>" . $product->getData('thread_value') . "</td><td>" . $product->getData('driver_value') . "</td><td>" . $product->getData('nibs_value') . "</td><td>" . $product->getData('driver_value') . "</td><td>" . $product->getData('finish_value') . "</td><td><strong>$" . number_format($product->getPrice(), 2) . "</strong></td>";
                $count++;


                echo "</tr>";
            }

            /* if ($product->getSku() === $fave) {
                echo "<pre>";
                var_dump($faveInfo);
                echo "</pre>";
            } */
        }

        echo "</table>";



        // echo "Total: " . $count . " screws.";

    }
}