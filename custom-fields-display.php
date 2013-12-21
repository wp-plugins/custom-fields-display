<?php
/*
Plugin Name: Custom fields display
Plugin URI: http://wordpress.org/extend/plugins/custom-fields-display/
Description: Custom fields display in post content.You have option to specific custom filds display in post content in any theme.
Version: 1.0
Author: Shambhu Prasad Patnaik
Author URI:http://aynsoft.com/
*/



add_action('admin_menu', 'custom_fields_display_menus');

if (!function_exists('custom_fields_display_menus')):
function custom_fields_display_menus()
{
 add_menu_page('Custom Fileds Display', 'Custom Fileds Display', 8, __FILE__, 'custom_fields_display_admin_page');
}
endif;

if (!function_exists('custom_fields_display_admin_page')):
function custom_fields_display_admin_page()
{
 $plugin_page= plugin_basename(__FILE__);
 $action_url=admin_url('admin.php?page='.$plugin_page);
 $myCsseUrl = plugins_url('stylesheet.css', __FILE__);
global $wpdb;
$limit = (int) apply_filters( 'postmeta_form_limit', 30 );
$keys = $wpdb->get_col( "
		SELECT meta_key
		FROM $wpdb->postmeta
		GROUP BY meta_key
		HAVING meta_key NOT LIKE '\_%'
		ORDER BY meta_key
		LIMIT $limit" );
if ($keys)
natcasesort($keys);
 $select_field =array();
 $action = stripslashes_deep($_POST['update']); 

 if($action=='Show Now')
 {
  $default_option =array();
  foreach($keys as $key)
  {
   $field_id = stripslashes_deep($key);
   $field_check = stripslashes_deep($_POST['check_'.$field_id]);
   $field_label = stripslashes_deep($_POST['field_'.$field_id]);
   if($field_check=='on')
   {
    $select_field[] = $field_id;
   }
   if($field_label=='')
    $field_label = stripslashes_deep($field['label']);

   $default_option[$field_id] =$field_label; 
  }
  $data =array('default_option' => $default_option,'select_field' =>$select_field);
  $data=json_encode($data);
  update_option('custom_fields_display_option',$data); 
  update_option('custom_fields_display_message','Successfully Updated.');
  echo "<meta http-equiv='refresh' content='0;url=".$action_url."&message=update' />"; 
 }
 echo'<link rel="stylesheet" type="text/css" href="'.$myCsseUrl.'">';
 echo'<div class="wrap">
      <div id="icon-generic" class="icon32 "></div>      
      <h2>Custom Fields Display</h2><br>
	  <div>';

  if($custom_fields_display_message = get_option('custom_fields_display_message') && !isset($_POST['update']))
 {
  $custom_fields_display_message = get_option('custom_fields_display_message');
  echo' <div class="updated_setting"><p>'.$custom_fields_display_message.'</p></div>&nbsp;';
  update_option('custom_fields_display_message','');
 }

 $data = json_decode(get_option('custom_fields_display_option'));
 //print_r($data);die();
 $default_option  = (array)$data->default_option;
 $default_select  = (array)$data->select_field;
?>
 <h3 class="title">About</h3>
 <p>Custom fields display in post content.You have option to specific custom filds display in post content in any theme.</p>

<?php
echo'<table border="0" width="98%" cellspacing="1" cellpadding="4" class="middle_table1"> <form method="post" action="'.$action_url.'"  onsubmit="return ValidateForm(this)">        
      <tr class="dataTableHeadingRow">
       <th colspan="2">Show fields</th>
       <th>Display Label</th>
      </tr>';
 $i =1;
 foreach($keys as $key)
 {
  $field_id          = $key;
  $field_label       = $key;
  if(isset($default_option[$key]))
  $field_value       = $default_option[$key];
  else 
  $field_value       = $key;
  if(in_array($field_id,$default_select))
  $fild_checkbox = custom_fields_display_checkbox_field('check_'.$field_id,'on', true,'', ' id="check_'.$field_id.'" '); 
  else
  $fild_checkbox = custom_fields_display_checkbox_field('check_'.$field_id,'on', false,'', ' id="check_'.$field_id.'" '); 

  echo '<tr class="dataTableRow'.(($i%2==1)?'1':'2').'" >
       <td valign="top" >'.$fild_checkbox.'</td>
       <td valign="top" width="30%"><nobr><label for="check_'.$field_id.'" >'.$field_label.'</label></nobr></td>
       <td valign="top"><input type="text"  name ="field_'.$field_id.'" value="'.esc_attr($field_value).'" size="40"></td>
    </tr>';

  $i++;

 }
 echo' <tr>
        <td colspan="5"  align="center"><input type="submit" name="update" value="Show Now" class="button button-primary button-large"></td>
       </tr></form>
	 </table>';
 echo'</div></div>';

}
endif;

if (!function_exists('custom_fields_display_activate')):
function custom_fields_display_activate()
{
 update_option('custom_fields_display_option','');
}
endif;

if (!function_exists('custom_fields_display_checkbox_field')):
function custom_fields_display_checkbox_field($name, $value = '', $checked = false, $compare = '', $parameter = '')
{
 $str = '<input type="checkbox" name="' . $name . '"';
 if ($value != '') 
 {
  $str .= ' value="' . $value . '"';
 }
 if ( ($checked == true) || ($value && ($value == $compare)) ) 
 {
  $str .= ' CHECKED';
 }
 if ($parameter != '') 
 {
  $str .= ' ' . $parameter;
 }   
 $str .= '>';
 return $str;

}
endif;

//add_action( 'init', 'custom_fields_display_init' );
//function custom_fields_display_init()
{
 add_filter( 'the_content', 'custom_fields_display_content_filter', 20 );
}

register_deactivation_hook(__FILE__, 'custom_fields_display_deactivate');
register_activation_hook(__FILE__, 'custom_fields_display_activate');

if (!function_exists('custom_fields_display_deactivate')):
function custom_fields_display_deactivate()
{
 update_option('custom_fields_display_option','');
}
endif;

if ( ! function_exists( 'custom_fields_display_content_filter' ) ) :
function custom_fields_display_content_filter( $content )
{
 global $post;
 $data = json_decode(get_option('custom_fields_display_option'));
 $default_option  = (array)$data->default_option;
 $default_select  = (array)$data->select_field;
 $add_content='';
 if ( is_single() ):
 if(count($default_select) > 0)
 { 
  $add_content ='<ul class=\'post-meta\'>';
  foreach($default_select as $key)
  {
   $value = get_post_meta($post->ID,$key,true);
   if($value !='')
   {
    $add_content .='<li>';
    if(isset($default_option[$key]) &&  $value!='' )
    $add_content .='<span class=\'post-meta-key\'>'.esc_attr($default_option[$key]).'</span> ';
    $add_content .=esc_attr($value).'</li>';
   }
  }
  $add_content .='</ul>';
 }
 endif;

 return $add_content.$content;
}
endif;

?>