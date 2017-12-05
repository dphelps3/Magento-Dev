<?php
/**
 * Trivera_Seotext extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Trivera
 * @package        Trivera_Seotext
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Treemenu model
 *
 * @category    Trivera
 * @package     Trivera_Seotext
 * @author      Trivera
 */
class Trivera_Seotext_Model_Seotext extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     *
     * @access public
     * @return void
     * @author Trivera
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('trivera_seotext/seotext');
    }
}
