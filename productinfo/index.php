<?php
/*
Plugin Name: My Plugin
Description: this plugin is made for installing multiple tables in a single plugin .
Author: Vinod Joshi
Version: 0.1
*/


register_activation_hook(__FILE__,'table_add');
function table_add(){
	global $wpdb;
	//To add the display product page

 	$the_page_title = 'Display Product';
    $the_page_name = 'display-plugin';

    // the menu entry...
    delete_option("my_plugin_page_title");
    add_option("my_plugin_page_title", $the_page_title, '', 'yes');
    // the slug...
    delete_option("my_plugin_page_name");
    add_option("my_plugin_page_name", $the_page_name, '', 'yes');
    // the id...
    delete_option("my_plugin_page_id");
    add_option("my_plugin_page_id", '0', '', 'yes');

    $the_page = get_page_by_title( $the_page_title );

    if (!$the_page) {
        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "[display-list-product]";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );
		$pageid = $wpdb->insert_id;
    }
    else {
        // the plugin may have been previously active and the page may just be trashed...
        $the_page_id = $the_page->ID;
        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );
    }

    delete_option('my_plugin_page_id');
    add_option('my_plugin_page_id', $the_page_id );
	
	//End of display product page code
	
	/***********Add Tables in database***************/

	$product_primary_category = $wpdb->prefix . "product_primary_category";
   	if($wpdb->get_var('SHOW TABLES LIKE ' . $product_primary_category) != $product_primary_category){
	  $sql_one = 'CREATE TABLE ' . $product_primary_category . '(
		  ppc_id INT(11),
		  ppc_name VARCHAR (255),
		  PRIMARY KEY(ppc_id))';

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_one);
	}
	
	$product_sub_category = $wpdb->prefix . "product_sub_category";
	if($wpdb->get_var('SHOW TABLES LIKE ' . $product_sub_category) != $product_sub_category){
	  $sql_two = 'CREATE TABLE ' . $product_sub_category . '(
		  psc_id INT(11),
		  ppc_id int (11),
		  psc_name VARCHAR (255),
		  PRIMARY KEY(psc_id))';

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_two);
	}
	
	$product_list = $wpdb->prefix . "product_list";
	if($wpdb->get_var('SHOW TABLES LIKE ' . $product_list) != $product_list){
	  $sql_three = 'CREATE TABLE ' . $product_list . '(
		  product_id INT(11) UNSIGNED AUTO_INCREMENT,
		  ppc_id int (11),
		  psc_id int (11),
		  product_name VARCHAR (255),
		  product_description VARCHAR (255),
		  product_code VARCHAR (255),
		  product_image VARCHAR (255),
		  PRIMARY KEY(product_id))';

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_three);
	}
	
	$wishlist = $wpdb->prefix . "product_wishlist";
	if($wpdb->get_var('SHOW TABLES LIKE ' . $wishlist) != $wishlist){
	  $sql_four = 'CREATE TABLE ' . $wishlist . '(
		  w_id INT(11) UNSIGNED AUTO_INCREMENT,
		  product_id VARCHAR (255),
		  user_id int(11),
		 PRIMARY KEY(w_id))';

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_four);
	}

	$store_checkout = $wpdb->prefix . "product_store_checkout";
	if($wpdb->get_var('SHOW TABLES LIKE ' . $store_checkout) != $store_checkout){
	  $sql_five = 'CREATE TABLE ' . $store_checkout . '(
		  s_c_id INT(11) UNSIGNED AUTO_INCREMENT,
		  active_user int(11),
		  active_user_id int (11),
		  guest_user_id int (11),
		  product_info VARCHAR (255),
		 PRIMARY KEY(s_c_id))';

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_five);
	}

	$guest_user_info = $wpdb->prefix . "product_guest_user";
	if($wpdb->get_var('SHOW TABLES LIKE ' . $guest_user_info) != $guest_user_info){
	  $sql_six = 'CREATE TABLE ' . $guest_user_info . '(
		  guest_id INT(11) UNSIGNED AUTO_INCREMENT,
		  guest_firstname VARCHAR(255),
		  guest_lastname VARCHAR(255),
		  guest_address VARCHAR (255),
		  guest_phone int(11),
		  guest_email VARCHAR (255),
		 PRIMARY KEY(guest_id))';

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_six);
	}
	
 }
 /**********End code of add tables in database***************/
 	
function array_flatten($sessioned_array) {
	if(empty($sessioned_array)) {
		$return = array();
		return $return;
	}
	else {
		$return = array();
		foreach ($sessioned_array as $key => $value) {
			if (is_array($value)){ $return = array_merge($return, array_flatten($value));}
			else {$return[$key] = $value;}
		}
   return $return;
   }
}

