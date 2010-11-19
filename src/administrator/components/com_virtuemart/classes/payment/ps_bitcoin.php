<?php
if (!defined('_VALID_MOS') && !defined('_JEXEC'))
	die('Shoo!');
/**
 *
 * @version @@bitcoin-virtuemart-version@@
 * @package Bitcoin payment processor for VirtueMart
 * @subpackage ps_bitcoin
 * @copyright Copyright (C) 2010 by Mike Gogulski - All rights reversed
 * @license http://www.unlicense.org/ (public domain)
 * @author Mike Gogulski - http://www.gogulski.com/ http://www.nostate.com/
 *
 * Code based in part on the PayCific VirtueMart 1.1.x Payment Module
 * http://extensions.virtuemart.net/index.php?option=com_sobi2&sobi2Task=sobi2Details&catid=2&sobi2Id=438&Itemid=2
 * by PayCific International AG - http://www.paycific.com/
 *
 */

/**
 * Bitcoin configuration panel class
 * If you want to change something "internal", you must modify the 'payment extra info'
 * in the payment method form configuration form
 */
class ps_bitcoin {
	var $classname = "ps_bitcoin";
	var $payment_code = "BC";

	/**
	 * Show all configuration parameters for this payment method
	 * @returns boolean False when the Payment method has no configration
	 */
	function show_configuration() {
		$db = new ps_DB();

		include_once(CLASSPATH . "payment/ps_bitcoin.cfg.php");
		if (!defined("BITCOIN_SCHEME"))
			define("BITCOIN_SCHEME", "http");
		if (!defined("BITCOIN_CERTIFICATE"))
			define("BITCOIN_CERTIFICATE", "");
		if (!defined("BITCOIN_USERNAME"))
			define("BITCOIN_USERNAME", "");
		if (!defined("BITCOIN_PASSWORD"))
			define("BITCOIN_PASSWORD", "");
		if (!defined("BITCOIN_HOST"))
			define("BITCOIN_HOST", "localhost");
		if (!defined("BITCOIN_PORT"))
			define("BITCOIN_PORT", "8332");
		if (!defined("BITCOIN_TIMEOUT"))
			define("BITCOIN_TIMEOUT", "72");
		if (!defined("BITCOIN_CONFIRMS"))
			define("BITCOIN_CONFIRMS", "1");
		if (!defined("BITCOIN_CRON_SECRET")) {
			srand(time());
			$seed = serialize($_SESSION) . rand();
			define("BITCOIN_CRON_SECRET", md5($seed));
		}

		define("PHPSHOP_ADMIN_CFG_BITCOIN_SCHEME", "Server scheme");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_SCHEME_EXPLAIN", "HTTP or HTTPS");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_CERTIFICATE", "SSL certificate file");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_CERTIFICATE_EXPLAIN", "Location of the server.cert file you generated for bitcoind");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_USERNAME", "Server username");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_USERNAME_EXPLAIN", "User name for your Bitcoin server's JSON-RPC-HTTP interface");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_PASSWORD", "Server password");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_PASSWORD_EXPLAIN", "Bitcoin server password");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_HOST", "Server address");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_HOST_EXPLAIN", "Bitcoin server domain name or IP address");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_PORT", "Server port");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_PORT_EXPLAIN", "Bitcoin server port (generally 8332)");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_TIMEOUT", "Payment timeout (hours)");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_TIMEOUT_EXPLAIN", "Transactions not paid for within this number of hours will be automatically cancelled. Partial payments will be refunded.");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_CONFIRMS", "Confirmations required");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_CONFIRMS_EXPLAIN", "Minimum number of Bitcoin transaction network confirmations required before a payment is considered accepted.");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_CRON_SECRET", "Cron secret");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_CRON_SECRET_EXPLAIN", "Secret parameter to validate bitcoin_cronjob runs. You shouldn't need to change this. <strong>Don't forget to create a cron job.</strong> Example: <code>0,5,10,15,20,25,30,35,40,45,50,55 * * * * /usr/bin/wget -O /dev/null 'http://[YOUR_SHOP_DOMAIN]/[PATH]/index.php?option=com_virtuemart&page=checkout.bitcoin_cronjob&secret=[YOUR_CRON_SECRET]'</code>");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_STATUS_SUCCESS", "Order status for successful transactions");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_STATUS_SUCCESS_EXPLAIN", "Orders will be moved to this status when the Bitcoin payment transaction is completed successfully. If you are selling downloadable goods, select the status which enables the download and notifies the customer via email.");

		define("VM_ADMIN_CFG_BITCOIN_STATUS_PENDING", "Order status for uncompleted transactions");
		define("VM_ADMIN_CFG_BITCOIN_STATUS_PENDING_EXPLAIN", "Orders will be placed in this status until paid, expired or cancelled.");

		define("PHPSHOP_ADMIN_CFG_BITCOIN_STATUS_INVALID", "Order status for failed transactions");
		define("PHPSHOP_ADMIN_CFG_BITCOIN_STATUS_INVALID_EXPLAIN", "Orders will be moved to this status when payment is not received by the timeout set above.");
