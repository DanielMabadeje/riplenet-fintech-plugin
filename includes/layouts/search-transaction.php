<?php

global $current_user;
wp_get_current_user();


$user = wp_get_current_user();
if (in_array('editor', (array) $user->roles)) {
    //The user has the "author" role
} elseif (in_array('admin', (array) $user->roles)) {
    # code...
} else {
    echo "<script>alert('You Do not have access here

Contact Admin')</script>";
}

// $all_wallets = $this->getWallets();

$atts = shortcode_atts(array(

    'action' => 'empty',
    'user_id' => $current_user->ID,
    'wallet_id' => 'woocommerce_base_cur',
    'cancel_wdr_button_text' => 'cancel_wdr_button_text',
    'action_header_text' => 'action_header_text',
    'posts_per_page' => get_option('posts_per_page'),
), $atts);


$action = $atts['action'];
$user_id = $atts['user_id'];
$wallet_id = $atts['wallet_id'];
$cancel_wdr_button_text = $atts['cancel_wdr_button_text'];
$action_header_text = $atts['action_header_text '];
$posts_per_page = $atts['posts_per_page'];


$viewed_url = $_SERVER['REQUEST_URI'];

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['revert']) && wp_verify_nonce($_POST['rimplenet-reverse-transaction-form'], 'rimplenet-reverse-transaction-form')) {
    $data = [
        'post_id' => sanitize_text_field(trim($_POST['post_id'])),
        'wallet_id' => "ngn",
        'note' => "Reversal of Transaction #" . sanitize_text_field(trim($_POST['post_id']))
    ];
    $txn_id = $data['post_id'];
    $post_data = get_post($data['post_id']);
    $amount = get_post_meta($txn_id, 'amount', true);
    $txn_type = get_post_meta($txn_id, 'txn_type', true);
    $wallet_id = get_post_meta($txn_id, 'currency', true);
    $data['amount_to_add'] = $amount;
    $data['user_id'] = $post_data->post_author;
    $data['wallet_id'] = $wallet_id;

    // $reverseTransaction = new ReverseTransaction();
    $results = $this->reverseTransactions($data);
    if ($results['status'] == true) {
        echo "<script>alert('Reverted Successfully')</script>";
    } else {
        echo "<script>alert('Revertion Failed')</script>";
?>
        <center>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong> ERROR: </strong> <?= $results['message'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </center>
    <?php
        return;
    }
} else {
    ?>
    <center>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong> ERROR: </strong> Error . Please Retry or contact admin
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </center>
    <?php
    return;
}



if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['search']) && wp_verify_nonce($_POST['rimplenet-reverse-transaction-form'], 'rimplenet-reverse-transaction-form')) {
    $data = [
        'post_id' => sanitize_text_field(trim($_POST['post_id']))
    ];
    $txn_id = $data['post_id'];
    $post_data = get_post($data['post_id']);
    $amount = get_post_meta($txn_id, 'amount', true);
    $txn_type = get_post_meta($txn_id, 'txn_type', true);
    $wallet_id = get_post_meta($txn_id, 'currency', true);
    $date_time = get_the_date('D, M j, Y', $txn_id) . '<br>' . get_the_date('g:i A', $txn_id);
    $wallet_symbol = $all_rimplenet_wallets[$wallet_id]['symbol'];
    $wallet_decimal = $all_rimplenet_wallets[$wallet_id]['decimal'];
    $amount = get_post_meta($txn_id, 'amount', true);
    $txn_type = get_post_meta($txn_id, 'txn_type', true);


    if ($txn_type == "CREDIT") {
        $amount_formatted_disp = '<font color="green">+' . $wallet_symbol . number_format($amount, $wallet_decimal) . '</font>';
    } elseif ($txn_type == "DEBIT") {
        $amount_formatted_disp = '<font color="red">-' . $wallet_symbol . number_format($amount, $wallet_decimal) . '</font>';
    }


    $amount_formatted_disp = apply_filters("rimplenet_history_amount_formatted", $amount_formatted_disp, $txn_id, $txn_type, $amount, $amount_formatted_disp);

    $note = get_post_meta($txn_id, 'note', true);

    $view_txn_nonce = wp_create_nonce('view_txn_nonce');
    // $data['amount_to_add'] = $amount;
    // $data['user_id'] = $post_data->post_author;
    // $data['wallet_id'] = $wallet_id;
    if ($post_data) {
        # code...


    ?>
        <table class="table text-left">
            <thead>
                <tr>
                    <th> ID </th>
                    <th> Date </th>
                    <th> Amount </th>
                    <th> Type </th>
                    <th> Note </th>
                    <th> Action </th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <th scope="row"> #<?= $txn_id ?></th>
                    <td> <?= $date_time ?></td>
                    <td> <?= $amount_formatted_disp; ?> </td>
                    <td> <?= $txn_type; ?> </td>
                    <td class="td-note"><?= $note; ?></td>
                    <td>
                        <!-- <?php do_action('rimplenet_wallet_history_txn_action', $txn_id, $wallet_id, $amount, $txn_type, $note); ?> -->


                        <?php
                        if ($txn_type == "CREDIT") {
                            echo "No Action Required";
                        } elseif ($txn_type == "DEBIT") {
                        ?>
                            <form method="POST">
                                <input type="hidden" name="post_id" value="<?= $txn_id ?>">
                                <input type="hidden" name="walletId" value="">
                                <?php wp_nonce_field('rimplenet-reverse-transaction-form', 'rimplenet-reverse-transaction-form'); ?>
                                <div class="clearfix"></div>
                                <input type="submit" value="Revert" name="revert" class="btn btn-danger bg-danger">
                            </form>

                        <?php
                        }


                        ?>

                        <!--                                                 <a href="" class="btn btn-danger">Revert</a> -->
                    </td>
                </tr>

            </tbody>

        </table>
    <?php
    } else {
    ?>

        <center>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong> ERROR: </strong> No Transaction found
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </center>

    <?php
    }
} else {
    ?>
    <div class="col-md-8 card">
        <div class="form">
            <form action="" method="post">
                <div class="form-input">
                    <input type="text" name="post_id" id="" class="form-control">

                    <?php wp_nonce_field('rimplenet-reverse-transaction-form', 'rimplenet-reverse-transaction-form'); ?>
                    <div class="clearfix"></div>

                    <input type="submit" value="Search" name="search" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
<?php
    return;
}

?>