function productInfoGetAQuote() {
	if (is_user_logged_in()) {
	global $wpdb;
			$current_userId = get_current_user_id();
			$activeUser = 1;
			$active_user_id = $current_userId;
			$guest_user_id = 0;
			$quoted_product = $_POST;
			unset ($quoted_product['getAquote']);
			$new_array = array();
			foreach($quoted_product as $i=>$element){
    			foreach($element as $j=>$sub_element){
        			$new_array[$j][$i] = $sub_element; //We are basically inverting the indexes
    			}
			}
			$product_info = json_encode($new_array);
			$result = $wpdb->get_results("SELECT * FROM wp_product_store_checkout where active_user_id=".$current_userId);
			if(count($result) == 0){
				$sql = $wpdb->query("INSERT INTO  wp_product_store_checkout (active_user, active_user_id, guest_user_id, product_info) VALUES ('".$activeUser."', '".$active_user_id."', '".$guest_user_id."', '".$product_info."')");
			}
			else {
				$wpdb->update( 'wp_product_store_checkout',  array('active_user' => $activeUser, 'guest_user_id' => $guest_user_id,'product_info' => $product_info), array('active_user_id' =>  $current_userId), array('%d','%d','%s'), array('%d'));
			}
			$wpdb->delete('wp_product_wishlist', array('user_id' => $current_userId), array('%d'));
			session_destroy();
		}
		else {
		global $wpdb;
		if($_POST['guestuser'] == 1){
				$activeUser = 0;
				$active_user_id = 0;
				$user_info = $_POST;
				$sql = $wpdb->query("INSERT INTO  wp_product_guest_user (guest_firstname, guest_lastname, guest_address, guest_phone, guest_email) VALUES ('".$user_info['firstname']."', '".$user_info['lastname']."', '".$user_info['address']."', '".$user_info['phone']."', '".$user_info['email']."')");
				$guest_user_id = $wpdb->insert_id;
				$quoted_product = $_POST;
				unset($quoted_product['getAquote']);
				unset($quoted_product['firstname']);
				unset($quoted_product['lastname']);
				unset($quoted_product['address']);
				unset($quoted_product['phone']);
				unset($quoted_product['email']);
				unset($quoted_product['guestuser']);
				
				$new_array = array();
				foreach($quoted_product as $i=>$element){
					foreach($element as $j=>$sub_element){
						$new_array[$j][$i] = $sub_element; //We are basically inverting the indexes
					}
				}
				
				$product_info = json_encode($new_array);
				$sql = $wpdb->query("INSERT INTO  wp_product_store_checkout (active_user, active_user_id, guest_user_id, product_info) VALUES ('".$activeUser."', '".$active_user_id."', '".$guest_user_id."', '".$product_info."')");
				session_destroy();
			}
			else {
			echo do_shortcode('[my-login-form]');
			}
		}
}

