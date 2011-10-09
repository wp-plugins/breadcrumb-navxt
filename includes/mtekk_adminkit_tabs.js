jQuery(function()
{
	mtekk_admin_tabulator_init();
});
/**
 * Tabulator Bootup
 */
function mtekk_admin_tabulator_init(){
	if (!jQuery("#hasadmintabs").length) return;
	/* init markup for tabs */
	jQuery('#hasadmintabs').prepend("<ul><\/ul>");
	jQuery('#hasadmintabs > fieldset').each(function(i){
		id = jQuery(this).attr('id');
		caption = jQuery(this).find('h3').text();
		jQuery('#hasadmintabs > ul').append('<li><a href="#'+id+'"><span>'+caption+"<\/span><\/a><\/li>");
		jQuery(this).find('h3').hide();
	});
	/* init the tabs plugin */
	jQuery("#hasadmintabs").tabs();
	/* handler for opening the last tab after submit (compability version) */
	jQuery('#hasadmintabs ul a').click(function(i){
		var form   = jQuery('#'+objectL10n.mtad_uid+'-options');
		var action = form.attr("action").split('#', 1) + jQuery(this).attr('href');
		form.get(0).setAttribute("action", action);
	});
}