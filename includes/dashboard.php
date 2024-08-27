<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
include_once(plugin_dir_path(__FILE__) . 'common.php');

function quinn_handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('quinn_dashboard_form', 'quinn_dashboard_nonce')) {
        $domain_name = sanitize_text_field($_POST['domain_name']);
        $user_email = sanitize_email($_POST['user_email']);
        $password = sanitize_text_field($_POST['password']);
        $domain_name = preg_replace('/^(www\.|https?:\/\/)/', '', $domain_name);
        // Define the GraphQL mutation query
        $graphql_mutation = '
        mutation CreateSessionMutation($session: SessionInput!) {
            createSession(session: $session) {
                session {
                    id
                }
            }
        }';

        // Prepare the session data
        $session_data = array(
            'sessionid'   => 'offline_'.$domain_name . '_shoppable-videos',
            'shoptype'    => 'WOOCOMMERCE',
            'shopname'    => $domain_name,
            'accesstoken' => '',
            'appname'     => 'shoppable-videos',
            'state'       => '',
            'scope'       => '',
            "createFirebaseAccount" => true,
            "email" => $user_email,
            "password" => $password
        );

        // Prepare the GraphQL request payload
        $graphql_payload = wp_json_encode(array(
            'query' => $graphql_mutation,
            'variables' => array(
                'session' => $session_data
            )
        ));

        // Make the GraphQL request
        $response = wp_remote_post('https://ecomapi.quinn.live/backend/graphql', array(
            'method'    => 'POST',
            'headers'   => array(
                'Content-Type' => 'application/json',
                "x-quinn-admin-secret" => ""
            ),
            'body'      => $graphql_payload,
        ));
        // Handle the response
        if (is_wp_error($response)) {
            echo '<div class="error"><p>Error: ' .esc_html($response->get_error_message()) . '</p></div>';
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['errors'])) {
                echo '<div class="error"><p>Error: ' . esc_html($data['errors'][0]['message']) . '</p></div>';
            } else {
                echo '<div class="updated"><p>Settings saved and session created successfully</p></div>';
            }
        }
    }
}


function quinn_render_dashboard_page() {
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('quinn_dashboard_form', 'quinn_dashboard_nonce')) {
        quinn_handle_form_submission();
    }
    $site_url = get_site_url(); // Get the site URL
    // Fetch user details
    $user_details = quinn_fetch_user_details();
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $domain_name = quinn_get_domain_name();

    // HTML and CSS styling
    ?>
    <div class="wrap" style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
        <div style="max-width: 800px; width: 100%; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px;">
            <?php if (isset($user_details) && !empty($user_details)): ?>
                <h1  style="text-align: center;">Welcome to Quinn Dashboard</h1>
                <p style="text-align: center;">Please visit the <a href="https://newadmin.quinn.live" target="_blank">Quinn Admin Portal</a> and use the email and password you created to log in.</p>
                <p style="text-align: center;">
                    If you have any questions or need any assitance please contact us at <a href="mailto:" target="_blank">
                    admin@quinn.live</a>, We are happy to help.
                </p>

            <?php else: ?>
                <p style="text-align: center;">Please complete the setup to use the Quinn Dashboard.</p>
                <p style="text-align: center;">Once you have created the account, please use that account to login to  <a>Quinn admin portal</a></p>
                <form method="post" action="">
                    <?php wp_nonce_field('quinn_dashboard_form', 'quinn_dashboard_nonce'); ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="domain_name">Store Url:</label></th>
                            <td><input readonly="readonly"  type="text" value="<?php echo esc_attr($domain_name); ?>" id="domain_name" name="domain_name" value="" class="regular-text" required></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="user_email">User Email</label></th>
                            <td><input readonly="readonly"   type="email" id="user_email" value="<?php echo esc_attr($current_user_email); ?>" name="user_email" value="" class="regular-text" required></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="password">Password</label></th>
                            <td><input type="password" id="password" name="password" value="" class="regular-text" required></td>
                        </tr>
                    </table>
                    <p class="submit" style="text-align: center;"><input type="submit" class="button-primary" value="Create Account"></p>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function quinn_fetch_user_details() {
    $site_url = get_site_url(); // Fetch the site URL
    $domain_name = preg_replace('/^(www\.|https?:\/\/)/', '', $site_url);
    $session_id = 'offline_' . $domain_name . '_shoppable-videos'; // Modify the site URL
    $graphql_query = '{
        session(sessionid: "' . esc_attr($session_id) . '") {
            sessionid
            shopname
        }
    }';

    $response = wp_remote_post('https://ecomapi.quinn.live/backend/graphql', array(
        'method'    => 'POST',
        'headers'   => array(
            'Content-Type' => 'application/json',
            "x-quinn-admin-secret" => ""
        ),
        'body'      => wp_json_encode(array('query' => $graphql_query)),
    ));

    if (is_wp_error($response)) {
        return array('error' => $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data['errors'])) {
        return array('error' => $data['errors'][0]['message']);
    }
    return $data['data']['session'] ?? array();
}
