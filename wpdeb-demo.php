<?php
/*
Plugin Name: WPDB Demo
Plugin URI:
Description: Demonstration of WPDB Methods
Version: 1.0.0
Author: LWHH
Author URI:
License: GPLv2 or later
Text Domain: wpdb-demo
 */
array(
    'r'     => 'hhh',
    'kkkkk' => 'kkk',
);

function wpdbdemo_init() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    $sql = "CREATE TABLE {$table_name} (
			id INT NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(250),
			email VARCHAR(250),
            age INT,
			PRIMARY KEY (id)
	);";
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta( $sql );
}

register_activation_hook( __FILE__, "wpdbdemo_init" );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( 'toplevel_page_wpdb-demo' == $hook ) {
        wp_enqueue_style( 'pure-grid-css', '//unpkg.com/purecss@1.0.1/build/grids-min.css' );
        wp_enqueue_style( 'wpdb-demo-css', plugin_dir_url( __FILE__ ) . "assets/css/style.css", null, time() );
        wp_enqueue_script( 'wpdb-demo-js', plugin_dir_url( __FILE__ ) . "assets/js/main.js", array( 'jquery' ), time(), true );
        $nonce = wp_create_nonce( 'display_result' );
        wp_localize_script(
            'wpdb-demo-js',
            'plugindata',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => $nonce )
        );
    }
} );

function wpdemo_ajax_display_result() {
    if ( wp_verify_nonce( $_POST['nonce'], $_POST['action'] ) ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'persons';
        $task = $_POST['task'];
        if ( 'add-new-record' == $task ) {
            $person = array(
                'name'  => 'Jakir Hossain',
                'email' => 'jakir@gmail.com',
                'age'   => 42,
            );
            $wpdb->insert( $table_name, $person, array( '%s', '%s', '%d' ) );
            echo "New Record Added </br>";
            echo "ID:" . $wpdb->insert_id;

        } elseif ( 'replace-or-insert' == $task ) {
            $person = array(
                'id'    => 25,
                'name'  => 'Hasnath',
                'email' => 'hasnath@gmail.com',
                'age'   => 42,
            );
            $wpdb->replace( $table_name, $person, array( '%d', '%s', '%s', '%d' ) );
            echo "Operation Done</br>";
            echo "ID:" . $wpdb->insert_id;

        } elseif ( 'update-data' == $task ) {
            $person = array(
                'id'    => 25,
                'name'  => 'Hasnath Mia',
                'email' => 'hasnath@gmail.com',
                'age'   => 42,
            );
            $result = $wpdb->update( $table_name, $person, array( 'id' => 25 ), array( '%s' ) );
            echo "Udate Done. Result:{$result}</br>";

        } elseif ( 'load-single-row' == $task ) {

            $result = $wpdb->get_row( "SELECT * FROM {$table_name} WHERE id = 25" );
            $result1 = $wpdb->get_row( "SELECT * FROM {$table_name} WHERE id = 25", ARRAY_A );
            $result2 = $wpdb->get_row( "SELECT * FROM {$table_name} WHERE id = 25", ARRAY_N );
            print_r( $result );
            print_r( $result1 );
            print_r( $result2 );

        } elseif ( 'load-multiple-row' == $task ) {

            $result = $wpdb->get_results( "SELECT * FROM {$table_name} " );
            $result1 = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );
            $result2 = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_N );
            $result3 = $wpdb->get_results( "SELECT name, id, email, age FROM {$table_name}", OBJECT_K );
            print_r( $result );
            // print_r($result1);
            // print_r($result2);
            // print_r($result3);

        } elseif ( 'add-multiple' == $task ) {

            $persons = array(
                array(
                    'name'  => 'hanif',
                    'email' => 'hanif@gmail.com',
                    'age'   => 42,
                ),
                array(
                    'name'  => 'Hasan',
                    'email' => 'hasna@gmail.com',
                    'age'   => 42,
                ),
            );
            foreach ( $persons as $person ) {
                $wpdb->insert( $table_name, $person, array( '%s', '%s', '%d' ) );
            }
            $result = $wpdb->get_results( "SELECT * FROM {$table_name} " );
            print_r( $result );

        } elseif ( 'prepared-statement' == $task ) {
            //  $id = 3;
            $email = 'b@gmail.com';
            //  $query1 = $wpdb->prepare("SELECT * FROM {$table_name} WHERE id < %d",$id);
            $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE email = %s", $email );
            $result = $wpdb->get_results( $query, ARRAY_A );
            print_r( $result );

        } elseif ( 'single-column' == $task ) {
            $query = $wpdb->prepare( "SELECT email FROM {$table_name}" );
            $result = $wpdb->get_col( $query );
            print_r( $result );

        } elseif ( 'single-var' == $task ) {
         //  $query = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
          //  echo "Total Users {$query}<br>";
          // $wpdb->get_var( 'query', column_offset, row_offset );     // colum <=> User
            $query = $wpdb->get_var( "SELECT name, email FROM {$table_name}", 0 , 0 ); // 1st user Name
            $query = $wpdb->get_var( "SELECT name, email FROM {$table_name}", 0 , 1 ); // 2nd User Name
            $query = $wpdb->get_var( "SELECT name, email FROM {$table_name}", 0 , 2 ); // 3rd User Name

            $query = $wpdb->get_var( "SELECT name, email FROM {$table_name}", 1 , 0 ); // 1st User Email

            $query = $wpdb->get_var( "SELECT name, state FROM {$table_name}", 1 , 1 ); // 2sd User state
            $query = $wpdb->get_var( "SELECT name, state FROM {$table_name}", 1 , 2 ); // 3rd User state
            // Here select item name = 0, country = 1, state= 2, age= 3 (serialize)
            // Then last pram (Col <=> row ) item index <=> row index 
            $query = $wpdb->get_var( "SELECT name, state , country, age FROM {$table_name}", 2 , 1 ); // 3rd User state
            
            echo $query;                      
        

        
        } elseif ( 'delete-data' == $task ) {
    
            $result = $wpdb->delete($table_name,['id'=>30]); // 1st user Name
            echo 'Delete Result = '.$result;                      
         //   print_r( $query );

        }

    }
    die( 0 );
}
add_action( 'wp_ajax_display_result', 'wpdemo_ajax_display_result' );

