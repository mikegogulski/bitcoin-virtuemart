<?php
include_once(CLASSPATH . "payment/ps_bitcoin.cfg.php");
require_once(CLASSPATH . "ps_order.php");
$tot = number_format($db->f("order_total"), 2, ".", "");
$addr = $_SESSION["bitcoin_address"];
$oid = $db->f("order_id");
$addrinfo = "To make your payment and complete your order, please send BTC " . $tot . " to Bitcoin address " . $addr;
$confirminfo = "Your payment will be confirmed when " . BITCOIN_CONFIRMS . " confirmation";
if (BITCOIN_CONFIRMS != 1)
	$confirminfo .= "s";
$confirminfo .= " of the transaction ha";
if (BITCOIN_CONFIRMS != 1)
	$confirminfo .= "ve";
else
	$confirminfo .= "s";
$confirminfo .= " been received.";
$confirminfo .= " If payment is not received within " . BITCOIN_TIMEOUT . " hours, your order will be canceled automatically.";
// TODO: better address validation
if (strlen($addr) >= 27 && strlen($addr) <= 40) {
	$q = "UPDATE #__vm_order_payment SET order_payment_name='" . $addr . "' WHERE order_id='" . $oid . "'";
	$db->query($q);
	echo "<p><strong>" . $addrinfo . "</strong><p>";
	echo "<p>" . $confirminfo . "</p>";

	$d['include_comment'] = "Y";
	$d['order_comment'] = $addrinfo . ". " . $confirminfo;
	$d['current_order_status'] = "P";
	$d['order_status'] = "P";
	$d['notify_customer'] = "Y";
	$d['order_id'] = $oid;
	$order = new ps_order;
	// TODO: hackish but it gets the info to the customer. revisit.
	$order->order_status_update($d);

} else {
	$vmLogger->err("Shopping cart expired.");
}
?>