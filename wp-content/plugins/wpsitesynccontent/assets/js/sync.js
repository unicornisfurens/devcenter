/*
 * @copyright Copyright (C) 2014-2016 SpectrOMtech.com. - All Rights Reserved.
 * @author SpectrOMtech.com <SpectrOMtech.com>
 * @url https://wpsitesync.com/license
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images,
 * manuals, cascading style sheets, and included JavaScript *are NOT GPL*, and are released under the
 * SpectrOMtech Proprietary Use License v1.0
 * More info at https://wpsitesync.com
 */

/**
 * Javascript handlers for SYNC running on the post editor page
 * @since 1.0
 * @author SpectrOMtech
 */
function WPSiteSyncContent()
{
	this.$content = null;
	this.disable = false;
	this.post_id = null;
	this.original_value = '';
}


/**
 * Initializes SYNC operations on the page
 */
WPSiteSyncContent.prototype.init = function()
{
	var _self = this;

	this.$content = jQuery('#content');
	this.original_value = this.$content.val();
	this.$content.on('keypress change', function() { _self.on_content_change(); });
};

/**
 * Disables SYNC Button every time the content changes. 
 */
WPSiteSyncContent.prototype.on_content_change = function()
{
	if (this.$content.val() !== this.original_value) {
		this.disable = true;
		jQuery('#btn-sync').attr('disabled', true);
		jQuery('#disabled-notice-sync').show();
	} else {
		this.disable = false;
		jQuery('#btn-sync').removeAttr('disabled');
		jQuery('#disabled-notice-sync').hide();
	}
};

/**
 * Causes the browser to refresh the page contents
 */
WPSiteSyncContent.prototype.force_refresh = function()
{
	jQuery(window).trigger('resize');
};

/**
 * SYNC Content button handler
 * @param {int} post_id The post id to perform Push operations on
 */
WPSiteSyncContent.prototype.push = function(post_id)
{
console.log('push()');
	// Do nothing when in a disabled state
	if (this.disable)
		return;

	// clear the message to start things off
	jQuery('#sync-message').html('');
//	jQuery('#sync-message').html(jQuery('#sync-working-msg').html());
	jQuery('#sync-content-anim').show();
	jQuery('#sync-message').parent().hide().show(0);

	this.force_refresh();

	this.post_id = post_id;
	var data = { action: 'spectrom_sync', operation: 'push', post_id: post_id, _sync_nonce: jQuery('#_sync_nonce').val() };

	var push_xhr = {
		type: 'post',
		async: false,
		data: data,
		url: ajaxurl,
		success: function(response) {
console.log('push() success response:');
console.log(response);
			if (response.success) {
				jQuery('#sync-message').text(jQuery('#sync-success-msg').text());
			} else {
				if ('undefined' !== typeof(response.data.message))
					jQuery('#sync-message').text(response.data.message);
			}
			jQuery('#sync-content-anim').hide();
		},
		error: function(response) {
console.log('push() failure response:');
console.log(response);
			jQuery('#sync-content-anim').hide();
		}
	};

	// Allow other plugins to alter the ajax request
	jQuery(document).trigger('sync_push', [push_xhr]);
console.log('push() calling jQuery.ajax');
	jQuery.ajax(push_xhr);
console.log('push() returned from ajax call');
};

var wpsitesynccontent = new WPSiteSyncContent();

// initialize the SYNC operation on page load
jQuery(document).ready(function() {
	wpsitesynccontent.init();
});

// EOF
