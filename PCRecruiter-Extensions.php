<?php
/*
Plugin Name: PCRecruiter Extensions
Plugin URI: https://www.pcrecruiter.net
Description:  Embeds PCRecruiter forms and iframe content via shortcodes, and facilitiates RSS/XML Job Feeds.
Version: 1.4.10
Author: Main Sequence Technology, Inc.
Author URI: https://www.pcrecruiter.net
License: GPL3
*/

/*
Job Feed Display on page
*/

function pcr_assets() {
    wp_register_script( 'pcr-iframe', 'https://www2.pcrecruiter.net/pcrimg/inc/pcrframehost.js', false, false, false);
    wp_enqueue_script( 'pcr-iframe' );
}
add_action( 'wp_enqueue_scripts', 'pcr_assets' );
function pcr_frame($atts)
{
    $a = shortcode_atts(array(
        'link' => 'about:blank',
        'background' => 'transparent',
        'initialheight' => '640',
		'analytics' => '',
        'form' => ''
    ), $atts);
    // If the link was empty and the formnumber is numeric insert the custom form
    $sid         = $a['form'];
    $loadurl     = $a['link'];
	$analytics     = $a['analytics'];
	if ($a['analytics'] != '') {
		$analytics = ' analytics="true" ';
	};
    $pcrframecss = '<link rel="stylesheet" href="https://www2.pcrecruiter.net/pcrimg/inc/pcrframehost.css">';
    // If the link doesn't contain a specific module, append jobboard.aspx
    if (strpos($loadurl, '.asp?') === false && strpos($loadurl, '.exe?') === false && strpos($loadurl, '.aspx?') === false) {
            $loadurl     = 'jobboard.aspx?uid=' . $loadurl;
            $pcrframecss = '';
    };
    // If the link has a form number, load customform script instead of job board
    if (is_numeric($sid) && $loadurl !== "about:blank") {
        return '<!-- Start PCRecruiter Form --><script src="https://www2.pcrecruiter.net/pcrbin/' . $loadurl . '&action=opencustomform&sid=' . $sid . '"></script><!-- End PCRecruiter Form -->';
    } else {
        // If the link doesn't start with http and doesn't start with jobboard, prepend the Classic ASP URL and add CSS link
        if (substr($loadurl, 0, 4) !== "http" && substr($loadurl, 0, 8) !== "jobboard") {
            $aspurl  = 'https://www2.pcrecruiter.net/pcrbin/' . $loadurl;
            $loadurl = $aspurl;
        }
        ;
        // If the link doesn't start with http and starts with jobboard, prepend the Job Board URL
        if (substr($loadurl, 0, 4) !== "http" && substr($loadurl, 0, 8) == "jobboard") {
            $pcrframecss = '';
            $aspurl      = 'https://host.pcrecruiter.net/pcrbin/' . $loadurl;
            $loadurl     = $aspurl;
        };
        return "<!-- Start PCRecruiter WP 1.4.10-->" . $pcrframecss . "<iframe frameborder=\"0\" host=\"{$loadurl}\" id=\"pcrframe\" name=\"pcrframe\" src=\"about:blank\" style=\"height:{$a['initialheight']}px;width:100%;background-color:{$a['background']};border:0;margin:0;padding:0\" {$analytics} onload=\"pcrframeurl();\"></iframe><!-- End PCRecruiter WP -->";
    }
}
add_shortcode('PCRecruiter', 'pcr_frame');


/* Remove Canonical Link */
remove_action('wp_head', 'rel_canonical');
add_filter( 'wpseo_canonical', '__return_false' );

/*

BEGIN Get PCR Feed

*/

// checkop() to see if options exist. If not, set the defaults
function checkop() {
    //check if option is already present
    //option key is pcr_feed_options
    if(!get_option('pcr_feed_options')) {
        //not present, so add
        $pcr_options = array(
            //'activation'    =>  'true',
            'frequency'     =>  'daily',
            'query'         =>  'Status%20eq%20Available%20and%20ShowOnWeb%20eq%20true%20and%20%28NumberOfOpenings%20ne%200%20OR%20NumberOfOpenings%20eq%20%27%27)',
            'mode'          =>  'job',
        );
        add_option('pcr_feed_options', $pcr_options);
    }
}

register_activation_hook(__FILE__, 'pcr_feed_activation');

function pcr_feed_activation(){
    // set the defaults with checkop()
    checkop();
    pcr_feed_func();
}
add_action('pcr_feed', 'pcr_feed_activation');