function productInfosaveForQuote(){
global $wpdb;
	if(empty($_POST['product_information'])) {
	?>
		<span style="color:red;">Please Select Product To Send it for Quote.</span>
	<?php
		return false;
	}
	else {
		if(is_user_logged_in()) {
		$current_userId = get_current_user_id();
		$result = $wpdb->get_results("SELECT * FROM wp_product_wishlist where user_id=".$current_userId);
		$whishlist_product =  json_decode($result[0]->product_id);
		$whishlist_product = (array)$whishlist_product;

		$selected_products = $_POST['product_information'];
		$_SESSION['product_information'][] = $selected_products;
		$sessioned_array  = $_SESSION['product_information'];
		$sessioned_result = array_flatten($sessioned_array);
		$sessioned_result = array_unique($sessioned_result);
		
		foreach ($whishlist_product as $whishProduct) {
			$array = (array) $whishProduct;
				if(($key = array_search($array['product_id'], $sessioned_result)) !== false) {
    					unset($sessioned_result[$key]);
				}
			}

		}else {

		$selected_products = $_POST['product_information'];
		$_SESSION['product_information'][] = $selected_products;
		$sessioned_array  = $_SESSION['product_information'];
		$sessioned_result = array_flatten($sessioned_array);
		$sessioned_result = array_unique($sessioned_result);
		}
	
	}
	?>
	<form name="selected_product" id="selected_product" method="post" action="" style="float:right; width:200px;">
	<?php
	foreach($sessioned_result as $selected_product) {
		$result = $wpdb->get_results("SELECT * FROM wp_product_list where product_id=".$selected_product);
		foreach($result as $row){
		?>
			<div style="margin:10px 0px;">
				<span style="float:left;color:#0066FF;"><?php echo $row->product_name; ?></span>
				<input type="hidden" name="product_id[]" id="product_id" value="<?php echo $row->product_id; ?>" />
				<input type="number" name="product_quentity[]" id="product_quentity_<?php echo $row->product_id; ?>" min="1" width="20px" />
				
			</div>
		<?php
		}
	}
	if (is_user_logged_in() && !empty($sessioned_result)) {

	?>
		<input type="submit" name="addToWishlist" value="Add to Wishlist" />
<?php 
	$current_userId = get_current_user_id();
	$result = $wpdb->get_results("SELECT * FROM wp_product_wishlist where user_id=".$current_userId);
	$whishlist_product =  json_decode($result[0]->product_id);
	$whishlist_product = (array)$whishlist_product;

	if(!empty($whishlist_product)) {
	?><hr/>
		<span>Wishlisted Items</span>
	<?php
		foreach ($whishlist_product as $product) {
			 $result = $wpdb->get_results("SELECT product_name FROM wp_product_list where product_id=".$product->product_id);
			?>
				<div style="">
					<input type="hidden" name="product_id[]" id="product_id" value="<?php echo $product->product_id; ?>" />
					<span style="float:left;"><?php echo $result[0]->product_name; ?></span>
					<input type="number" name="product_quentity[]" id="product_quentity" min="1" width="20px" value="<?php echo $product->product_quentity; ?>"  />	
					
				</div>
			<?php
		}
		?>
<?php
	}
?>
		<input type="submit" name="getAquote" value="Get a quote" onclick="javascript:validateGetAQuote();" />
	</form>
	<?php 
	}else{
		?>	
			<button data-popup-target="#example-popup" onclick="javascript:return false;">Get a quote</button>
		<?php
		}
		?>
		
		<div id="example-popup" class="popup">
		<div class="popup-body">	
		<span class="popup-exit"></span>
		<div class="popup-content">
			<h2 class="popup-title">Please Select Any Option</h2>
			<p>
				<input type="radio" name="guestuser" value="0">Are you already registerd user?<br>
				<input type="radio" name="guestuser" value="1">Are you guest user?
			</p>
			<div id="g_field" style="display:none;">
				<p>First Name : <input type="text" name="firstname" id="firstname" /></p>
				<p>Last Name : <input type="text" name="lastname" id="lastname" /></p>
				<p>Address : <input type="text" name="address" id="address" /></p>
				<p>Phone : <input type="text" name="phone" id="phone" /></p>
				<p>Email : <input type="text" name="email" id="email" /></p>
			</div>
			<input type="submit" name="getAquote" id="getAquote"  value="Click to Save!!" />
		</div>
		</div>
		</div>
		
		</form>
		<div class="popup-overlay"></div>
		
		<?php
}

function productInfoaddToWishlist () {
	global $wpdb;
	$whishlist = $_POST;
	unset($whishlist['addToWishlist']);
	$new_array = array();
	foreach($whishlist as $i=>$element){
    		foreach($element as $j=>$sub_element){
        		$new_array[$j][$i] = $sub_element; //We are basically inverting the indexes
    		}
	}

	$whishlist_product = json_encode($new_array);
	$current_userId = get_current_user_id();
	$result = $wpdb->get_results("SELECT * FROM wp_product_wishlist where user_id=".$current_userId);
	if(count($result) == 0){
		$sql = $wpdb->query("INSERT INTO wp_product_wishlist (product_id, user_id) VALUES ('".$whishlist_product."', '".$current_userId."')");
	}
	else {
		$wpdb->update('wp_product_wishlist',  array('product_id' => $whishlist_product), array( 'user_id' =>  $current_userId ),
    array('%s'), array( '%d' ));
	}

}

