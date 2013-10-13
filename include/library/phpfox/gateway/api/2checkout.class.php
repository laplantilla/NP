<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 * 2checkout Payment Gateway API
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author			Raymond Benc and Fernando Gutierrez
 * @package 		Phpfox
 * @version 		$Id: 2checkout.class.php 6637 2013-09-12 13:33:06Z Fern $
 */
class Phpfox_Gateway_Api_2checkout implements Phpfox_Gateway_Interface
{
	/**
	 * Holds an ARRAY of settings to pass to the form
	 *
	 * @var array
	 */	
	private $_aParam = array();
	
	/**
	 * Holds an ARRAY of supported currencies for this payment gateway
	 *
	 * @var array
	 */	
	private $_aCurrency = array('ARS', 'AUD', 'BRL', 'GBP', 'CAD', 'DKK', 'EUR', 'HKD', 'INR', 'ILS', 'JPY', 'LTL', 'MYR', 'MXN', 'NZD', 'NOK', 'PHP', 'RON', 'RUB', 'SGD', 'ZAR', 'SEK', 'CHF', 'TRY', 'AED', 'USD');
	
	/**
	 * Class constructor
	 *
	 */	
	public function __construct()
	{			
		
	}
	
	/**
	 * Set the settings to be used with this class and prepare them so they are in an array
	 *
	 * @param array $aSetting ARRAY of settings to prepare
	 */	
	public function set($aSetting)
	{
		$this->_aParam = $aSetting;
		
		if (Phpfox::getLib('parse.format')->isSerialized($aSetting['setting']))
		{
			$this->_aParam['setting'] = unserialize($aSetting['setting']);
		}
	}
	
	/**
	 * Each gateway has a unique list of params that must be passed with the HTML form when posting it
	 * to their site. This method creates that set of custom fields.
	 *
	 * @return array ARRAY of all the custom params
	 */	
	public function getEditForm()
	{		
		return array(
			'2co_id' => array(
				'phrase' => Phpfox::getPhrase('core.2checkout_vendor_id_number'),
				'phrase_info' => Phpfox::getPhrase('core.your_numerical_vendor_id'),
				'value' => (isset($this->_aParam['setting']['2co_id']) ? $this->_aParam['setting']['2co_id'] : '')
			),
			/*'2co_secret' => array(
				'phrase' => Phpfox::getPhrase('core.2checkout_secret_word'),
				'phrase_info' => Phpfox::getPhrase('core.the_secret_word_as_set_within_the_look_and_feel_page_of_your_2checkout_account'),
				'value' => (isset($this->_aParam['setting']['2co_secret']) ? $this->_aParam['setting']['2co_secret'] : '')
			)*/
		);
	}	
	
