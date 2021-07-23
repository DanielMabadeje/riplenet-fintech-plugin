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
            } else {
                $data = [
                    'status' => false,
                    'message' => "Something went wrong!!"
                ];
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
    }

    private function addAlreadyReversedMeta($post_id)
    {
        if (add_post_meta($post_id, "already_reversed", "true")) {
            return true;
        } else {
            return false;
        }
    }
}