function productInfoGetppc_ic () {
	global $wpdb;
	$ppc_id = $_GET['ppc_id'];
	$psc_id = $_GET['psc_id'];
	if(isset($ppc_id)) {
		if(isset($psc_id)) {
			if(isset($_POST['search'])) {
			$searchKeyword = $_POST['search'];
			$result = $wpdb->get_results("SELECT * FROM wp_product_list where product_name LIKE '%$searchKeyword%' AND ppc_id='".$ppc_id."' AND psc_id='".$psc_id."'");
			}
			else {
			$result = $wpdb->get_results("SELECT * FROM wp_product_list where ppc_id='".$ppc_id."' AND psc_id='".$psc_id."'");
			}
		}
		else {
			if(isset($_POST['search'])) {
			$searchKeyword = $_POST['search'];
			$result = $wpdb->get_results("SELECT * FROM wp_product_list where product_name LIKE '%$searchKeyword%' AND ppc_id=".$ppc_id);
			}
			else {
			$result = $wpdb->get_results("SELECT * FROM wp_product_list where ppc_id=".$ppc_id);
			}
		}
		if(count($result)>0) {
		$sessioned_array  = $_SESSION['product_information'];
		$sessioned_result = array_flatten($sessioned_array);
		$sessioned_product = array_unique($sessioned_result);
?>
<form action="" method="post" name="product_list" id="product_list" style="float:left; width:200px;">
<!-- Wrapper -->
<div id="wrapper">
  <div id="wrapper-bottom">
    <div class="shell">
      <!-- Main -->
      <div id="main" style="margin-top:110px;">
        <!-- Content -->
        <div id="content">
          <!-- Featured Products -->
          <div class="products-holder">
            <div class="middle">
              <div class="cl"></div>
<?php
			foreach($result as $row){
				$product_img = $row->product_image;
			?>
			
			<div class="product">
			 <a class="example-image-link" href="<?php if($product_img == '') { echo plugins_url($noimage, __FILE__ ); } else {echo plugins_url($product_img, __FILE__ ); } ?>" data-lightbox="example-set" data-title="<?php echo $row->product_name; ?>"  data-description="<?php echo $row->product_description; ?>" data-url="<?php echo plugins_url('pdf.php?id='.$row->product_id, __FILE__ ); ?>"><img class="example-image" src="<?php if($product_img == '') { echo plugins_url($noimage, __FILE__ ); } else {echo plugins_url($product_img, __FILE__ ); } ?>" height="150px" width="150px" alt="" /> </a>
                <div class="desc">
                  <p style="font-size:10px;color:#003399;font-weight:bold;">
				     <input type="checkbox" id="checkbox_example" name="product_information[]" value="<?php echo $row->product_id; ?>" <?php if(in_array($row->product_id, $sessioned_product)) { echo "checked"; } ?> /> Category Name : <span><?php echo $row->product_name; ?></span> </p>
                </div>
                
                <div class="cl"></div>
              </div>
			<?php
			}
?>
</div>

          </div>
          <!-- END Featured Products -->
        </div>
        <!-- END Content -->
      </div>
      <!-- END Main -->
    </div>
  </div>
</div>
<!-- END Wrapper -->
<input type="submit" name="saveForQuote" value="Save For Quote" /> 
</form>
<?php
		}
		else {
			?>
			<span>There is no items in this category</span>
			<?php
		}
	}
}

function productInfoSearch() {
	global $wpdb;
	$noimage = 'product_images/noimage.png';
	$searchKeyword = $_POST['search'];
	$searchtype = $_POST['searchtype'];
	$sessioned_array  = $_SESSION['product_information'];
	$sessioned_result = array_flatten($sessioned_array);
	$sessioned_product = array_unique($sessioned_result);
	if($searchtype == 'category') {
		$result = $wpdb->get_results("SELECT * FROM wp_product_primary_category WHERE ppc_name LIKE '%$searchKeyword%'");
		foreach($result as $row){
		?>
		<a href="?ppc_id=<?php echo $row->ppc_id ?>"><?php echo $row->ppc_name; ?></a><br />
		<ul>
		<?php
		$results = $wpdb->get_results("SELECT * FROM wp_product_sub_category where ppc_id='".$row->ppc_id."' AND psc_name LIKE '%$searchKeyword%'");
		foreach($results as $rows){
		?>
			<li>
			<a href="<?php echo site_url(); ?>/display-product/?ppc_id=<?php echo $row->ppc_id ?>&psc_id=<?php echo $rows->psc_id ?>"><?php echo $rows->psc_name; ?></a><br />
			</li>
		<?php
		}
		?>
		</ul>
		<?php
		}
	}
	else {
		$result = $wpdb->get_results("SELECT * FROM wp_product_list WHERE product_name LIKE '%$searchKeyword%'");
		if(!empty($result)) {
	?>
		<form action="" method="post" name="product_list" id="product_list" style="float:left; width:200px;">
		<div id="wrapper">
		<div id="wrapper-bottom">
		<div class="shell">
		<!-- Main -->
		<div id="main" style="margin-top:130px;">
		<!-- Content -->
		<div id="content">
		  <!-- Featured Products -->
		  <div class="products-holder" style="width:981px;">
			<div class="middle">
			  <div class="cl"></div>
	<?php
		foreach($result as $row){
			$product_img = $row->product_image;
	?>
	
	<div class="product">
			 <a class="example-image-link" href="<?php if($product_img == '') { echo plugins_url($noimage, __FILE__ ); } else {echo plugins_url($product_img, __FILE__ ); } ?>" data-lightbox="example-set" data-title="<?php echo $row->product_name; ?>" data-description="<?php echo $row->product_description; ?>" data-url="<?php echo plugins_url('pdf.php?id='.$row->product_id, __FILE__ ); ?>"><img class="example-image" src="<?php if($product_img == '') { echo plugins_url($noimage, __FILE__ ); } else {echo plugins_url($product_img, __FILE__ ); } ?>" height="150px" width="150px" alt="" /> </a>
                <div class="desc">
                  <p style="font-size:10px;color:#003399;font-weight:bold;"> <input type="checkbox" id="checkbox_example" name="product_information[]" value="<?php echo $row->product_id; ?>" <?php if(in_array($row->product_id, $sessioned_product)) { echo "checked"; } ?> /> Category Name : <span><?php echo $row->product_name; ?></span> </p>
                </div>
                
                <div class="cl"></div>
              </div>
	
		<?php } ?>
			 
			</div>
			</div>
			<!-- END Featured Products -->
			</div>
			<!-- END Content -->
			</div>
			<!-- END Main -->
			</div>
			</div>
			</div>
			<!-- END Wrapper -->	
			<input type="submit" name="saveForQuote" value="Save For Quote"  />		
		</form>
	<?php
		}
		else {
		echo "<span>There are no Products available for this <strong>'".$searchKeyword."'</strong> .</span>";		
		}
	}
}