?>
		<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Like this plugin? Your gift of Bitcoins to 1FybsRQd8SHnFfyWpEZNPip3w9NyRpVuZu would be greatly appreciated. Other ways to send a gift can be found <a href="http://www.nostate.com/support-nostatecom/">here</a>. Thank you!</p>
		<table class="adminform">
			<tr class="row1">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_SCHEME ?></strong></td>
				<td>
					<select name="BITCOIN_SCHEME" class="inputbox">
						<option value="http" <?php if (BITCOIN_SCHEME != "https") echo ' selected="selected"'; ?>>HTTP</option>
						<option value="https" <?php if (BITCOIN_SCHEME == "https") echo ' selected="selected"'; ?>>HTTPS</option>
					</select>
				</td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_SCHEME_EXPLAIN ?></td>
			</tr>

			<tr class="row0">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_CERTIFICATE ?></strong></td>
				<td><input type="text" name="BITCOIN_CERTIFICATE" class="inputbox" value="<?php echo BITCOIN_CERTIFICATE ?>" /></td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_CERTIFICATE_EXPLAIN ?></td>
			</tr>

			<tr class="row1">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_USERNAME ?></strong></td>
				<td><input type="text" name="BITCOIN_USERNAME" class="inputbox" value="<?php echo BITCOIN_USERNAME ?>" /></td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_USERNAME_EXPLAIN ?></td>
			</tr>

			<tr class="row0">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_PASSWORD ?></strong></td>
				<td><input type="text" name="BITCOIN_PASSWORD" class="inputbox" value="<?php echo BITCOIN_PASSWORD ?>" /></td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_PASSWORD_EXPLAIN ?>
				</td>
			</tr>
			<tr class="row1">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_HOST ?></strong></td>
				<td><input type="text" name="BITCOIN_HOST" class="inputbox" value="<?php echo BITCOIN_HOST ?>" /></td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_HOST_EXPLAIN ?></td>
			</tr>

			<tr class="row0">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_PORT ?></strong></td>
				<td><input type="text" name="BITCOIN_PORT" class="inputbox" value="<?php echo BITCOIN_PORT ?>" /></td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_PORT_EXPLAIN ?></td>
			</tr>

			<tr class="row1">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_TIMEOUT ?></strong></td>
				<td><input type="text" name="BITCOIN_TIMEOUT" class="inputbox" value="<?php echo BITCOIN_TIMEOUT ?>" /></td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_TIMEOUT_EXPLAIN ?>
				</td>
			</tr>

			<tr class="row0">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_CONFIRMS ?></strong></td>
				<td><input type="text" name="BITCOIN_CONFIRMS" class="inputbox" value="<?php echo BITCOIN_CONFIRMS ?>" /></td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_CONFIRMS_EXPLAIN ?>
				</td>
			</tr>

			<tr class="row1">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_CRON_SECRET ?></strong></td>
				<td><input type="text" name="BITCOIN_CRON_SECRET" class="inputbox" value="<?php echo BITCOIN_CRON_SECRET ?>" /></td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_CRON_SECRET_EXPLAIN ?>
				</td>
			</tr>

			<tr class="row0">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_STATUS_SUCCESS ?></strong></td>
				<td>
					<select name="BITCOIN_VERIFIED_STATUS" class="inputbox" >
					<?php
		$q = "SELECT order_status_name,order_status_code FROM #__{vm}_order_status ORDER BY list_order";
		$db->query($q);
		$order_status_code = Array();
		$order_status_name = Array();

		while ($db->next_record()) {
			$order_status_code[] = $db->f("order_status_code");
			$order_status_name[] = $db->f("order_status_name");
		}
		for ($i = 0; $i < sizeof($order_status_code); $i++) {
			echo "<option value=\"" . $order_status_code[$i];
			if (BITCOIN_VERIFIED_STATUS == $order_status_code[$i])
				echo "\" selected=\"selected\">";
			else
				echo "\">";
			echo $order_status_name[$i] . "</option>\n";
		}
