<?php
/**
 * Komfortkasse
 * Installer
 *
 * @version 1.0.8-xtc3
 * 
 * use these SQL statements to delete the configuration entries in order to re-install the plugin:
 * delete from configuration_group where configuration_group_title='Komfortkasse';
 * delete from configuration where configuration_key like 'KOMFORTKASSE%';
 */

#error_reporting(E_ALL);
#ini_set('display_errors', '1');
$basedir = explode('callback', $_SERVER['SCRIPT_NAME']) ;

?>
<html>
<head>
<title>Komfortkasse Installer</title>
</head>
<body>
	<font face="Verdana,Arial,Helvetica"> <img
		src="images/komfortkasse_eu.png" border="0"><br />
		<h3>Auto Installer</h3>
<?php $steps = 9; $step=0; ?>
Note: if the installer exits before step <?php echo $steps; ?> without an error message, enable error reporting in this install.php file. (Uncomment lines 13 and 14.)

<br />
	<br />
	<b><?php echo ++$step;?>/<?php echo $steps;?></b>
Including files...

<?php


$basepath = explode('callback', $_SERVER['SCRIPT_FILENAME']) ;
require_once ($basepath[0].'includes/configure.php');
require_once (DIR_WS_INCLUDES.'application_top_callback.php');
require_once ('Komfortkasse_Config.php');
?>

<br />
	<br />
	<b><?php echo ++$step;?>/<?php echo $steps;?></b>
Determining Language...

<?php
require_once (DIR_WS_CLASSES . 'language.php');
$lng = new language();
$lng->get_browser_language();
echo $lng->language ['code'];
?>

<br />
	<br />
	<b><?php echo ++$step;?>/<?php echo $steps;?></b>
Checking Language additions...

<?php
$file = DIR_WS_LANGUAGES . $lng->language ['directory'] . '/admin/configuration.php';
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ( $lines as $line ) {
	if (strstr($line, Komfortkasse_Config::activate_export) !== FALSE) {
		$found = 1;
		break;
	}
}
if (!$found) {
	echo "<br/>ERROR: The configuration translation has not been added to your language files (e.g. " . $file . "). Please add the lines to the language file(s) (you can find them in the /callback/komfortkasse/lang folder) and start the installer again.";
	die();
} else {
	echo "ok";
}

?>

<br />
	<br />
	<b><?php echo ++$step;?>/<?php echo $steps;?></b>
Determining Configuration Group ID...

<?php
$config_group_q = xtc_db_query("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " where configuration_group_title='Komfortkasse'");
$config_group_a = xtc_db_fetch_array($config_group_q);
$config_group_id = $config_group_a ['configuration_group_id'];
if ($config_group_id) {
	echo 'ERROR: Configuration group ID for "Komfortkasse" already exists. Probably the Module is already installed. This Installer will exit now.';
	die();
}

$config_group_q1 = xtc_db_query("SELECT max(configuration_group_id) as maxid FROM " . TABLE_CONFIGURATION_GROUP);
$config_group_a1 = xtc_db_fetch_array($config_group_q1);
$config_group_id1 = $config_group_a1 ['maxid'] + 1;

$config_group_q2 = xtc_db_query("SELECT max(configuration_group_id) as maxid FROM " . TABLE_CONFIGURATION);
$config_group_a2 = xtc_db_fetch_array($config_group_q2);
$config_group_id2 = $config_group_a2 ['maxid'] + 1;

$config_group_id = max($config_group_id1, $config_group_id2);

echo $config_group_id;
?>

<br />
	<br />
	<b><?php echo ++$step;?>/<?php echo $steps;?></b>
Checking Admin Menu...
<?php
$found = 0;
$file = DIR_FS_DOCUMENT_ROOT . 'admin/includes/column_left.php';
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ( $lines as $line ) {
	if ((strstr($line, 'Komfortkasse') !== FALSE) && (strstr($line, 'gID=' . $config_group_id) !== FALSE)) {
		$found = 1;
		break;
	}
}
if (!$found) {
	echo "<br/>Please add the following line to the file " . $file . " (at the bottom, just before the '?&gt;'):<br/><hr/>";
	?>
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '&lt;a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=<?php echo $config_group_id; ?>', 'NONSSL') . '" class="menuBoxContentLink"> -Komfortkasse&lt;/a&gt;&lt;br&gt;';
	<hr /> After you have added the line, please re-start the installer.<br />
	<br />
	<?php
	die();
} else {
	echo "ok";
}
?>
  