add_action( 'admin_menu', function () {
    add_menu_page( 'WPDB Demo', 'WPDB Demo', 'manage_options', 'wpdb-demo', 'wpdbdemo_admin_page' );
} );

function wpdbdemo_admin_page() {
    ?>
        <div class="container" style="padding-top:20px;">
            <h1>WPDB Demo</h1>
            <div class="pure-g">
                <div class="pure-u-1-4" style='height:100vh;'>
                    <div class="plugin-side-options">
                        <button class="action-button" data-task='add-new-record'>Add New Data</button>
                        <button class="action-button" data-task='replace-or-insert'>Replace or Insert</button>
                        <button class="action-button" data-task='update-data'>Update Data</button>
                        <button class="action-button" data-task='load-single-row'>Load Single Row</button>
                        <button class="action-button" data-task='load-multiple-row'>Load Multiple Row</button>
                        <button class="action-button" data-task='add-multiple'>Add Multiple Row</button>
                        <button class="action-button" data-task='prepared-statement'>Prepared Statement</button>
                        <button class="action-button" data-task='single-column'>Display Single Column</button>
                        <button class="action-button" data-task='single-var'>Display Variable</button>
                        <button class="action-button" data-task='delete-data'>Delete Data</button>
                    </div>
                </div>
                <div class="pure-u-3-4">
                    <div class="plugin-demo-content">
                        <h3 class="plugin-result-title">Result</h3>
                        <div id="plugin-demo-result" class="plugin-result"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
