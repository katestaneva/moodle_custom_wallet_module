<?php
/**
 * Plugin library.
 *
 * @package   local_wallet
 * @author    Ekaterina Staneva <kate.staneva@iris-worldwide.com>
 */

function add_transaction($user_id, $is_widthraw, $amount){
    global $DB;
   
    if(isset($user_id) && isset($is_widthraw) && isset($amount) ){
        $record = new stdClass();
        $record->user_id = $user_id;
        //TODO just google it
        $record->is_withdraw = (bool)$is_widthraw;
        $record->amount = $amount;
        $record->createdat = date('Y-m-d H:i:s');
        if($lastinsertid = $DB->insert_record('local_wallet_transactions', $record, true)){
            return $lastinsertid;
        }
    }
    
    return false;
}

function add_prod($user_id, $transaction_id, $product_id){
    global $DB;
    if(isset($transaction_id) && isset($product_id)){
        $record = new stdClass();
        $record->user_id = $user_id;
        $record->transaction_id = $transaction_id;
        $record->prod_id = $product_id;
    
        if($lastinsertid = $DB->insert_record('local_wallet_product', $record, false)) {
            return $lastinsertid;
        }
    }

    return false;
}

function add_new_wallet($user_id, $balance){
    global $DB; 
    if(isset($user_id) && isset($balance)){
  
        $record = new stdClass();
        $record->user_id = $user_id;
        $record->balance = $balance;
    
        if($lastinsertid = $DB->insert_record('local_wallet', $record, false)) {
            return $lastinsertid;
        }
    }

    return false;
}

function update_wallet($user_id, $amount){
    global $DB; 
    if(isset($user_id) && isset($amount)){
        
        $get_balance = $DB->get_record_sql('SELECT balance FROM {local_wallet} WHERE user_id = ?', array($user_id));
        if($get_balance !== false){
            $balance = $get_balance->balance;
            $new_balance = $balance + $amount;

            $update_result = $DB->execute('UPDATE {local_wallet} 
                                            SET balance=? 
                                            WHERE user_id=?', 
                                        array($new_balance, $user_id));
    
                    if($update_result !== false) {
                        $transation_id = add_transaction($user_id, $is_widthraw = false, $amount);
                        if($transation_id !== false){
                            return true;
                        }
                    }
        }  
       
    }

    return false;
}


function get_prod_price($product_id){
    global $DB;
   
    $products = $DB->get_records_sql(
        'SELECT name, summary FROM {course_sections} WHERE id = ?', array($product_id));
    
        if($product->name != ""){
            $content = json_decode($product->summary, true);
            return  $content['price'];
    }
   
    return false;
}


