<?php

class Aitoc_Aitcheckout_Model_Onlyif_Observer
{
    public function reloadShippingMethod(Varien_Event_Observer $observer)
    {
        $block     = $observer->getBlock();
        $transport = $observer->getTransport();
        $postHtml  = '<script type="text/javascript">shippingMethod.update();</script>';// here we empty gift options when Gift Wrapping block loaded. This is done to avoid preserving old Gift Wrapping Info in the Cart

        if ($block instanceof Enterprise_GiftWrapping_Block_Checkout_Options) {
            if ($block->canDisplayGiftWrapping() && !Mage::helper('aitcheckout/data')->isDisabled()) {
                $html = $transport->getHtml();
                $html = $html . $postHtml;
                $transport->setHtml($html);
            }
        }
    }
}
