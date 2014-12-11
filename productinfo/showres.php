<?php
session_start();
$os = $_POST['cbdata'];
$new_array = array();
foreach ($os as $index => $value) {
	if ($value == 'true') {
		$_SESSION['product_information'][] = $index;
	}
}
$sessioned_array  = $_SESSION['product_information'];
$sessioned_result = array_unique($sessioned_array);
echo "Value is stored";
?>