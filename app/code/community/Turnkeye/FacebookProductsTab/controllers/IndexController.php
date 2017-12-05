<?php
/**
 * TurnkeyE Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send
 * an email to info@turnkeye.com so we can send you a copy immediately.
 *
 * @category   Turnkeye
 * @package    Turnkeye_FacebookProductsTab
 * @copyright  Copyright (c) 2010-2012 TurnkeyE Co. (http://turnkeye.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Controller for facebook tab page
 *
 * @category   Turnkeye
 * @package    Turnkeye_FacebookProductsTab
 * @author     Viacheslav Fedorenko <v.fedorenko@turnkeye.com>
 */

class Turnkeye_FacebookProductsTab_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * index action
     */
    public function indexAction()
    {
        $this->loadLayout('facebookproductstab_page');
        $this->renderLayout();
    }

}