<br />
	<br />
	<b><?php echo ++$step;?>/<?php echo $steps;?></b>
Creating Configuration Group...
<?php
$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_group_title' => 'Komfortkasse',
		'configuration_group_description' => 'Komfortkasse Konfiguration',
		'sort_order' => $config_group_id,
		'visible' => 1 
);
xtc_db_perform(TABLE_CONFIGURATION_GROUP, $sql_data_array);

?>

<br />
	<br />
	<b><?php echo ++$step;?>/<?php echo $steps;?></b>
Creating Configuration ...

<?php
$sort_order = 1;
include_once 'install_defaults.php';

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::activate_export,
		'configuration_value' => KOMFORTKASSE_ACTIVATE_EXPORT_DEFAULT,
		'set_function' => 'xtc_cfg_select_option(array(\'true\', \'false\'),',
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::activate_update,
		'configuration_value' => KOMFORTKASSE_ACTIVATE_UPDATE_DEFAULT,
		'set_function' => 'xtc_cfg_select_option(array(\'true\', \'false\'),',
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::payment_methods,
		'configuration_value' => KOMFORTKASSE_PAYMENT_METHODS_DEFAULT,
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::status_open,
		'configuration_value' => KOMFORTKASSE_STATUS_OPEN_DEFAULT,
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::status_paid,
		'configuration_value' => KOMFORTKASSE_STATUS_PAID_DEFAULT,
		'use_function' => 'xtc_get_order_status_name',
		'set_function' => 'xtc_cfg_pull_down_order_statuses(',
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::status_cancelled,
		'configuration_value' => KOMFORTKASSE_STATUS_CANCELLED_DEFAULT,
		'use_function' => 'xtc_get_order_status_name',
		'set_function' => 'xtc_cfg_pull_down_order_statuses(',
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::payment_methods_invoice,
		'configuration_value' => KOMFORTKASSE_PAYMENT_CODES_INVOICE_DEFAULT,
		'sort_order' => $sort_order
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::status_open_invoice,
		'configuration_value' => KOMFORTKASSE_STATUS_OPEN_INVOICE_DEFAULT,
		'sort_order' => $sort_order
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::status_paid_invoice,
		'configuration_value' => KOMFORTKASSE_STATUS_PAID_INVOICE_DEFAULT,
		'use_function' => 'xtc_get_order_status_name',
		'set_function' => 'xtc_cfg_pull_down_order_statuses(',
		'sort_order' => $sort_order
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::status_cancelled_invoice,
		'configuration_value' => KOMFORTKASSE_STATUS_CANCELLED_INVOICE_DEFAULT,
		'use_function' => 'xtc_get_order_status_name',
		'set_function' => 'xtc_cfg_pull_down_order_statuses(',
		'sort_order' => $sort_order
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::payment_methods_cod,
		'configuration_value' => KOMFORTKASSE_PAYMENT_CODES_COD_DEFAULT,
		'sort_order' => $sort_order
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::status_open_cod,
		'configuration_value' => KOMFORTKASSE_STATUS_OPEN_COD_DEFAULT,
		'sort_order' => $sort_order
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::status_paid_cod,
		'configuration_value' => KOMFORTKASSE_STATUS_PAID_COD_DEFAULT,
		'use_function' => 'xtc_get_order_status_name',
		'set_function' => 'xtc_cfg_pull_down_order_statuses(',
		'sort_order' => $sort_order
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::status_cancelled_cod,
		'configuration_value' => KOMFORTKASSE_STATUS_CANCELLED_COD_DEFAULT,
		'use_function' => 'xtc_get_order_status_name',
		'set_function' => 'xtc_cfg_pull_down_order_statuses(',
		'sort_order' => $sort_order
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;