function pcr_feed_func(){
    // do this via cron
    $pcr_feed_options = get_option('pcr_feed_options', array());
    $pcr_customfields = str_replace(' ', '%20', $pcr_feed_options['custom_fields']  ?? '');
    $pcr_standardfields = str_replace(' ', '%20', $pcr_feed_options['standard_fields']  ?? '');
	$pcr_id_number = $pcr_feed_options['id_number'] ?? '';
	$pcr_activation = $pcr_feed_options['activation'] ?? '';
    $url = "https://host.pcrecruiter.net/pcrbin/feeds.aspx?action=customfeed&query=". $pcr_feed_options['query'] ."&xtransform=RSS2&SessionId=" . $pcr_id_number . "&FieldsPlus=" . $pcr_standardfields . "&custom=" . $pcr_customfields . "&mode=" . $pcr_feed_options['mode'];


    if($pcr_activation){

        // JSON
        //$xml = simplexml_load_string($url, null, LIBXML_NOCDATA, "http://www.pcrecruiter.net");
        $buffer = file_get_contents($url);
        $buffer = str_replace(array("\n", "\r", "\t"), '', $buffer);
        $xml = simplexml_load_string($buffer, null, LIBXML_NOCDATA);
        $namespaces = $xml->getDocNamespaces(true);

       //var_dump($namespaces);

        $array = [];
        foreach ($namespaces as $namespace) {
			if (is_object($namespace) && property_exists($namespace, 'key')) {
				$xml = simplexml_load_string($buffer, null, LIBXML_NOCDATA, $namespace->key);
				$array = array_merge_recursive($array, (array) $xml);
			} else {
				// Handle the case where $namespace is not a valid object with a 'key' property
				// For example: log an error or skip this iteration
			}
		}

        $json = json_encode($array, JSON_PRETTY_PRINT);

        //$xml = file_get_contents($url);
        //$xml = str_replace('<pcr:', '<', $xml);
        //$xml = simplexml_load_string($xml, null, LIBXML_NOCDATA);
        //
        //$xml->channel->item->children('pcr', true);
        //$xml->registerXPathNamespace('pcr', 'http://www.pcrecruiter.net');
        // use XPath to get all pcr:* nodes
        /*
        $items = $xml->xpath('//pcr:*');
        foreach($items as $item) {
            $pcrItems = $pcrItems . $item;
        }
        */
        //
        //$xml->createElementNS('//pcr:*', '//*'); // no prefix
        //$json = json_encode($xml, JSON_PRETTY_PRINT);
        //$array = json_decode($json,TRUE);
        //$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
        //$fileContents = trim(str_replace('"', "'", $fileContents));
        //$simpleXml = simplexml_load_string($fileContents);
        //$json_data = json_encode($fileContents);
        file_put_contents(WP_CONTENT_DIR . '/uploads/pcrjobfeed.json', $json);

        if (!copy($url, WP_CONTENT_DIR . "/uploads/pcrjobfeed.xml")) {

        } else {

        }
    }
}

// deactivate hook
register_deactivation_hook(__FILE__, 'pcr_deactivation');
function pcr_deactivation(){
    wp_clear_scheduled_hook('pcr_feed');
}
/**/
/*
END Get PCR Feed
*/
/*
Settings for the PCRecruiter Extensions Plugin
*/
class PcrSettingsPage
{
    /**
    * Holds the values to be used in the fields callbacks
    */
    private $options;

    /**
    * Start up
    */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
    * Add options page
    */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'PCRecruiter Extensions',
            'manage_options',
            'pcr-ext-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
    * Options page callback
    */
    public function create_admin_page()
    {
        $this->show_cron_status();
        // Set class property
        $this->options = get_option( 'pcr_feed_options' );
        ?>
        <div class="wrap">
            <h1>PCRecruiter Extensions Settings</h1>
            <form method="post" onsubmit="return validatePCRfeed()" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'pcr_ext_option_group' );
                do_settings_sections( 'pcr-ext-setting-admin' );
             ?>

