<?php
/**
 * Bitcoin cronjob script
 * Until bitcoind supports attaching a JSON-RPC callback to an address, we're stuck with this
 *
 * @version 0.9.0
 * @package Bitcoin payment processor for VirtueMart
 * @subpackage checkout.bitcoin_cronjob
 * @copyright Copyright (C) 2010 by Mike Gogulski - All rights reversed
 * @license http://www.unlicense.org/ (public domain)
 * @author Mike Gogulski - http://www.gogulski.com/ http://www.nostate.com/
 *
 * Code based in part on the PayCific VirtueMart 1.1.x Payment Module
 * http://extensions.virtuemart.net/index.php?option=com_sobi2&sobi2Task=sobi2Details&catid=2&sobi2Id=438&Itemid=2
 * by PayCific International AG - http://www.paycific.com/
 */

if (!$_GET)
	die();

header("HTTP/1.0 200 OK");

global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $database, $mosConfig_mailfrom, $mosConfig_fromname;

/*** access Joomla's configuration file ***/
$my_path = dirname(__FILE__);
// TODO: make this less megastupid
if (file_exists($my_path . "/../../../../configuration.php")) {
	$absolute_path = dirname($my_path . "/../../../../configuration.php");
	require_once($my_path . "/../../../../configuration.php");
} elseif (file_exists($my_path . "/../../../configuration.php")) {
	$absolute_path = dirname($my_path . "/../../../configuration.php");
	require_once($my_path . "/../../../configuration.php");
} elseif (file_exists($my_path . "/../../configuration.php")) {
	$absolute_path = dirname($my_path . "/../../configuration.php");
	require_once($my_path . "/../../configuration.php");
} elseif (file_exists($my_path . "/configuration.php")) {
	$absolute_path = dirname($my_path . "/configuration.php");
	require_once($my_path . "/configuration.php");
} else {
	die("Joomla Configuration File not found!");
}

$absolute_path = realpath($absolute_path);

// Set up the appropriate CMS framework
if (class_exists('jconfig')) {
	// Load the framework
	require_once(JPATH_BASE . DS . 'includes' . DS . 'defines.php');
	require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');

	// create the mainframe object
	$mainframe =& JFactory::getApplication('site');

	// Initialize the framework
	$mainframe->initialise();

	// load system plugin group
	JPluginHelper::importPlugin('system');

	// trigger the onBeforeStart events
	$mainframe->triggerEvent('onBeforeStart');
	$lang =& JFactory::getLanguage();
	$mosConfig_lang = $GLOBALS['mosConfig_lang'] = strtolower($lang->getBackwardLang());

	// Adjust the live site path
	$mosConfig_live_site = str_replace('/administrator/components/com_virtuemart', '', JURI::base());
	$mosConfig_absolute_path = JPATH_BASE;
} else {
	define('_VALID_MOS', '1');
	require_once($mosConfig_absolute_path . '/includes/joomla.php');
	require_once($mosConfig_absolute_path . '/includes/database.php');
	$database = new database($mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix);
	$mainframe = new mosMainFrame($database, 'com_virtuemart', $mosConfig_absolute_path);
}

