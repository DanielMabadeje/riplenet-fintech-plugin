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

if ($_SERVER['REQUEST_METHOD'] == "POST" && wp_verify_nonce($_POST['rimplenet-reverse-transaction-form'], 'rimplenet-reverse-transaction-form')) {
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
} 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refunds</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <section class="bg-light">
        <section class="container pt-5 pb-5 mt-5">
            <div class="card col-md-12">
                <div class="card-body">
                    <div class="card-title mb-0">Transactions</div>
                    <div class="table-responsive">

                        <?php if (!empty($pageno) or $_GET['pageno'] > 1) {
                            $pageno = sanitize_text_field($_GET['pageno']);
                        } else {
                            $pageno = 1;
                        }

                        $txn_loop = new WP_Query(
                            array(
                                'post_type' => 'rimplenettransaction',
                                'post_status' => 'any',
                                //                                 'author' => $user_id,
                                'author' => 'any',
                                'posts_per_page' => $posts_per_page,
                                'paged' => $pageno,
                                'tax_query' => array(
                                    'relation' => 'OR',
                                    array(
                                        'taxonomy' => 'rimplenettransaction_type',
                                        'field'    => 'name',
                                        'terms'    => array('DEBIT'),
                                    ),
                                ),
                            )
                        );


                        if ($txn_loop->have_posts()) {
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
                                    <?php
                                    while ($txn_loop->have_posts()) {
                                        $txn_loop->the_post();
                                        $txn_id = get_the_ID();
                                        $status = get_post_status();

                                        // 										var_dump($txn_loop->the_post());
                                        $date_time = get_the_date('D, M j, Y', $txn_id) . '<br>' . get_the_date('g:i A', $txn_id);
                                        $wallet_id = get_post_meta($txn_id, 'currency', true);

                                        $author_id = get_post_field('post_author', $txn_id);

                                        $all_rimplenet_wallets = getWallets();


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
                                        //$txn_view_url = add_query_arg( array( 'txn_id'=>$txn_id,'view_txn_nonce'=>$view_txn_nonce), home_url(add_query_arg(array(),$wp->request)) );

                                    ?>

                                        <tr>
                                            <th scope="row"> #<?= $txn_id ?></th>
                                            <td> <?= $date_time ?></td>
                                            <td> <?= $amount_formatted_disp; ?> </td>
                                            <td> <?= $txn_type; ?> </td>
                                            <td class="td-note"><?= $note; ?></td>
                                            <td>
                                                <!-- <?php do_action('rimplenet_wallet_history_txn_action', $txn_id, $wallet_id, $amount, $txn_type, $note); ?> -->
                                                <form method="POST">
                                                    <input type="hidden" name="post_id" value="<?= $txn_id ?>">
                                                    <input type="hidden" name="walletId" value="">
                                                    <?php wp_nonce_field('rimplenet-reverse-transaction-form', 'rimplenet-reverse-transaction-form'); ?>
                                                    <div class="clearfix"></div>
                                                    <input type="submit" value="Revert" class="btn btn-danger bg-danger">
                                                </form>
                                                <!--                                                 <a href="" class="btn btn-danger">Revert</a> -->
                                            </td>
                                        </tr>


                                    <?php } ?>
                                </tbody>
                            </table>

                        <?php } else {
                            echo "<center>No Transaction found for this request</center>";
                        }

                        rimplenet_pagination_bar($txn_loop, $pageno);
                        wp_reset_postdata(); ?>
                    </div>
                </div>
            </div>
        </section>
    </section>
</body>

</html>