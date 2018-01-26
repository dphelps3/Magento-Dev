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

        $imgPathPre = "<img src='https://www.cshardware.com/media/images/";
        $imgPathPost = "' width='30px'/>";

        echo "<table style='text-align:center;border: 2px solid black;'>";
        echo "<tr><th>SKU</th><th>Length</th><th>Shaft Diameter</th><th>Head</th><th>Thread</th><th>Drive</th><th>Nibs</th><th>Self-Tapping</th><th>Finish</th><th>Price</th></tr>";

        foreach ($products as $product) {

            // check to make sure the screw is less than $3/ea. (weeds out the bulk screw buckets)
            if ( $product->getPrice() < 3.00 ) {

                // determine which image to show for the head, drive, and finish values
                // head
                switch ( strtolower($product->getData('head_value')) ) {

                    case 'flat':
                        $head = $imgPathPre . 'flathead_black.png' . $imgPathPost;
                        break;

                    case 'pan':
                        $head = $imgPathPre . 'panhead_black.png' . $imgPathPost;
                        break;

                    case 'modified pan':
                        $head = $imgPathPre . 'modified-pan_black.png' . $imgPathPost;
                        break;

                    case 'truss':
                        $head = $imgPathPre . 'truss_black.png' . $imgPathPost;
                        break;

                    case 'round washer':
                        $head = $imgPathPre . 'round-washer_black.png' . $imgPathPost;
                        break;

                    case 'large round washer':
                        $head = $imgPathPre . 'largeRw-black.png' . $imgPathPost;
                        break;
                    case 'power head':
                        $head = $imgPathPre . 'powerhead_black.png' . $imgPathPost;
                        break;
                    case 'undercut':
                        $head = $imgPathPre . 'undercut_black.png' . $imgPathPost;
                        break;

                    case 'bugle':
                        $head = $imgPathPre . 'bugle_black.png' . $imgPathPost;
                        break;

                    case 'oval':
                        $head = $imgPathPre . 'oval_black.png' . $imgPathPost;
                        break;

                    case 'trim flat':
                        $head = $imgPathPre . 'trim-flat_black.png' . $imgPathPost;
                        break;

                    case 'hex':
                        $head = $imgPathPre . 'hex_black.png' . $imgPathPost;
                        break;

                    default:
                        $head = '';
                }


                // drive
                switch ( strtolower($product->getData('driver_value')) ) {

                    case 'phillips':
                        $drive = $imgPathPre . 'phillipsdrive_sm.png' . $imgPathPost;
                        break;

                    case 'combo':
                        $drive = $imgPathPre . 'combo_sm.png' . $imgPathPost;
                        break;

                    case 'square':
                        $drive = $imgPathPre . 'squaredrive_sm.png' . $imgPathPost;
                        break;

                    case 'pozidriv':
                        $drive = $imgPathPre . 'pozidrive_sm.png' . $imgPathPost;
                        break;

                    case 'other':
                        $drive = '';
                        break;

                    default:
                        $drive = '';
                }

                // finish
                switch ( strtolower($product->getData('finish_value')) ) {

                    case 'zinc':
                        $finish = $imgPathPre . 'zinc-swatch.gif' . $imgPathPost;
                        break;

                    case 'black':
                        $finish = $imgPathPre . 'black-swatch.gif' . $imgPathPost;
                        break;

                    case 'plain':
                        $finish = $imgPathPre . 'plain-swatch.gif' . $imgPathPost;
                        break;

                    case 'antique brass':
                    $finish = $imgPathPre . 'antique-brass-swatch.gif' . $imgPathPost;
                    break;

                    case 'bright brass':
                        $finish = $imgPathPre . 'bright-brass-swatch.gif' . $imgPathPost;
                        break;

                    case 'ultraguard':
                        $finish = $imgPathPre . 'ultraguard-swatch.gif' . $imgPathPost;
                        break;

                    case 'white':
                        $finish = $imgPathPre . 'blank.gif' . $imgPathPost;
                        break;

                    case 'stainless steel':
                        $finish = $imgPathPre . 'stainless-steel-swatch.gif' . $imgPathPost;
                        break;

                    case 'statuatory-bronze':
                        $finish = $imgPathPre . 'statuatory-bronze-swatch.gif' . $imgPathPost;
                        break;

                    case 'protec-kote':
                        $finish = $imgPathPre . 'protec-kote-swatch.gif' . $imgPathPost;
                        break;

                    case 'blue-kote':
                        $finish = $imgPathPre . 'blue-kote-swatch.gif' . $imgPathPost;
                        break;

                    case 'nickel':
                        $finish = $imgPathPre . 'nickel-swatch.gif' . $imgPathPost;
                        break;

                    case 'nylan':
                        $finish = $imgPathPre . 'nylan-swatch.gif' . $imgPathPost;
                        break;

                    case 'other':
                        $finish = '';
                        break;

                    default:
                        $finish = '';
                }

                echo "<tr>";

                echo "<td>" . $product->getSku() . "</td><td>" . $product->getData('length_inches_value') . "</td><td>" . $product->getData('shaft_diameter_value') . "</td><td>" . $head . $product->getData('head_value') . "</td><td>" . $product->getData('thread_value') . "</td><td>" . $drive . $product->getData('driver_value') . "</td><td>" . $product->getData('nibs_value') . "</td><td>" . $product->getData('driver_value') . "</td><td>" . $finish . $product->getData('finish_value') . "</td><td><strong>$" . number_format($product->getPrice(), 2) . "</strong></td>";
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