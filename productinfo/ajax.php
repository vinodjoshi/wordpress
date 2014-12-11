<script type="text/javascript">
(function( $ ) {
	$(function() {
		$( "a", "#pagination" ).on( "click", function( e ) {
			e.preventDefault();
			var $a = $( this );
			$a.addClass( "current" ).
			siblings().
			removeClass( "current" );
			var page = $a.data( "page" );
			$.get( "http://localhost/wordpress/wp-content/plugins/myplugin/ajax.php", { s: page }, function( html ) {
				$( "#content" ).html( html );
			});
		});
	});
})( jQuery );
</script>
<?php 
include_once($_SERVER['DOCUMENT_ROOT'].'/wordpress/wp-config.php');
global $wpdb;
$start = 0;
$end = 3;
$value = 0;
if( isset( $_GET[ 's'] ) ) {
	$taintedStart = $_GET[ 's' ];
	if( strlen( $taintedStart ) <= 2 ) {
		$s = intval( $taintedStart );
	
		if( filter_var( $s, FILTER_VALIDATE_INT ) ) {
			if( $s > $start ) {
				$start = $s;
			}
		
		}
	}
}


$value = ( $start * $end ) - $end;
$result = $wpdb->get_results("SELECT * from wp_product_list  ORDER BY ID DESC LIMIT $value,$end");
$result_count = $wpdb->get_results("SELECT * FROM wp_product_list where ppc_id='".$_GET[ 't']."' AND psc_id='".$_GET[ 'u']."' ORDER BY ID DESC LIMIT $value,$end");
echo "SELECT * FROM wp_product_list where ppc_id='".$_GET[ 't']."' AND psc_id='".$_GET[ 'u']."' ORDER BY ID DESC LIMIT $value,$end";
exit;
$pages = count($result_count) / 3;
$html = 'hello';
exit;
foreach($result as $row){
		$product_img = $row->product_image;
		if($product_img == '') { $imgStr = plugins_url($noimage, __FILE__ ); } else { $imgStr =  plugins_url($product_img, __FILE__ ); }
		$html .= '<div style="float:left;">';
		$html .= '<input type="checkbox" id="checkbox_example" name="product_information[]" value="'.$row->product_id.'"/>';
		$html .= '<span>'.$row->product_name .'</span>';
		$html .= '<a class="example-image-link" href="'. $imgStr .'" data-lightbox="example-set" data-title="' . $row->product_name .'"><img class="example-image" src="' . $product_img .'" height="150px" width="150px" alt="" /> </a>';
		$html .= '</div>';
	}
		$html .= '<div id="pagination">';

			for( $i = 0; $i < $pages; $i++ ) {
				$n = $i + 1;
				$current = ( $n == 1 ) ? ' class="current"' : '';
				$html .=  '<a href="#" data-page="' . $n .'"'. $current . '>' . $n . '</a>';
			}
		$html .= '</div>';
echo $html;		
?>