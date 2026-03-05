<?php

include('authentication.php');

function clover_settings_init() {

    global $wpdb, $table_name;

    $defaultValues = $wpdb->get_row("SELECT * FROM ".$table_name." where id=1");

    $status = "NULL";

    if ($defaultValues->apiKey != NULL){
        $status = $defaultValues->apiKey;
    }
	// Register a new setting for "clover" page.
	register_setting( 'clover', 'clover_options' );

	// Register a new section in the "clover" page.
	add_settings_section(
		'clover_section_developers',
		__( '', 'clover' ), 'clover_section_developers_callback',
		'clover'
	);

	// Register a new field in the "clover_section_developers" section, inside the "clover" page.
	add_settings_field(
		'app_id', 
        __( 'Api Token', 'clover' ),
		'clover_cb',
		'clover',
		'clover_section_developers',
        array(
			'label_for'         => 'app_id',
            'default'           => $defaultValues->apiToken
		)
	);

    add_settings_field(
		'code', 
        __( 'Api Key', 'clover' ),
		'clover_code',
		'clover',
		'clover_section_developers',
        array(
			'label_for' => 'code',
            'default'    => $status
		)
	);

	add_settings_field(
		'email', 
        __( 'Reciept Email', 'clover' ),
		'clover_email',
		'clover',
		'clover_section_developers',
        array(
			'label_for' => 'email',
            'default'    => $defaultValues->email
		)
	);

	add_settings_field(
		'development', 
        __( 'Development', 'clover' ),
		'clover_dev',
		'clover',
		'clover_section_developers',
        array(
			'label_for' => 'development',
            'default'    => $defaultValues->development
		)
	);
}

function clover_section_developers_callback( $args ) {
	?>
	<?php
}

function clover_cb( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'clover_options' );
	?>

    <input id="<?php echo esc_attr( $args['label_for'] );?>" name="<?php echo esc_attr( $args['label_for'] );?>" value="<?php echo esc_attr( $args['default'] );?>"></input>

	<?php
}

function clover_code( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'clover_options' );
	

    if ($args["default"] == "NULL"){
        ?>
		<a href="<?php echo get_site_url()?>/wp-json/clover-api/v1/oauth-start">Generate
		</a>
        <?php

	}else{
        ?>
        <p><?php echo $args["default"]?></p>
        <?php
    }
}

function clover_email( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'clover_options' );
	?>

    <input id="<?php echo esc_attr( $args['label_for'] );?>" name="<?php echo esc_attr( $args['label_for'] );?>" value="<?php echo esc_attr( $args['default'] );?>"></input>

	<?php
}

function clover_dev( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'clover_options' );
	?>

    <input type="checkbox" id="<?php echo esc_attr( $args['label_for'] );?>" name="<?php echo esc_attr( $args['label_for'] );?>" <?php if (esc_attr( $args['default'] ) == 1) echo "checked";?>></input>

	<script>
		$(document).ready(function() {
			// Attach a change event listener to the checkbox
			$('#development').on('change', function() {
				// If the checkbox is checked, set its value to 1
				// If unchecked, set its value to 0
				if ($(this).is(':checked')) {
					$(this).val(1);
				} else {
					$(this).val(0);
				}
			});
		});
	</script>
	<?php
}

function clover_options_page() {
	add_menu_page(
		'Clover API',
		'Clover API',
		'manage_options',
		'clover',
		'clover_options_page_html'
	);
}

function clover_options_page_html() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        global $wpdb;
		// add settings saved message with the class of "updated"

        $table_name = $wpdb->prefix . 'clover';

        $app_id=$_POST["app_id"];
		$email=$_POST["email"];
		$dev=$_POST["development"];

        $sql = "UPDATE ".$table_name." set apiToken='".$app_id."', email='".$email."', development='".$dev."' where id=1";
        $results = $wpdb->query($sql);

		header("Refresh:0");

    }

	// add error/update messages

	// check if the user have submitted the settings
	// WordPress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {

		global $wpdb;
		// add settings saved message with the class of "updated"

        $table_name = $wpdb->prefix . 'clover';

        $email=$_POST["email"];

        $sql = "UPDATE ".$table_name." set email='".$email."' where id=1";
        $results = $wpdb->query($sql);

		add_settings_error( 'clover_messages', 'clover_message', __( 'Settings Saved', 'clover' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'clover_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="" method="post">
			<?php
			// output security fields for the registered setting "clover"
			settings_fields( 'clover' );
			// output setting sections and their fields
			// (sections are registered for "clover", each field is registered to a specific section)
			do_settings_sections( 'clover' );
			// output save settings button
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}

add_action( 'admin_init', 'clover_settings_init' );
add_action( 'admin_menu', 'clover_options_page' );

?>