?>
					</select>
				</td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_STATUS_SUCCESS_EXPLAIN ?>
				</td>
			</tr>
			<tr class="row1">
				<td><strong><?php echo VM_ADMIN_CFG_BITCOIN_STATUS_PENDING ?></strong></td>
				<td>
					<select name="BITCOIN_PENDING_STATUS" class="inputbox" >
					<?php
		for ($i = 0; $i < sizeof($order_status_code); $i++) {
			echo "<option value=\"" . $order_status_code[$i];
			if (BITCOIN_PENDING_STATUS == $order_status_code[$i])
				echo "\" selected=\"selected\">";
			else
				echo "\">";
			echo $order_status_name[$i] . "</option>\n";
		}
?>
					</select>
				</td>
				<td><?php echo VM_ADMIN_CFG_BITCOIN_STATUS_PENDING_EXPLAIN ?></td>
			</tr>
			<tr class="row0">
				<td><strong><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_STATUS_INVALID ?></strong></td>
				<td>
					<select name="BITCOIN_INVALID_STATUS" class="inputbox" >
					<?php
		for ($i = 0; $i < sizeof($order_status_code); $i++) {
			echo "<option value=\"" . $order_status_code[$i];
			if (BITCOIN_INVALID_STATUS == $order_status_code[$i])
				echo "\" selected=\"selected\">";
			else
				echo "\">";
			echo $order_status_name[$i] . "</option>\n";
		}
?>
					</select>
				</td>
				<td><?php echo PHPSHOP_ADMIN_CFG_BITCOIN_STATUS_INVALID_EXPLAIN; ?>
				</td>
			</tr>
		</table>
