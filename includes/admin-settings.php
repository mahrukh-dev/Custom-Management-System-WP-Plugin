<?php 

function cms_settings_init() {
	// register a new setting for "reading" page
	register_setting('cms-plugin', 'cms_setting_field_txt');
    register_setting('cms-plugin', 'cms_setting_field_checkbox');


	// register a new section in the "reading" page
	add_settings_section(
		'cms_settings_section',
		'CMS Settings Section', 
        'cms_settings_section_callback',
		'cms-plugin'
	);

	// register a new field in the "wporg_settings_section" section, inside the "reading" page
	add_settings_field(
		'cms_settings_field',
		'CMS Setting', 
        'cms_settings_field_callback',
		'cms-plugin',
		'cms_settings_section'
	);

    // register a new field in the "wporg_settings_section" section, inside the "reading" page
	add_settings_field(
		'cms_settings_field_2',
		'CMS Setting', 
        'cms_settings_field_callback_2',
		'cms-plugin',
		'cms_settings_section'
	);
}

/**
 * register wporg_settings_init to the admin_init action hook
 */
add_action('admin_init', 'cms_settings_init');

/**
 * callback functions
 */

// section content cb
function cms_settings_section_callback() {
	echo '<p>WPOrg Section Introduction.</p>';
}

// field content cb
function cms_settings_field_callback() {
	// get the value of the setting we've registered with register_setting()
	$setting = get_option('cms_setting_field_txt');
	// output the field
	?>
	<input type="text" name="cms_setting_field_txt" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
    <?php
}

function cms_settings_field_callback_2() {
	// get the value of the setting we've registered with register_setting()
	$setting = get_option('cms_setting_field_checkbox');
	// output the field
	?>
	<input type="checkbox" name="cms_setting_field_checkbox">
    <?php
}