// load Joomla Language File
if (file_exists($mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php'))
	require_once($mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php');
elseif (file_exists($mosConfig_absolute_path . '/language/english.php'))
	require_once($mosConfig_absolute_path . '/language/english.php');
/*** END of Joomla config ***/

/*** VirtueMart part ***/
require_once($mosConfig_absolute_path . '/administrator/components/com_virtuemart/virtuemart.cfg.php');

/* Load the Bitcoin Configuration File */
require_once(CLASSPATH . 'payment/ps_bitcoin.cfg.php');
// validate the cron secret for a wee bit of DDOS protection. Pity we can't do this earlier.
if ($_GET["secret"] != BITCOIN_CRON_SECRET)
	die("Wrong secret!");

include_once(ADMINPATH . '/compat.joomla1.5.php');
require_once(ADMINPATH . 'global.php');
require_once(CLASSPATH . 'ps_main.php');

/* @MWM1: Logging enhancements (file logging & composite logger). */
$vmLogIdentifier = "checkout.bitcoin_cronjob.php";
require_once(CLASSPATH . "Log/LogInit.php");

// restart session
// Constructor initializes the session!
$sess = new ps_session();
/*** END VirtueMart part ***/

global $vmLogger;
// slurp in all the open Bitcoin transactions
$sql = "select #__{vm}_orders.order_id,order_payment_name,order_number,order_total ";
$sql .= "from #__{vm}_order_payment left join #__{vm}_orders on #__{vm}_orders.order_id = #__{vm}_order_payment.order_id ";
// TODO: Extract "BC" in some intelligent, safe manner
$sql .= "where payment_method_id=(select payment_method_id from #__{vm}_payment_method where payment_method_code='BC') and length(order_payment_name)=34 and order_status='" . BITCOIN_PENDING_STATUS . "'";
$db = new ps_DB();
$db->query($sql);
if (!$db->next_record())
	die("No open Bitcoin transactions");
require_once(CLASSPATH . 'ps_order.php');
require_once(CLASSPATH . "payment/ps_bitcoin.php");
// prepare the JSON-RPC client
$bc = new ps_bitcoin();
$bitcoin_client = $bc->get_bitcoin_client();
// loop through the open transactions
do {
	// check for transaction completion
	$address = $db->f("order_payment_name");
	$m = new jsonrpcmsg("getreceivedbyaddress");
	$m->addParam(new jsonrpcval($address));
	$m->addParam(new jsonrpcval(BITCOIN_CONFIRMS, "int"));
	$r = $bitcoin_client->send($m);
	if ($r->faultCode())
		$vmLogger->err("Bitcoin server communication failed on getreceivedbyaddress " . $address);
	elseif ($r->value()->kindOf() != "scalar")
		$vmLogger->err("getreceivedbyaddress returned something other than a scalar " . $address);
	else {
		$ps_order = new ps_order;
		$order_id = $db->f("order_id");
		$d['order_id'] = $order_id;
		$paid = $r->value()->scalarVal();
		// TODO: Handle overpayment
		if ($paid >= $db->f("order_total")) {// PAID IN FULL
			// TODO: If the product was a downloadable, mark the order as SHIPPED
			$d['order_status'] = BITCOIN_VERIFIED_STATUS;
			$d['notify_customer'] = "Y";
			$ps_order->order_status_update($d);
		} else {// NOT PAID YET
			$t = time();
			$m = new jsonrpcmsg("getlabel", array(new jsonrpcval($address)));
			$r = $bitcoin_client->send($m);
			if ($r->faultCode())
				$vmLogger->err("Bitcoin server failed on getlabel " . $address);
			elseif ($r->value()->kindOf() != "scalar")
				$vmLogger->err("getlabel returned something other than a scalar " . $address);
			else {
				$label = $r->value()->scalarVal();
				list($order_number, $order_price, $timestamp) = explode(" ", $label);
				if ($t > ($timestamp + (BITCOIN_TIMEOUT * 60 * 60))) {// ORDER EXPIRED
					// TODO: Handle refund of partial payment
					$d['order_status'] = BITCOIN_INVALID_STATUS;
					$d['notify_customer'] = "Y";
					$d['order_comment'] = "Your payment was not completed with at least " . BITCOIN_CONFIRMS . " confirmation";
					if (BITCOIN_CONFIRMS != 1)
						$d['order_comment'] .= "s";
					$d['order_comment'] .= " within " . BITCOIN_TIMEOUT . " hours. Your order has been cancelled. If you have made a partial payment, please contact the shop administrator at the email address below.";
					$d['include_comment'] = "Y";
					$ps_order->order_status_update($d);
				}
			}
		}
	}
} while ($db->next_record());
?>