                <?php submit_button();?>
            </form>
        </div>
        <?php
    }
    /**
     * Gets the status of cron functionality on the site by performing a test spawn. Cached for one hour when all is well.
     *
     * @param bool $cache Whether to use the cached result from previous calls.
     * @return true|WP_Error Boolean true if the cron spawner is working as expected, or a WP_Error object if not.
     */
    public function test_cron_spawn( $cache = true ) {
        global $wp_version;

        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
            /* translators: 1: The name of the PHP constant that is set. */
            return new WP_Error( 'cron_info', sprintf( __( 'The %s constant is set to true. WP-Cron spawning is disabled.', 'wp-cron' ), 'DISABLE_WP_CRON' ) );
        }

        if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
            /* translators: 1: The name of the PHP constant that is set. */
            return new WP_Error( 'cron_info', sprintf( __( 'The %s constant is set to true.', 'wp-cron' ), 'ALTERNATE_WP_CRON' ) );
        }

        $cached_status = get_transient( 'wp-cron-test-ok' );

        if ( $cache && $cached_status ) {
            return true;
        }

        $sslverify     = version_compare( $wp_version, 4.0, '<' );
        $doing_wp_cron = sprintf( '%.22F', microtime( true ) );

        $cron_request = apply_filters( 'cron_request', array(
            'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
            'key'  => $doing_wp_cron,
            'args' => array(
                'timeout'   => 3,
                'blocking'  => true,
                'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
            ),
        ) );

        $cron_request['args']['blocking'] = true;

        $result = wp_remote_post( $cron_request['url'], $cron_request['args'] );

        if ( is_wp_error( $result ) ) {
            return $result;
        } elseif ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
            return new WP_Error( 'unexpected_http_response_code', sprintf(
                /* translators: 1: The HTTP response code. */
                __( 'Unexpected HTTP response code: %s', 'wp-cron' ),
                intval( wp_remote_retrieve_response_code( $result ) )
            ) );
        } else {
            set_transient( 'wp-cron-test-ok', 1, 3600 );
            return true;
        }

    }
    /**
     * Shows the status of cron functionality on the site. Only displays a message when there's a problem.
     */
    public function show_cron_status() {

        $status = $this->test_cron_spawn();

        if ( is_wp_error( $status ) ) {
            if ( 'cron_info' === $status->get_error_code() ) {
                ?>
                <div id="cron-status-notice" class="notice notice-info">
                    <p><?php echo esc_html( $status->get_error_message() ); ?></p>
                </div>
                <?php
            } else {
                ?>
                <div id="cron-status-error" class="error">
                    <p>
                        <?php
                        printf(
                            /* translators: 1: Error message text. */
                            esc_html__( 'There was a problem with your cron configuration. The cron events on your site may not work. The problem was: %s', 'wp-cron' ),
                            '<br><strong>' . esc_html( $status->get_error_message() ) . '</strong>'
                        );
                        ?>
                    </p>
                </div>
                <?php
            }
        }
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {

        register_setting(
            'pcr_ext_option_group', // Option group
            'pcr_feed_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'PCRecruiter Feed Settings (Optional)', // Title
            array( $this, 'print_section_info' ), // Callback
            'pcr-ext-setting-admin' // Page
        );

        add_settings_field(
            'activation',
            'Job Feed Enabled',
            array( $this, 'activation_callback' ),
            'pcr-ext-setting-admin',
            'setting_section_id'
        );

        add_settings_field(
            'frequency',
            'Frequency of Update',
            array( $this, 'frequency_callback' ),
            'pcr-ext-setting-admin',
            'setting_section_id'
        );

        add_settings_field(
            'id_number', // ID
            'PCR SessionID', // Title
            array( $this, 'id_number_callback' ), // Callback
            'pcr-ext-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'standard_fields', // ID
            'Standard Fields', // Title
            array( $this, 'standard_fields_callback' ), // Callback
            'pcr-ext-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'custom_fields', // ID
            'Custom Fields', // Title
            array( $this, 'custom_fields_callback' ), // Callback
            'pcr-ext-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'query', // ID
            'Query', // Title
            array( $this, 'query_callback' ), // Callback
            'pcr-ext-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'mode', // ID
            'Mode', // Title
            array( $this, 'mode_callback' ), // Callback
            'pcr-ext-setting-admin', // Page
            'setting_section_id' // Section
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();


        if( isset( $input['activation'] ) )
            $new_input['activation'] = sanitize_text_field( $input['activation'] );

        if( isset( $input['frequency'] ) )
            $new_input['frequency'] = sanitize_text_field( $input['frequency'] );

        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = $input['id_number'];

        if( isset( $input['standard_fields'] ) )
            $new_input['standard_fields'] = sanitize_text_field( $input['standard_fields'] );

        if( isset( $input['custom_fields'] ) )
            $new_input['custom_fields'] = sanitize_text_field( $input['custom_fields'] );

        if( isset( $input['query'] ) )
            $new_input['query'] = $input['query'];

        if( isset( $input['mode'] ) )
            $new_input['mode'] = $input['mode'];

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        echo '<p>When enabled, this feature will duplicate PCRecruiter\'s dynamic RSS feed as a static file at <a target="_blank" href="'. site_url() .'/wp-content/uploads/pcrjobfeed.xml">'. site_url() .'/wp-content/uploads/pcrjobfeed.xml</a>. You may use this data as a source for plugins and other third-party feed utilities.</p><p><strong>The settings in this panel are NOT required for standard PCRecruiter Job Board embedding functions.</strong> Checking the "Job Feed Enabled" box below without proper values in the rest of this form may introduce errors into your website. Please <a target="_blank" href="https://help.pcrecruiter.com">contact PCRecruiter Support</a> for guidance if you wish to enable this feature.</p>';

         // Check to see if "Store Local Feed" is active. If it is, show the manual save button
                if($this->options['activation'] ?? false){
                    $filename = 'pcrjobfeed.xml';
                    $fname = WP_CONTENT_DIR . "/uploads/".$filename;
                    if (file_exists($fname)) {
                        $d = date ("F d Y H:i:s", filectime($fname));
                        echo "<em style=\"font-weight:bold\">" . $filename . " last updated: " . $d . " (UTC).</em>";
                    } else {
                        echo "<i>File " .$fname . " doesn't exist...</i>";
                    }
                }
    }

    /**
     * Get the settings option array and print one of its values
     */

    public function activation_callback()
    {
        printf(
            '<input id="activation" name="pcr_feed_options[activation]" type="checkbox" %2$s />',
            'activation',
            checked( isset( $this->options['activation'] ), true, false )
        );
    }

    public function frequency_callback()
    {
        $options = $this->options['frequency'];
        $items = array("daily", "hourly", "twicedaily");
        echo "<select id='frequency' name='pcr_feed_options[frequency]'>";
        foreach($items as $item) {
            $selected = ($this->options['frequency']==$item) ? 'selected="selected"' : '';
            echo "<option value='$item' $selected>$item</option>";
        }
        echo "</select>";
    }

    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="pcr_feed_options[id_number]" value="%s" size="60" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }

    public function standard_fields_callback()
    {
        printf(
            '<input type="text" id="standard_fields" name="pcr_feed_options[standard_fields]" value="%s" size="60" /><br /><span style="font-size:.8em;">Comma separated.</span>',
            isset( $this->options['standard_fields'] ) ? esc_attr( $this->options['standard_fields']) : ''
        );
    }

    public function custom_fields_callback()
    {

        printf(
            '<input type="text" id="custom_fields" name="pcr_feed_options[custom_fields]" value="%s" size="60" /><br /><span style="font-size:.8em;">Comma separated.</span>',
            isset( $this->options['custom_fields'] ) ? esc_attr( $this->options['custom_fields']) : ''
        );
    }

    public function query_callback()
    {
        printf(
            '<input type="text" id="query" name="pcr_feed_options[query]" value="%s" rows="4" size="60" />',
            isset( $this->options['query'] ) ? esc_attr( $this->options['query']) : ''
        );
    }

    public function mode_callback()
    {
        if($this->options['mode'] == "job"){
            $check1 = "checked";
            $check2 = "";
        } else if($this->options['mode'] == "apply"){
            $check2 = "checked";
            $check1 = "";
        } else {
            $check1 = "checked";
            $check2 = "";
        }
        printf('<input type="radio" id="job" name="pcr_feed_options[mode]" value="job" %s /> Job Link<br />', $check1);
        printf('<input type="radio" id="apply" name="pcr_feed_options[mode]" value="apply" %s /> Apply Link<br />', $check2);
    }
}



