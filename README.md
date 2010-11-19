bitcoin-virtuemart
==================

A [Bitcoin](Bitcoin) payment processor plugin for the
[VirtueMart](VirtueMart) shopping cart for [Joomla!](Joomla).

Version: @@bitcoin-virtuemart-version@@

Features
--------

* Generates a new bitcoin address for every order
* Provides payment address to customer on site at checkout, plus in a
  subsequent email
* Configurable timeout after which unpaid transactions will be canceled
  automatically
* Configurable number of Bitcoin network confirmations after which an order
  is considered paid
* HTTP or HTTPS access to bitcoind

Requirements
------------

### Base requirements
* Joomla! 1.5
* VirtueMart 1.1.x

### PHP requirements:
* PHP5
* cURL support  
* SSL support (if you're using HTTPS to talk to bitcoind)

### Other requirements:
* Access to create a cronjob on your web server (or elsewhere)
* wget or the curl commandline tool on the machine running the cronjob

Limitations
-----------

* It is assumed that Bitcoin is the *only* currency accepted.
* All prices are assumed to be in Bitcoins, and no currency conversions are
  performed.
* A cronjob is required to check for payment receipt, at least until
  bitcoind allows attaching a JSON-RPC callback to an address.
* Emailing the bitcoin address requires an extra email to be sent (modifying
  the order confirmation message VirtueMart sends would be preferable, but
  the API doesn't support that).
* Validation of bitcoind's generated address is based only on its length.
  The PHP solution available requires the GMP extension to be installed.
* Orders for downloadables are not tagged as "shipped" once paid.
* The Bitcoin address associated with a transaction is stored in a database
  field not intended for it.
* No localization support.

Installation
------------

* Install Joomla! <http://help.joomla.org/content/category/15/99/132/>.
* Install VirtueMart (a PDF file included in the distribution explains the
  process).
* Untar the distribution archive in your Joomla! installation's base
  directory. 

Configuration
-------------

* Log into your Joomla! installation as an administrator.
* Click Components -> VirtueMart on the main menu.
* At left, click Store -> List Payment Methods.
* Click Bitcoin in the list.
* Click the checkbox next to "Name" at the top of the list, then clear the
  Bitcoin checkbox.
* Click "Unpublish" at top.
* On the "Payment Method Form" tab, configure as follows:
	* Active - checked
	* Payment Method Name - Bitcoin
	* Code - BC
	* Payment class name - ps_bitcoin
	* Payment method type - HTML-Form based (e.g. PayPal)
* Now click to the "Configuration" tab and proceed as follows:
	* Configure your bitcoind server information.
	* If you are using HTTPS to talk to bitcoind and would like to validate
      the connection using bitcoind's own SSL certificate, enter the
      absolute path to the certificate file (server.cert) you've uploaded
      to the server.
	* Configure your payment timeout and number of transaction confirmations
      required.
	* *Note the instruction to create a cronjob.* The cronjob is what
	  queries bitcoind periodically to see if pending orders have been paid. The
      cronjob is mandatory.
	* Leave the "Order status" options at their defaults:
		* Successful = Confirmed
		* Uncompleted = Pending
		* Failed = Cancelled
	* Copy and paste all of the code from `extrainfo.php` in the distribution
      archive's root directory into the "Payment Extra Info" box.
	* Save.
* Create the Bitcoin currency:
	* Assuming you're still in the VirtueMart component, click Admin ->
	  List Currencies at left.
	* Click New at top right.
	* Enter:
		* Currency name: Bitcoin
		* Currency code: BTC
	* Save.
* Set the store-wide currency:
	* Click Store -> Edit Store
	* In the "Currency Display Style" box:
		* Open "Currency" and select Bitcoin.
		* Enter "BTC" as the Currency Symbol.
		* Under "List of accepted currencies" click Bitcoin and make sure
		  that all other currencies are deselected.
	* Save.

Donate
------

* Bitcoin payments: 1MU97wyf7msCVdaapneW2dW1uXP7oEQsFA
* Gifts via other methods: <http://www.nostate.com/support-nostatecom/>

Authors
-------

* [Mike Gogulski](http://github.com/mikegogulski) -
  <http://www.nostate.com/> <http://www.gogulski.com/>

Credits
-------

* jsonrpc
* PayCific

License
-------

bitcoin-virtuemart is free and unencumbered public domain software. For more
information, see <http://unlicense.org/> or the accompanying UNLICENSE file.


[Bitcoin]:		http://www.bitcoin.org/
[VirtueMart]:	http://www.virtuemart.net/
[Joomla]:		http://www.joomla.org/