	/**
	 * Returns the actual HTML <form> used to post information to the 3rd party gateway when purchasing
	 * an item using this specific payment gateway
	 *
	 * @return bool FALSE if we can't use this payment gateway to purchase this item or ARRAY if we have successfully created a form
	 */	
	public function getForm()
	{
		if (!in_array($this->_aParam['currency_code'], $this->_aCurrency))
		{
			if (!empty($this->_aParam['alternative_cost']))
			{
				$aCosts = unserialize($this->_aParam['alternative_cost']);
				$bPassed = false;
				foreach ($aCosts as $sCode => $iPrice)
				{
					if (in_array($sCode, $this->_aCurrency))
					{
						$this->_aParam['amount'] = $iPrice;
						$this->_aParam['currency_code'] = $sCode;      
						$bPassed = true;
						break;
					}
				}
			   
				if ($bPassed === false)
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
	   
		$aForm = array(
			'url' => 'https://www.2checkout.com/checkout/purchase',
			'param' => array(
				'sid' => $this->_aParam['setting']['2co_id'],
				'mode' => '2CO',
				'li_0_type' => 'product',
				'li_0_name' => $this->_aParam['item_name'],
				'li_0_price' => $this->_aParam['amount'],
				'li_0_tangible' => 'N',
				'li_0_product_id' => $this->_aParam['item_number'],
				'currency_code' => $this->_aParam['currency_code'],
				'demo' => $this->_aParam['is_test'] ? 'Y' : ''
			)
		);     
	   
		if ($this->_aParam['recurring'] > 0)
		{
			switch ($this->_aParam['recurring'])
			{
				case '1':
					$sPeriod = 'Month';
					$iEach = 1;
					break;
				case '2':
					$sPeriod = 'Month';
					$iEach = 3;
					break;
				case '3':
					$sPeriod = 'Month';
					$iEach = 6;
					break;
				case '4':
					$sPeriod = 'Year';
					$iEach = 1;
					break;              
			}
			
			$aCosts = unserialize($this->_aParam['alternative_recurring_cost']);
			
			$aForm['param']['li_0_recurrence'] = $iEach . " " . $sPeriod;
			$aForm['param']['li_0_price'] = $aCosts[$this->_aParam['currency_code']];
			if($this->_aParam['amount'] > 0)
			{
				$aForm['param']['li_0_startup_fee'] = $this->_aParam['amount'];
				$aForm['param']['li_1_name'] = Phpfox::getPhrase('subscribe.recurring_price');
				$aForm['param']['li_1_type'] = 'coupon';
				$aForm['param']['li_1_price'] = $aCosts[$this->_aParam['currency_code']];
			}
		}
		
		return $aForm;
	}
	
	/**
	 * Performs the callback routine when the 3rd party payment gateway sends back a request to the server,
	 * which we must then back and verify that it is a valid request. This then connects to a specific module
	 * based on the information passed when posting the form to the server.
	 *
	 */	
	public function callback()
	{
		Phpfox::log('Starting 2checkout callback');
		Phpfox::log('Creating MD5 hash');

		$bIsRefund = false;                            
		if (isset($this->_aParam['is_test']) && $this->_aParam['is_test'])
		{
			$sHash = $this->_aParam['md5_hash'];
			Phpfox::log('Test hash.');
		}
		else
		{
			if (isset($this->_aParam['message_type']) && isset($this->_aParam['md5_hash']))                
			{
				if(isset($this->_aParam['item_type_1']) && $this->_aParam['item_type_1'] == 'refund')
				{
					$bIsRefund = true;
				}
				// sale_id + vendor_id + invoice_id + secret word      
				$sHash = strtoupper(md5($this->_aParam['sale_id'] . $this->_aParam['setting']['2co_id'] . $this->_aParam['invoice_id'] . $this->_aParam['setting']['2co_secret']));
				Phpfox::log('Refund hash.');
			}
			else
			{
				$sHash = strtoupper(md5($this->_aParam['setting']['2co_secret'] . $this->_aParam['setting']['2co_id'] . $this->_aParam['order_number'] . $this->_aParam['total']));
				Phpfox::log('Purchase hash.');
			}                      
		}
		Phpfox::log('Hash created: ' . $sHash);
	   
		if (($bIsRefund && $sHash == $this->_aParam['md5_hash']) || (!$bIsRefund && $sHash == $this->_aParam['md5_hash']))
		{
			Phpfox::log('Hash is valid');
		   
			$aParts = explode('|', $this->_aParam['vendor_order_id']);
		   
			Phpfox::log('Attempting to load module: ' . $aParts[0]);                       
		   
			if (Phpfox::isModule($aParts[0]))
			{
				Phpfox::log('Module is valid.');
				Phpfox::log('Checking module callback for method: paymentApiCallback');
				if (Phpfox::hasCallback($aParts[0], 'paymentApiCallback'))
				{
					Phpfox::log('Module callback is valid.');
					Phpfox::log('Building payment status: ' . $this->_aParam['message_description']);
   
					$sStatus = null;
					if ($bIsRefund)
					{
						$sStatus = 'cancel';   
					}
					else
					{
						switch (strtolower($this->_aParam['payment_type']))
						{
							case 'credit card':
								$sStatus = 'completed';
								break;
							default:
								$sStatus = 'pending';
								break;
						}
					}
				   
					Phpfox::log('Status built: ' . $sStatus);
				   
					if ($sStatus !== null)
					{
						Phpfox::log('Executing module callback');
						Phpfox::callback($aParts[0] . '.paymentApiCallback', array(
								'gateway' => '2checkout',
								'ref' => ($bIsRefund ? $this->_aParam['sale_id'] : $this->_aParam['order_number']),
								'status' => $sStatus,
								'item_number' => $aParts[1],
								'total_paid' => ($bIsRefund ? '0' : $this->_aParam['item_list_amount_1'])
							)
						);
					}
					else
					{
						Phpfox::log('Status is NULL. Nothing to do');
					}                                      
				}
				else
				{
					Phpfox::log('Module callback is not valid.');
				}
			}
			else
			{
				Phpfox::log('Module is not valid.');
			}
		}
		else
		{
			Phpfox::log('Hash is invalid');
		}                                              
	   
		return 'redirect';
	}
}

?>