function getAquoteFromWhishlist() {
	global $wpdb;
	$current_userId = get_current_user_id();
	$activeUser = 1;
	$active_user_id = $current_userId;
	$guest_user_id = 0;
	$quoted_product = $_POST;
	unset ($quoted_product['getAquoteFromWhishlist']);
	$new_array = array();
	foreach($quoted_product as $i=>$element){
    		foreach($element as $j=>$sub_element){
        		$new_array[$j][$i] = $sub_element; //We are basically inverting the indexes
    		}
	}
	$product_info = json_encode($new_array);
	$result = $wpdb->get_results("SELECT * FROM wp_product_store_checkout where active_user_id=".$current_userId);
	if(count($result) == 0){
		$sql = $wpdb->query("INSERT INTO  wp_product_store_checkout (active_user, active_user_id, guest_user_id, product_info) VALUES ('".$activeUser."', '".$active_user_id."', '".$guest_user_id."', '".$product_info."')");
	}
	else {
		$wpdb->update( 'wp_product_store_checkout',  array('active_user' => $activeUser, 'guest_user_id' => $guest_user_id,'product_info' => $product_info), array('active_user_id' =>  $current_userId), array('%d','%d','%s'), array('%d'));
	}

	$wpdb->delete('wp_product_wishlist', array('user_id' => $current_userId), array('%d'));
	session_destroy();
}
add_shortcode( 'my-login-form', 'my_login_form_shortcode' );

function my_login_form_shortcode() {

	if ( is_user_logged_in() )
		return '';

	return wp_login_form( array( 'echo' => false ) );
}


