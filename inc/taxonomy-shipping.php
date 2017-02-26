<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

class PWS_state_city_taxonomy {
	
	public function __construct(){
			
		add_filter( 'state_city_row_actions', array($this, 'state_city_row_actions'), 10, 2);

		add_action( 'admin_menu', array($this, 'admin_menu'), 10);
		add_action( 'edited_state_city', array($this, 'save_state_city'), 10, 2 );
		add_action( 'create_state_city', array($this, 'save_state_city'), 10, 2 );
		add_action( 'state_city_add_form_fields', array($this, 'state_city_add_form_fields'), 10, 2 );
		add_action( 'state_city_edit_form_fields', array($this, 'state_city_edit_form_fields'), 10, 2 );
	}

	public function state_city_row_actions( $actions, $term ){
		if($term->parent)
			return $actions;

		$edit_list_link = add_query_arg(
			'term_id',
			$term->term_id,
			admin_url('admin.php?page=sabira_edit_state')
		);

		$actions['edit_list'] = "<a href='{$edit_list_link}'>ویرایش شهر ها</a>";

		return $actions;
	}
	
	public function admin_menu(){
		add_submenu_page( '', '', '', 'manage_woocommerce', 'sabira_edit_state', array($this, 'sabira_edit_state_callback') );
	}
	
	public function save_state_city( $term_id ) {
		if ( isset( $_POST['term_meta'] ) && is_array($_POST['term_meta']) )
			update_option( "sabira_taxonomy_$term_id", $_POST['term_meta'] );
	}

	public function state_city_add_form_fields() {
		?>
		<div class="form-field">
			<label for="term_meta[tipax_on]">فعال بودن تیپاکس</label>
			<input type="checkbox" name="term_meta[tipax_on]" id="term_meta[tipax_on]" value="1">
		</div>
		<div class="form-field">
			<label for="term_meta[tipax_cost]">هزینه تیپاکس</label>
			<input type="text" name="term_meta[tipax_cost]" id="term_meta[tipax_cost]" placeholder="<?php echo get_woocommerce_currency_symbol(); ?>" />
		</div>
		<div class="form-field">
			<label for="term_meta[courier_on]">فعال بودن پیک موتوری</label>
			<input type="checkbox" name="term_meta[courier_on]" id="term_meta[courier_on]" value="1">
		</div>
		<div class="form-field">
			<label for="term_meta[courier_cost]">هزینه پیک موتوری</label>
			<input type="text" name="term_meta[courier_cost]" id="term_meta[courier_cost]" placeholder="<?php echo get_woocommerce_currency_symbol(); ?>" />
		</div>
		<div class="form-field">
			<label for="term_meta[custom_cost]">هزینه پست سفارشی</label>
			<input type="text" name="term_meta[custom_cost]" id="term_meta[custom_cost]" placeholder="<?php echo get_woocommerce_currency_symbol(); ?>" />
		</div>
		<div class="form-field">
			<label for="term_meta[forehand_cost]">هزینه پست پیشتاز</label>
			<input type="text" name="term_meta[forehand_cost]" id="term_meta[forehand_cost]" placeholder="<?php echo get_woocommerce_currency_symbol(); ?>" />
		</div>
		<?php
	}
	
