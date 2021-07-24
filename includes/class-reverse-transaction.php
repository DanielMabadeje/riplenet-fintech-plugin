<?php

class ReverseTransaction

{

    public function __construct()
    {
        add_shortcode('rimplenet_all_transactions', array($this, 'getTransactions'));
        add_shortcode('rimplenet_search_transactions', array($this, 'searchTransaction'));
        // add_shortcode('rimplenet_revert_transaction', array($this, 'reverseTransactions'));
    }

    public function getTransactions()
    {
        ob_start();

        include plugin_dir_path(__FILE__) . 'layouts/reverse-transaction.php';

        $output = ob_get_clean();

        return $output;
    }

    public function reverseTransactions($data)
    {

        if ($this->checkIfAlreadyReversed($data['post_id'])) {
            // return false;
            $data = [
                'status' => false,
                'message' => "Transaction Already Reversed!!"
            ];
            return $data;
        } else {
            $rimplewallet = new Rimplenet_Wallets();
            if ($rimplewallet->add_user_mature_funds_to_wallet($data['user_id'], $data['amount_to_add'], $data['wallet_id'], $data['note'], $tags = [])) {
                $this->addAlreadyReversedMeta($data['post_id']);

				
                $data = [
                    'status' => true,
                    'message' => "Transaction Reversed!!"
                ];
				return $data;
            } else {
                $data = [
                    'status' => false,
                    'message' => "Something went wrong!!"
                ];
				
				return $data;
            }
        }
    }

    public function searchTransaction()
    {
        ob_start();

        include plugin_dir_path(__FILE__) . 'layouts/search-transaction.php';

        $output = ob_get_clean();

        return $output;
    }

    private function checkIfAlreadyReversed($post_id)
    {
        $data = get_metadata('post', $post_id, "already_reversed", "true");

		
        if (empty($data) || $data == "" || $data == null || $data == false) {
            return false;
        } else {
            return true;
        }
    }

    private function addAlreadyReversedMeta($post_id)
    {
        if (add_post_meta($post_id, "already_reversed", "true")) {
            return true;
        } else {
            return false;
        }
    }
	
	public function getWallets($include_only = '')
{ //$exclude can be default, woocommerce, or db
    if (empty($include_only)) {
        $include_only = array('default', 'woocommerce', 'db');
    }

    $activated_wallets = array();
    $wallet_type = array('mature', 'immature');


    if (in_array('default', $include_only)) {

        $activated_wallets['rimplenetcoin'] = array(
            "id" => "rimplenetcoin",
            "name" => "RIMPLENET Coin",
            "symbol" => "RMPNCOIN",
            "symbol_position" => "right",
            "value_1_to_base_cur" => 0.01,
            "value_1_to_usd" => 1,
            "value_1_to_btc" => 0.01,
            "decimal" => 0,
            "min_wdr_amount" => 0,
            "max_wdr_amount" => INF,
            "include_in_withdrawal_form" => "yes",
            "include_in_woocommerce_currency_list" => "no",
            "action" => array(
                "deposit" => "yes",
                "withdraw" => "yes",
            )
        );
    }

    if (in_array('woocommerce', $include_only) and in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        //For Woocommerce
        $activated_wallets['woocommerce_base_cur']  = apply_filters('rimplenet_filter_woocommerce_base_cur', get_option('rimplenet_woocommerce_wallet_and_currency'));
    }

    /*
     $activated_wallets['btc'] = array( 
          "id" => "btc",  
          "name" => "Bitcoin", 
          "symbol" => "BTC", 
          "value_1_to_base_cur" => 0.01, 
          "value_1_to_usd" => 0.01, 
          "value_1_to_btc" => 0.01, 
          "decimal" => 8, 
          "include_in_woocommerce_currency_list" => 'no',
          "action" => array( 
              "deposit" => "yes",  
              "withdraw" => "yes", 
          ) 
      ); 
      
      */



    if (in_array('db', $include_only)) {
        //Add Wallets saved in database
        $WALLET_CAT_NAME = 'RIMPLENET WALLETS';
        $txn_loop = new WP_Query(
            array(
                'post_type' => 'rimplenettransaction',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'rimplenettransaction_type',
                        'field'    => 'name',
                        'terms'    => $WALLET_CAT_NAME,
                    ),
                ),
            )
        );
        if ($txn_loop->have_posts()) {
            while ($txn_loop->have_posts()) {
                $txn_loop->the_post();
                $txn_id = get_the_ID();
                $status = get_post_status();
                $wallet_name = get_the_title();
                $wallet_desc  = get_the_content();

                $wallet_decimal = get_post_meta($txn_id, 'rimplenet_wallet_decimal', true);
                $min_wdr_amount = get_post_meta($txn_id, 'rimplenet_min_withdrawal_amount', true);
                if (empty($min_wdr_amount)) {
                    $min_wdr_amount = 0;
                }

                $max_wdr_amount = get_post_meta($txn_id, 'rimplenet_max_withdrawal_amount', true);
                if (empty($max_wdr_amount)) {
                    $max_wdr_amount = INF;
                }

                $wallet_symbol = get_post_meta($txn_id, 'rimplenet_wallet_symbol', true);
                $wallet_symbol_position = get_post_meta($txn_id, 'rimplenet_wallet_symbol_position', true);
                $wallet_id = get_post_meta($txn_id, 'rimplenet_wallet_id', true);
                $include_in_withdrawal_form = get_post_meta($txn_id, 'include_in_withdrawal_form', true);
                $include_in_woocommerce_currency_list = get_post_meta($txn_id, 'include_in_woocommerce_currency_list', true);

                $activated_wallets[$wallet_id] = array(
                    "id" => $wallet_id,
                    "name" => $wallet_name,
                    "symbol" => $wallet_symbol,
                    "symbol_position" => $wallet_symbol_position,
                    "value_1_to_base_cur" => 0.01,
                    "value_1_to_usd" => 1,
                    "value_1_to_btc" => 0.01,
                    "decimal" => $wallet_decimal,
                    "min_wdr_amount" => $min_wdr_amount,
                    "max_wdr_amount" => $max_wdr_amount,
                    "include_in_withdrawal_form" => "yes",
                    "include_in_woocommerce_currency_list" => $include_in_woocommerce_currency_list,
                    "action" => array(
                        "deposit" => "yes",
                        "withdraw" => "yes",

                    )
                );
            }
        }

        wp_reset_postdata();
    }


    return $activated_wallets;
}

}