$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::encryption,
		'configuration_value' => '',
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::accesscode,
		'configuration_value' => '',
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::publickey,
		'configuration_value' => '',
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::privatekey,
		'configuration_value' => '',
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

$sql_data_array = array (
		'configuration_group_id' => $config_group_id,
		'configuration_key' => Komfortkasse_Config::apikey,
		'configuration_value' => '',
		'sort_order' => $sort_order 
);
xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
$sort_order++;

?>

<br />
	<br />
	<b><?php echo ++$step;?>/<?php echo $steps;?></b>
Modifying .htaccess file...
<?php
$ok = 0;
if (rename('.htaccess', '_htaccess.beforeinstall') === TRUE) {
	if (rename('_htaccess.afterinstall', '.htaccess') === TRUE) {
		$ok = 1;
	}
}
if ($ok) {
	echo "ok";
} else {
	echo "Important: your .htaccess file could not be changed. For improved security, please change your .htaccess file so that the install.php script cannot be executed, or rename install.php.";
}

?>

<br />
	<br />
	<b><?php echo ++$step;?>/<?php echo $steps;?></b> Finished. <a
		href="<?php echo $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$basedir[0];?>admin/configuration.php?gID=<?php echo $config_group_id; ?>"
		target="_new">Please check the configuration now.</a><br /> (If you
		cannot access this link, please login to your admin panel and open the
		Komfortkasse configuration from the menu - should be the last menu
		entry.)<br /> <br /> <br />
		<h3>Instant order transmission</h3> New orders will be read
		periodically from your online shop. Additionally, you can activate <b>instant
			order transmission</b>, which will transmit any new order
		immediately. This way, your customer will receive payment information
		immediately. We encourage you to activate instant order transmission.
		In order to activate instant order transmission, edit the following
		files:<br /> <br /> <b>/admin/orders.php</b>, around line 120: <pre>
xtc_db_query("insert into ".TABLE_ORDERS_STATUS_HISTORY." ...
<b>
// BEGIN Komfortkasse
include_once '../callback/komfortkasse/Komfortkasse.php';
$k = new Komfortkasse();
$k->notifyorder($oID);
// END Komfortkasse
</b>
$order_updated = true;
</pre> <br /> <b>/includes/modules/payment/eustandardtransfer.php</b>,
		or any other payment module that will be used with Komfortkasse (e.g.
		banktransfer, moneyorder), in function after_process, at the end of
		the function: <pre>
<b>
// BEGIN Komfortkasse
include_once './callback/komfortkasse/Komfortkasse.php';
$k = new Komfortkasse();
$k->notifyorder($insert_id);
// END Komfortkasse
</b>
</pre> <br /> <b>/lang/[your
			languages]/modules/payment/eustandardtransfer.php</b>, or any other
		payment module that will be used with Komfortkasse (e.g. banktransfer,
		moneyorder), change the MODULE_PAYMENT_[...]_TEXT_DESCRIPTION constant
		(e.g. MODULE_PAYMENT_EUTRANSFER_TEXT_DESCRIPTION): <pre>
<b>
// german
define('MODULE_PAYMENT_EUTRANSFER_TEXT_DESCRIPTION', '&lt;br /&gt;Sie erhalten nach Bestellannahme die Kontodaten in einer gesonderten E-Mail.');

// english
define('MODULE_PAYMENT_EUTRANSFER_TEXT_DESCRIPTION', '&lt;br /&gt;After your order is confirmed, you will receive payment details in a separate e-mail.');
</b>
</pre>

	</font>
</body>
</html>