add_shortcode('display-list-product' , 'display_list_of_products');
function display_list_of_products(){
	global $wpdb;
	$noimage = 'product_images/noimage.png';

	if(isset($_POST['getAquote'])) {
		productInfoGetAQuote();
	}
	if(isset($_POST['getAquoteFromWhishlist'])) {
		getAquoteFromWhishlist();
	}
	if(isset($_POST['saveForQuote'])) {
		productInfosaveForQuote();
	}
	
	if(isset($_POST['addToWishlist'])) {
		productInfoaddToWishlist();
	}
	
	if(isset($_GET['ppc_id'])) {
		productInfoGetppc_ic();
	}
	
	if(isset($_POST['search'])) {
		productInfoSearch();
	}
?>
<?php
if(is_user_logged_in()) {
	$current_userId = get_current_user_id();
	$result = $wpdb->get_results("SELECT * FROM wp_product_wishlist where user_id=".$current_userId);
	$whishlist_product =  json_decode($result[0]->product_id);
	$whishlist_product = (array)$whishlist_product;
	
	if(!empty($whishlist_product) && !isset($_POST['saveForQuote'])) {
	?>
		<form name="selected_product" id="selected_product" method="post" action=""  style="float:right; width:200px;">
		<span style="font-weight:bold;"> Your Wishlist Items</span><br /><br /><?php
		foreach ($whishlist_product as $product) {
			 $result = $wpdb->get_results("SELECT product_name FROM wp_product_list where product_id=".$product->product_id);
			?>
				<div style="margin:5px 0 5px 0;">
					<input type="hidden" name="product_id[]" id="product_id" value="<?php echo $product->product_id; ?>" />
					<span style="float:left;width:100%"><?php echo $result[0]->product_name; ?></span>
					<input type="number" name="product_quentity[]" id="product_quentity" min="1" width="20px" value="<?php echo $product->product_quentity; ?>"  />	
					<br />
				</div>
			<?php
		}
		?>
		<input type="submit" name="getAquoteFromWhishlist" value="Get a quote" onclick="javascript:validateGetAQuote();" />
</form>
<?php
	}
}
?>
<form name="search_form" action="" method="post" style="margin-top:10px;border:1px solid #ccc;padding:15px;width:682px;">
	
	<input type="radio" name="searchtype" value="category"  checked><label for="name">Search By Category</label> <br />
	<input type="radio" name="searchtype" value="product"> <label for="name">Search By Product</label><br/>
	<input name="search" id="search" type="text" />
	<input type="submit" value="search"/>
</form>
<?php 
	if(!isset($_GET['ppc_id']) && !isset($_POST['search'])) { 
?>
<form action="" method="post" name="product_list" id="product_list" style="float:left; width:200px;">
<div id="wrapper">
  <div id="wrapper-bottom">
    <div class="shell">
      <!-- Main -->
      <div id="main" style="margin-top:0px;">
        <!-- Content -->
        <div id="content">
          <!-- Featured Products -->
          <div class="products-holder" style="width:981px;">
            <div class="middle">
              <div class="cl"></div>
<?php
	$sessioned_array  = $_SESSION['product_information'];
	$sessioned_result = array_flatten($sessioned_array);
	$sessioned_product = array_unique($sessioned_result);
	$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
	$limit = 5;
	$offset = ( $pagenum - 1 ) * $limit;
	$result = $wpdb->get_results( "SELECT * FROM wp_product_list LIMIT $offset, $limit");
	if( $result ) 
	{ 
		
	foreach($result as $row){
		$product_img = $row->product_image;
		$noimage = 'product_images/noimage.png';
		?>
		
		<div class="product">
		 <a class="example-image-link" href="<?php echo plugins_url($product_img, __FILE__ ) ?>" data-lightbox="example-set" data-title="<?php echo $row->product_name; ?>" data-description="<?php echo $row->product_description; ?>" data-url="<?php echo plugins_url('pdf.php?id='.$row->product_id, __FILE__ ); ?>"><img class="example-image" src="<?php if($product_img == '') { echo plugins_url($noimage, __FILE__ ); } else {echo plugins_url($product_img, __FILE__ ); } ?>" height="150px" width="150px" alt="" /> </a>
			<div class="desc">
			  <p style="font-size:10px;color:#003399;font-weight:bold;"> <input type="checkbox" id="checkbox_example" name="product_information[]" value="<?php echo $row->product_id; ?>" <?php if(in_array($row->product_id, $sessioned_product)) { echo "checked"; } ?>  /> Category Name : <span><?php echo $row->product_name; ?></span> </p>
			</div>
			
			<div class="cl"></div>
		  </div>
		
		<?php
		}
	}	
		?>
		</div>
	  </div>
	  <!-- END Featured Products -->
	</div>
	<!-- END Content -->
  </div>
  <!-- END Main -->
</div>
</div>
</div>
<!-- END Wrapper -->
		<input type="submit" name="saveForQuote" value="Save For Quote" /> 
</form>
<?php
}

$total = $wpdb->get_var( "SELECT COUNT(`product_id`) FROM {$wpdb->prefix}product_list" );
$num_of_pages = ceil( $total / $limit );
$page_links = paginate_links( array(
 'base' => add_query_arg( 'pagenum', '%#%' ),
 'format' => '',
 'prev_text' => __( '&laquo;', 'aag' ),
 'next_text' => __( '&raquo;', 'aag' ),
 'total' => $num_of_pages,
 'current' => $pagenum
) );

if ( $page_links ) {
 echo '<div class="tablenav" style="clear:both;text-align:center;"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
}

echo '</div>';
}
//End code for Product List Display

//Sidebar category List
add_shortcode('sidebar-primary-category', 'category_list_for_sidebar');
function category_list_for_sidebar() {
global $wpdb;
$ppc_id = $_GET['ppc_id'];
	if(isset($_GET['ppc_id'])) {
		$result = $wpdb->get_results("SELECT * FROM wp_product_primary_category");
	?> 
	<div style="float:right;width:200px;">
	<?php	
	
		foreach($result as $row){
		?>
		<a href="?ppc_id=<?php echo $row->ppc_id ?>"><?php echo $row->ppc_name; ?></a><br />
		
		<ul>
		<?php
		$results = $wpdb->get_results("SELECT * FROM wp_product_sub_category where ppc_id='".$row->ppc_id."'");
			if($ppc_id == $row->ppc_id) {
				foreach($results as $rows){
				?>
				<li  style="padding:3px 0px 3px 20px;">
					<a href="<?php echo site_url(); ?>/display-product/?ppc_id=<?php echo $row->ppc_id ?>&psc_id=<?php echo $rows->psc_id ?>"><?php echo $rows->psc_name; ?></a><br />
				</li>
				<?php
				}
			}
			?>
		</ul>
		
		<?php
		}
	}
	else {
		$result = $wpdb->get_results( "SELECT * FROM wp_product_primary_category");
		foreach($result as $row){	
		?>
			<a href="<?php echo site_url(); ?>/display-product/?ppc_id=<?php echo $row->ppc_id ?>"><?php echo $row->ppc_name ?></a><br />
		<?php
		}	
	}
}
?>
</div>
<?php
//End code of Sidebar category List