/* Begin update cron schedule if frequency changes */
function update_cron() {
     register_setting('pcr_ext_option_group', 'pcr_feed_options');
}
add_action('admin_init', 'update_cron');

function do_after_update($old, $new) {
    // Do the stuff here
    $pcr_feed_options = get_option('pcr_feed_options', array());
    $current_frequency = $pcr_feed_options['frequency'];
    $activated = $pcr_feed_options['activation'] ?? '';
    wp_clear_scheduled_hook('pcr_feed');
    if($activated){
        wp_schedule_event( time(), $current_frequency, 'pcr_feed' );
        pcr_feed_func();
    } else {
		$feedlinkxml = WP_CONTENT_DIR . "/uploads/pcrjobfeed.xml";
		$feedlinkjson = WP_CONTENT_DIR . "/uploads/pcrjobfeed.json";
		if (file_exists($feedlinkxml)) {
        unlink(WP_CONTENT_DIR . "/uploads/pcrjobfeed.xml");
		} else {}
		if (file_exists($feedlinkjson)) {
        unlink(WP_CONTENT_DIR . "/uploads/pcrjobfeed.json");
		} else {}
    }
}
add_action('update_option_pcr_feed_options','do_after_update', 10, 2);
/* End update cron schedule if frequency changes */


if( is_admin() )
    $my_settings_page = new PcrSettingsPage();

// JS for Admin Panel
function pcr_settings_scripts(){
    wp_enqueue_media();
    wp_register_script('pcr-settings-scripts',plugin_dir_url( __DIR__ ) .'pcrecruiter-extensions/assets/js/pcr-admin.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('pcr-settings-scripts');
}
add_action( 'admin_enqueue_scripts', 'pcr_settings_scripts' );