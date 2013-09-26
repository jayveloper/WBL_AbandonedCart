<?php

require_once Mage::getModuleDir('controllers','Mage_Checkout').DS.'CartController.php';

class WBL_AbandonedCart_AbandonedController extends Mage_Checkout_CartController
{
    /**
     *
     */
    public function loadquoteAction()
    {
        $params = $this->getRequest()->getParams();
        if(isset($params['id']))
        {
            //restore the quote
//            Mage::log($params['id']);

            $quote = Mage::getModel('sales/quote')->load($params['id']);
            if(!isset($params['token']) || (isset($params['token'])&&$params['token']!=$quote->getWblAbandonedcartToken())) {
                Mage::getSingleton('customer/session')->addNotice("Your token cart is incorrect");
                $this->_redirect('/');
            }
            else {
                $url = Mage::getStoreConfig(WBL_AbandonedCart_Model_Config::PAGE,$quote->getStoreId());
                $quote->setWblAbandonedcartFlag(1);
                $quote->save();
                if(!$quote->getCustomerId()) {
                    $this->_getSession()->setQuoteId($quote->getId());
                    $this->_redirect($url);
                }
                else {
                    if(Mage::getStoreConfig(WBL_AbandonedCart_Model_Config::AUTOLOGIN,$quote->getStoreId())) {
                        $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
                        if($customer->getId())
                        {
                            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                        }
                        $this->_redirect($url);
                    }
                    else {
                        if(Mage::helper('customer')->isLoggedIn()) {
                            $this->_redirect($url);
                        }
                        else {
                            Mage::getSingleton('customer/session')->addNotice("Login to complete your order");
                            $this->_redirect('customer/account');
                        }
                    }
                }
            }
        }
//        $this->_redirect('checkout/cart');
    }
}