	public function state_city_edit_form_fields( $term ) {

		$state = !$term->parent;
		$term_meta = PWS()->get_term_options( $term->term_id );
		$city_type = $state ? "استان" : "شهر";

		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[tipax_on]">فعال بودن تیپاکس</label></th>
			<td>
				<input type="checkbox" name="term_meta[tipax_on]" id="term_meta[tipax_on]" value="1" <?php checked($term_meta['tipax_on'], 1); ?>>
				<p class="description">توجه کنید در صورتی که تیپاکس استان غیرفعال باشد، تیپاکس شهر خود به خود غیرفعال است.</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[tipax_cost]">هزینه تیپاکس</label></th>
			<td>
				<input type="text" name="term_meta[tipax_cost]" id="term_meta[tipax_cost]" value="<?php echo $term_meta['tipax_cost']; ?>" placeholder="<?php echo get_woocommerce_currency_symbol(); ?>" />
				<p class="description">هزینه ارسال تیپاکس به این <?php echo $city_type; ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[courier_on]">فعال بودن پیک موتوری</label></th>
			<td>
				<input type="checkbox" name="term_meta[courier_on]" id="term_meta[courier_on]" value="1" <?php checked($term_meta['courier_on'], 1); ?>>
				<p class="description">توجه کنید در صورتی که پیک موتوری استان غیرفعال باشد، پیک موتوری شهر خود به خود غیرفعال است.</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[courier_cost]">هزینه پیک موتوری</label></th>
			<td>
				<input type="text" name="term_meta[courier_cost]" id="term_meta[courier_cost]" value="<?php echo $term_meta['courier_cost']; ?>" placeholder="<?php echo get_woocommerce_currency_symbol(); ?>" />
				<p class="description">هزینه ارسال با پیک موتوری به این <?php echo $city_type; ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[custom_cost]">هزینه پست سفارشی</label></th>
			<td>
				<input type="text" name="term_meta[custom_cost]" id="term_meta[custom_cost]" value="<?php echo $term_meta['custom_cost']; ?>" placeholder="<?php echo get_woocommerce_currency_symbol(); ?>" />
				<p class="description"> هزینه ارسال با پست سفارشی به این <?php echo $city_type; ?> (با احتساب بیمه و مالیات) - برای استفاده از قیمت پیش فرض خالی بگذارید</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[forehand_cost]">هزینه پست پیشتاز</label></th>
			<td>
				<input type="text" name="term_meta[forehand_cost]" id="term_meta[forehand_cost]" value="<?php echo $term_meta['forehand_cost']; ?>" placeholder="<?php echo get_woocommerce_currency_symbol(); ?>" />
				<p class="description">هزینه ارسال با پست پیشتاز به این <?php echo $city_type; ?> (با احتساب بیمه و مالیات) - برای استفاده از قیمت پیش فرض خالی بگذارید</p>
			</td>
		</tr>
		<?php
	}
	
	
	public function sabira_edit_state_callback(){
		
		$state_term = get_term($_GET['term_id'], 'state_city');
		
		if(is_wp_error($state_term)){
			wp_redirect(admin_url('edit-tags.php?taxonomy=state_city&post_type=product'));
			return false;
		}
		
		if($state_term->parent){
			wp_redirect(add_query_arg(
				'term_id',
				$state_term->parent,
				admin_url('admin.php?page=sabira_edit_state')
			));
			return false;
		}

		$msg = "";

		if(isset($_POST['submit']))
			if(isset($_POST['taxonomy'], $_POST['tag_ID'], $_POST['action'])){
				foreach ($_POST['term_meta'] as $term_id => $term)
					update_option( "sabira_taxonomy_$term_id", $term);
				$msg = '<div id="message" class="updated"><p><strong>تنظیمات با موفقیت ذخیره شدند.</strong></p></div>';
			}

		$terms = array( -1 => $state_term) + get_terms(array('taxonomy' => 'state_city', 'hide_empty' => false, 'child_of' => $state_term->term_id));
		?>
		<div class="wrap">
		<h1>ویرایش استان <?php echo $state_term->name; ?></h1>
		<?php echo $msg; ?>

		<div id="ajax-response"></div>
		
		<?php echo PWS()->donate(); ?>
		
		<form name="edittag" id="edittag" method="post" action="" class="validate">
		<input type="hidden" name="action" value="editedtag"/>
		<input type="hidden" name="tag_ID" value="<?php echo esc_attr( $state_term->term_id ) ?>"/>
		<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $state_term->taxonomy ) ?>" />
		<p class="submit">
			<input class="button button-primary check_tipax" value="فعال کردن تیپاکس همه" type="button">
			<input class="button uncheck_tipax" value="غیر فعال کردن تیپاکس همه" type="button">
			<input class="button button-primary check_courier" value="فعال کردن پیک موتوری همه" type="button">
			<input class="button uncheck_courier" value="غیر فعال کردن پیک موتوری همه" type="button">
		</p>
		<table class="widefat fixed" cellspacing="0">
			<thead>
				<tr>
					<th>نام شهر</th>
					<th style="width: 50px;">تیپاکس</th>
					<th>هزینه تیپاکس</th>
					<th style="width: 70px;">پیک موتوری</th>
					<th>هزینه پیک موتوری</th>
					<th>هزینه پست سفارشی</th>
					<th>هزینه پست پیشتاز</th>
				</tr>
			</thead>
			<tfoot>
			<tr>
				<th>نام شهر</th>
				<th>تیپاکس</th>
				<th>هزینه تیپاکس</th>
				<th>پیک موتوری</th>
				<th>هزینه پیک موتوری</th>
				<th>هزینه پست سفارشی</th>
				<th>هزینه پست پیشتاز</th>
			</tr>
			</tfoot>
			<tbody>
				<?php foreach($terms as $term){
					$i = $term->term_id;
					$term_option = PWS()->get_term_options($i);
					$class = ($i == $state_term->term_id) ? "style='background:#52ACCC;'" : "";
					$term->name = $i == $state_term->term_id ? "استان {$term->name}" : $term->name;

					$j = str_repeat("- ", max(count(get_ancestors($term->term_id, 'state_city')) - 1, 0));
					echo "<tr {$class}>
					<td>{$j}{$term->name}</td>
					<td><center><input type=\"checkbox\" name=\"term_meta[{$i}][tipax_on]\" id=\"term_meta[{$i}][tipax_on]\" class=\"tipax_on\" value=\"1\" ".checked($term_option['tipax_on'], 1, false)."></center></td>
					<td><input type=\"text\" name=\"term_meta[{$i}][tipax_cost]\" id=\"term_meta[{$i}][tipax_cost]\" value=\"{$term_option['tipax_cost']}\"></td>
					<td><center><input type=\"checkbox\" name=\"term_meta[{$i}][courier_on]\" id=\"term_meta[{$i}][courier_on]\" class=\"courier_on\" value=\"1\" ".checked($term_option['courier_on'], 1, false)."></center></td>
					<td><input type=\"text\" name=\"term_meta[{$i}][courier_cost]\" id=\"term_meta[{$i}][courier_cost]\" value=\"{$term_option['courier_cost']}\"></td>
					<td><input type=\"text\" name=\"term_meta[{$i}][custom_cost]\" id=\"term_meta[{$i}][custom_cost]\" value=\"{$term_option['custom_cost']}\"></td>
					<td><input type=\"text\" name=\"term_meta[{$i}][forehand_cost]\" id=\"term_meta[{$i}][forehand_cost]\" value=\"{$term_option['forehand_cost']}\"></td>
					</tr>";

					$previous_term_id = $i;
				} ?>
			</tbody>
		</table>
		<p class="submit">
			<input class="button button-primary check_tipax" value="فعال کردن تیپاکس همه" type="button">
			<input class="button uncheck_tipax" value="غیر فعال کردن تیپاکس همه" type="button">
			<input class="button button-primary check_courier" value="فعال کردن پیک موتوری همه" type="button">
			<input class="button uncheck_courier" value="غیر فعال کردن پیک موتوری همه" type="button">
		</p>
		
		<?php 
			echo PWS()->donate();
			submit_button( __('Update') );
		?>
		</form>
		</div>
		
		<style>
			table, tr, th, td, input, h1, div, p {
				font-family: Segoe UI;
			}
			tr:nth-child(even) {background: #CCC}
			tr:nth-child(odd) {background: #FFF}
			tr:hover {background: #72C8E5}
			input[type=text]{
				width: 100%;
			}
			th { text-align: center !important; }
		</style>
		<script>
			jQuery(document).ready(function($) {
				$(".check_tipax").click(function(){ $(".tipax_on").prop('checked', true); });
				$(".uncheck_tipax").click(function(){ $(".tipax_on").prop('checked', false); });
				$(".check_courier").click(function(){ $(".courier_on").prop('checked', true); });
				$(".uncheck_courier").click(function(){ $(".courier_on").prop('checked', false); });
			});
		</script>
		<?php
	}
}	

return new PWS_state_city_taxonomy();