//Start code for category at conent of page
add_shortcode('primary-category', 'add_list_of_primary_category');
function add_list_of_primary_category() {
	global $wpdb;
	$ppc_id = $_GET['ppc_id'];
	$noimage = 'product_images/noimage.png';
?>
<div id="wrapper">
  <div id="wrapper-bottom">
    <div class="shell">
      <!-- Main -->
      <div id="main" style="margin-top:110px;">
        <!-- Content -->
        <div id="content">
          <!-- Featured Products -->
          <div class="products-holder" style="width:981px;">
            <div class="middle">
              <div class="cl"></div>
<?php	
	if(isset($_GET['ppc_id'])) {
		$result = $wpdb->get_results("SELECT * FROM wp_product_primary_category where ppc_id=".$ppc_id);
		foreach($result as $row){
			$rand_product_img = $wpdb->get_results( "SELECT * FROM wp_product_list where ppc_id='".$row->ppc_id."'");
			$rand_product_img = $rand_product_img[0]->product_image;
			$results = $wpdb->get_results("SELECT * FROM wp_product_sub_category where ppc_id='".$row->ppc_id."'");
			if($ppc_id == $row->ppc_id) {
				foreach($results as $rows){
					$rand_product_img = $wpdb->get_results( "SELECT * FROM wp_product_list where ppc_id='".$rows->ppc_id."'");
					$rand_product_img = $rand_product_img[0]->product_image;
					
				?>
				<div class="product"> <a href="<?php echo site_url(); ?>/display-product/?ppc_id=<?php echo $row->ppc_id ?>&psc_id=<?php echo $rows->psc_id ?>"><img src="<?php if($rand_product_img == '') { echo plugins_url($noimage, __FILE__ ); } else { echo plugins_url($rand_product_img, __FILE__ ); } ?>" /></a>
					<div class="desc">
					  <p style="font-size:10px;color:#003399;font-weight:bold;">Category Name : <span><a href="<?php echo site_url(); ?>/display-product/?ppc_id=<?php echo $row->ppc_id ?>&psc_id=<?php echo $rows->psc_id ?>"><?php echo $rows->psc_name; ?></a></span></p>
					</div>
					
					<div class="cl"></div>
				</div>
					
				<?php
				}
			}
			?>
		<?php
		}
	}
	else {
		$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
		$limit = 3;
		$offset = ( $pagenum - 1 ) * $limit;
		$result = $wpdb->get_results("SELECT * FROM wp_product_primary_category LIMIT $offset, $limit");
		foreach($result as $row){
			$rand_product_img = $wpdb->get_results( "SELECT * FROM wp_product_list where ppc_id=".$row->ppc_id);
			$sub_category_list = $wpdb->get_results( "SELECT * FROM wp_product_sub_category where ppc_id=".$row->ppc_id);
			$rand_product_img = $rand_product_img[0]->product_image;
			$noimage = 'product_images/noimage.png'; 
	?>	
			
			<div class="product"> <a href="<?php if(empty($sub_category_list)){ echo site_url().'/display-product/?ppc_id='.$row->ppc_id; }else { echo '?ppc_id='.$row->ppc_id; } ?>"><img src="<?php if($rand_product_img == '') { echo plugins_url($noimage, __FILE__ ); }else { echo plugins_url($rand_product_img, __FILE__ ); } ?>" /></a>
					<div class="desc">
					  <p style="font-size:10px;color:#003399;font-weight:bold;">Category Name : <span><a href="<?php if(empty($sub_category_list)){ echo site_url().'/display-product/?ppc_id='.$row->ppc_id; }else { echo '?ppc_id='.$row->ppc_id; } ?>"><?php echo $row->ppc_name ?></a><br /></span></p>
					</div>
					
					<div class="cl"></div>
				</div>
			
		<?php
		}
		
		$total = $wpdb->get_var( "SELECT COUNT(`ppc_id`) FROM {$wpdb->prefix}product_primary_category" );
		$num_of_pages = ceil( $total / $limit );
		$page_links = paginate_links( array(
		 'base' => add_query_arg( 'pagenum', '%#%' ),
		 'format' => '',
		 'prev_text' => __( '&laquo;', 'aag' ),
		 'next_text' => __( '&raquo;', 'aag' ),
		 'total' => $num_of_pages,
		 'current' => $pagenum
		) );
		
		if ( $page_links ) {
		 echo '<div class="tablenav" style="clear:both;text-align:center;"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
		}
		
		echo '</div>';
	}
?>
		</div>
	  </div>
	  <!-- END Featured Products -->
	</div>
	<!-- END Content -->
  </div>
  <!-- END Main -->
</div>
</div>
</div>
<!-- END Wrapper -->
<?php
}
//END code for category at conent of page


