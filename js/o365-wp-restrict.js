/* 

 * Used in menu callback page.

 * Version: 1.0

 */
var wp_restrict_mapping_level = 0;
jQuery(document).ready(function(e) {
	
	var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:0});
	
	jQuery("#o365_wp_restrict_mapping_add_field_button").click(function(e){
		jQuery("#o365-wp-restrict-please-wait-bg").css("display","block");
			/*add select box for wordpress role start code*/
		var data = { 'action':'o365_wp_restrict_wp_roles','wp_selected_role':"" };
		var wp_restrict_mapping_level_tr = jQuery( "#o365_wp_restrict_wp_role_redirect_content tr:last" ).attr('id');
		
		if (typeof wp_restrict_mapping_level_tr === "undefined")
		{
			wp_restrict_mapping_level = parseInt(0);
		}
		else
		{
			last_mapped_tr = wp_restrict_mapping_level_tr.split('__');
			if(last_mapped_tr[1])
			{
				wp_restrict_mapping_level = parseInt(last_mapped_tr[1]) + 1;
			}
		}
		
		jQuery.post( ajaxurl, data , function( response, txtStatus, jqXHR ){
			var o365_wp_restrict_string="";
			/*add wordpress role in select box start*/
			o365_wp_restrict_string += "<tr class='o365_data_row_cstm' id='o365_wp_restrict_mapping_table_row__"+wp_restrict_mapping_level+"'><td class='o365_data_row_inputs'><select name='o365_wp_restrict_roles["+wp_restrict_mapping_level+"][]'><option value=''>Select Wordpress Role</option>";
			if( response != "" )
			{
				o365_wp_restrict_string += response;
			}
			o365_wp_restrict_string += "</td>";
			o365_wp_restrict_string += "<td class='o365_data_row_inputs'>";
			o365_wp_restrict_string += "<input id='o365_wp_restrict_role_based_redirect_url["+wp_restrict_mapping_level+"][]' name='o365_wp_restrict_role_based_redirect_url["+wp_restrict_mapping_level+"][]' type='text' value='' />";
			o365_wp_restrict_string += "</td>";
			o365_wp_restrict_string += "<td class='less_button' onclick='o365_wp_restrict_remove_mapping_fields_selected_row("+wp_restrict_mapping_level+")'> - </td>";
			o365_wp_restrict_string += "</tr>";
			
			/*add wordpress role in select box end*/
			jQuery("#o365_wp_restrict_wp_role_redirect_content").append( o365_wp_restrict_string );
			jQuery("#o365-wp-restrict-please-wait-bg").css("display","none");
			
		}).fail(function(){
			console.log( "Fail" );
			jQuery("#o365-wp-restrict-please-wait-bg").css("display","none");
		});
	});

	jQuery("#o365_wp_restrict_enable_auth").change(function(e) {
		if(jQuery("#o365_wp_restrict_enable_auth").val()== "other")
		{
			jQuery("#o365_wp_restrict_enable_auth_other").css("display","block");
			jQuery("#o365_wp_restrict_enable_auth_other").attr("required","required");
		}
		else
		{
			jQuery("#o365_wp_restrict_enable_auth_other").css("display","none");
			jQuery("#o365_wp_restrict_enable_auth_other").removeAttr("required","required");
			jQuery("#o365_wp_restrict_enable_auth_other").val('');
		}
	});
	/* custom al */
	jQuery('.help_inc').hover(function(e) {
		jQuery(this).next('.question_block').toggleClass('active_help');
	});
	jQuery('.question_block').hover(function(e) {
		jQuery(this).toggleClass('active_help');
	});
	/* custom al */
});



function o365_wp_restrict_remove_mapping_fields_selected_row(selected_row)
{
	jQuery("#o365_wp_restrict_mapping_table_row__"+selected_row).closest( 'tr').remove();
}