<?php
	}

	function has_configuration() {
		// return false if there's no configuration
		return true;
	}

	/**
	 * Returns the "is_writeable" status of the configuration file
	 * @param void
	 * @returns boolean True if the configuration file is writeable, false otherwise
	 */
	function configfile_writeable() {
		return is_writeable(CLASSPATH . "payment/" . $this->classname . ".cfg.php");
	}

	/**
	 * Returns the "is_readable" status of the configuration file
	 * @param void
	 * @returns boolean True if the configuration file is writeable, false otherwise
	 */
	function configfile_readable() {
		return is_readable(CLASSPATH . "payment/" . $this->classname . ".cfg.php");
	}

	/**
	 * Writes the configuration file for this payment method
	 * @param array Array of configuration parameters
	 * @returns boolean True when configuration file written successfully, false otherwise
	 */
	function write_configuration(&$d) {
		$my_config_array = array();
		$varnames = array("BITCOIN_SCHEME",
			"BITCOIN_CERTIFICATE",
			"BITCOIN_USERNAME",
			"BITCOIN_PASSWORD",
			"BITCOIN_HOST",
			"BITCOIN_PORT",
			"BITCOIN_TIMEOUT",
			"BITCOIN_CONFIRMS",
			"BITCOIN_CRON_SECRET",
			"BITCOIN_VERIFIED_STATUS",
			"BITCOIN_PENDING_STATUS",
			"BITCOIN_INVALID_STATUS");
		foreach ($varnames as $name)
			$my_config_array[$name] = isset($d[$name]) ? $d[$name] : "";
		$config = "<?php\nif (!defined('_VALID_MOS') && !defined('_JEXEC')) die('Shoo!');\n\n";
		foreach ($my_config_array as $key=>$value)
			$config .= "define('$key', '$value');\n";
		$config .= "?" . ">";// hokeyness to prevent vi thinking this is the end of the code

		if (!$fp = fopen(CLASSPATH . "payment/" . $this->classname . ".cfg.php", "w"))
			return false;
		fputs($fp, $config, strlen($config));
		fclose($fp);
		return true;
	}

	/**
	 * create a jsonrpc_client object to talk to the bitcoin server and return it, or false on failure
	 * @return boolean|jsonrpc_client
	 */
	function get_bitcoin_client() {
		require_once(CLASSPATH . 'payment/ps_bitcoin.cfg.php');
		require_once(CLASSPATH . "/xmlrpc.inc");
		require_once(CLASSPATH . "/jsonrpc.inc");
		$uri = BITCOIN_SCHEME . "://" . BITCOIN_USERNAME . ":" . BITCOIN_PASSWORD . "@" . BITCOIN_HOST . ":" . BITCOIN_PORT . "/";
		$client = new jsonrpc_client($uri);
		//$client->setDebug(2);
		$client->setSSLVerifyHost(0);
		if (BITCOIN_SCHEME == "https")
			if (defined("BITCOIN_CERTIFICATE") && strlen(BITCOIN_CERTIFICATE))
				$client->setCaCertificate(BITCOIN_CERTIFICATE);
			else
				$client->setSSLVerifyPeer(false);
		$m = new jsonrpcmsg("getinfo");
		$r = $client->send($m);
		if ($r->faultCode()) {
			print htmlentities($r->faultString()) . "\n";
			return false;
		}
		return $client;
	}

	/**
	 * Process the payment
	 * @param string $order_number
	 * @param float $order_total
	 * @param array $d
	 * @return boolean true if bitcoin
	 */
	function process_payment($order_number, $order_total, &$d) {
		// TODO: handle conversions via to-be-written converter script
		// it's also available as global $vendor_currency
		//"currency_code" => $_SESSION['vendor_currency'],
		global $vmLogger;
		//require_once(CLASSPATH . 'payment/ps_bitcoin.cfg.php');
		$bitcoin_client = $this->get_bitcoin_client();
		if (!$bitcoin_client) {
			$vmLogger->err("The Bitcoin server is presently unavailable. Please contact the site administrator.");
			return false;
		}
		// stuff the (long) order number, the total order price and a timestamp into the bitcoin address's label
		$label = $order_number . " " . number_format($order_total, 2, ".", "") . " " . time();
		$m = new jsonrpcmsg("getnewaddress", array(new jsonrpcval($label, "string")));
		$r = $bitcoin_client->send($m);

		if ($r->faultCode()) {
			$vmLogger->err("The Bitcoin server was unable to generate an address for your payment. Please contact the site administrator.");
			return false;
		}

		$address = $r->value()->scalarVal();

		// TODO: better address validation
		// https://www.bitcoin.org/smf/index.php?topic=1026.0 has PHP code, but it depends on the GMP extension
		if (!$address || empty($address) || strlen($address) < 27 || strlen($address) > 40) {
			$vmLogger->err("The Bitcoin server returned an invalid address. Please contact the site administrator.");
			return false;
		}
		// stuff the payment address into the session so the "extra info" code can access it
		// TODO: There's gotta be a better way...
		$_SESSION["bitcoin_address"] = $address;
		$d['include_comment'] = "Y";
		$d['order_comment'] = "Please send your payment to Bitcoin address " . $address;
		return true;
	}
}