/* Start code for plugin deactivation*/
register_deactivation_hook( __FILE__, 'my_plugin_remove' );
function my_plugin_remove() {
    global $wpdb;
    $the_page_title = get_option("my_plugin_page_title");
    $the_page_name = get_option("my_plugin_page_name");
    //  the id of our page...
    $the_page_id = get_option('my_plugin_page_id');
    if($the_page_id) {
        wp_delete_post($the_page_id); // this will trash, not delete
    }
    delete_option("my_plugin_page_title");
    delete_option("my_plugin_page_name");
    delete_option("my_plugin_page_id");
}
/* End code for plugin deactivation*/


/* Start code for plugin uninstall*/
register_uninstall_hook( __FILE__, 'pluginUninstall' );
function pluginUninstall()
{
	global $wpdb;
		
	$product_primary_category = $wpdb->prefix . "product_primary_category";
	$product_sub_category = $wpdb->prefix . "product_sub_category";
	$product_list = $wpdb->prefix . "product_list";
	$wishlist = $wpdb->prefix . "product_wishlist";
	$store_checkout = $wpdb->prefix . "product_store_checkout";
	$guest_user_info = $wpdb->prefix . "product_guest_user";
	
	$sql1 = "DROP TABLE ".$product_primary_category;
	$sql2 = "DROP TABLE ".$product_sub_category;
	$sql3 = "DROP TABLE ".$product_list;
	$sql4 = "DROP TABLE ".$wishlist;
	$sql5 = "DROP TABLE ".$store_checkout;
	$sql6 = "DROP TABLE ".$guest_user_info;

	$wpdb->query($sql1);
	$wpdb->query($sql2);
	$wpdb->query($sql3);
	$wpdb->query($sql4);
	$wpdb->query($sql5);
	$wpdb->query($sql6);
}
/*End code for plugin uninstall*/

/********Pagination code Start*********/
function wp_merge_pdf_scripts() {
	wp_enqueue_style('lightboxScreeCss', plugins_url( '/css/stye001.css', __FILE__ ));
	wp_enqueue_style('PaginationCss', plugins_url( '/css/popup_style.css', __FILE__ ));
	wp_enqueue_style('PaginationCss', plugins_url( '/css/pagination_style.css', __FILE__ ));
	wp_enqueue_style('lightboxCss', plugins_url( '/css/lightbox.css', __FILE__ ));
	wp_enqueue_style('lightboxScreeCss', plugins_url( '/css/screen.css', __FILE__ ));
}
add_action( 'wp_enqueue_scripts', 'wp_merge_pdf_scripts' );

/********Pagination code End*********/
add_action('wp_footer', 'pagination_script');
function pagination_script() {
?>
<script src="<?php echo plugins_url('/js/jquery-1.11.0.min.js', __FILE__ ); ?>"></script>
<script src="<?php echo plugins_url('/js/lightbox.js', __FILE__ ); ?>"></script>
<script src="<?php echo plugins_url('/js/customjspopup.js', __FILE__ ); ?>"></script>

<script type="text/javascript">
	$('input:checkbox').change (
		function () {
			reloadInfo ();
		}
	);
	function reloadInfo (){
			var cbdata = new Array();
			$('input:checkbox').each (
				function ()  {
					cbdata[$(this).val()] = $(this).is(':checked');
				}
			);
			//Set reload image while waiting for ajax
			$.ajax({
				url: "<?php echo plugins_url('/showres.php', __FILE__ ); ?>",
				type: "POST",
				data:  {cbdata: cbdata},
				success: function(msg){
				},
				statusCode: {
					404: function() {
						alert ("Something is wrong");
					}
				}
			});
		}
</script>
<?php
}
/**************Session code Start************************/
add_action('init', 'myStartSession', 1);
add_action('wp_logout', 'myEndSession');
add_action('wp_login', 'myEndSession');

function myStartSession() {
    if(!session_id()) {
        session_start();
    }
}

function myEndSession() {
    session_destroy();
}
/**************Session code End************************/			
?>