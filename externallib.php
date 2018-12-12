<?php
/**
 * External Web Service Template
 *
 * @package   local_wallet
 * @author    Ekaterina Staneva <kate.staneva@iris-worldwide.com>
 */

global $CFG, $USER, $DB;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot .'/course/externallib.php');

class local_wallet_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */

    public static function purchase_product_parameters() {
        return new external_function_parameters(
            array(
                'prod_id' => new external_value(PARAM_INT, 'id of the purchased product')
            )
        );
    }

      /**
     * Returns product insetrion status
     * @return int product insert status
     */
    public static function purchase_product($prod_id) {
        global $CFG, $USER, $DB;
        $id = $USER->id;

        $params = self::validate_parameters(self::purchase_product_parameters(),
                array('prod_id' => $prod_id));

        $context = context_user::instance($id);
        self::validate_context($context);

        //if (!has_capability($capability, $context)) {

            require_once($CFG->dirroot . '/local/wallet/lib.php');

            //TODO create a new course with modules
            $price = get_prod_price($params['prod_id']);

            $get_balance = $DB->get_record_sql('SELECT balance FROM {local_wallet} WHERE user_id = ?', array($id));
            if($get_balance === false){
                //TODO - No wallet!
                //TODO - $balance = get all users credits
                //TODO - $db_res = add_new_wallet($id, $balance);
            }else{
                $balance = $get_balance->balance;
                if($balance > $price){

                    $new_balance = $balance - $price;
                    $update_result = $DB->execute('UPDATE {local_wallet}
                                            SET balance=?
                                            WHERE user_id=?',
                                        array($new_balance, $id));

                    if($update_result !== false) {
                        $transation_id = add_transaction($id, true, $price);
                        if($transation_id !== false){
                            if(add_prod($id, $transation_id, $params['prod_id']) !== false){
                               return array('purchase_status' => true);
                            }
                        }
                    }
                }
            }

            return array('purchase_status' => false);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function purchase_product_returns() {
        return new external_function_parameters(
            array(
                'purchase_status' => new external_value(PARAM_BOOL, 'is successful', PARAM_REQUIRED)
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */

    public static function get_wallet_balance_parameters() {
        return new external_function_parameters(
            array(
                //no params
            )
        );
    }

    /**
     * Returns amount of credits in users wallet
     * @return string welcome message
    */
    public static function get_wallet_balance() {
        global $CFG, $USER, $DB;
        $id = $USER->id;

        $context = context_user::instance($id);
        self::validate_context($context);

        $balance = $DB->get_record_sql('SELECT balance FROM {local_wallet} WHERE user_id = ?', array($id));
        if($balance !== false && (current($balance) !== null) ){
            return array('balance' => current($balance));
        }else{
            return array('balance' => null);
        }
    }

     /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_wallet_balance_returns() {
        return new external_function_parameters(
            array(
                'balance' => new external_value(PARAM_INT, 'Amount of credits in users wallet', PARAM_REQUIRED),
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */

    public static function get_user_transactions_parameters() {
        global  $USER;
        return new external_function_parameters(
            array(
                'user_id' => new external_value(PARAM_INT, 'get transactions of specific user.
                    If this field is empty the transactions for the current user will be returned', VALUE_DEFAULT, $USER->id),
                'is_withdraw' => new external_value(PARAM_BOOL,
                    'Determins if the transaction was deposit or withdraw. If emprty all matching records will be returned.',
                    PARAM_OPTIONAL),
                'page' => new external_value(PARAM_INT, 'Current page.  If emprty all matching records will be returned.',
                    PARAM_OPTIONAL),
                'limit' => new external_value(PARAM_INT, 'Page limit. If emprty all matching records will be returned.',
                    PARAM_OPTIONAL),
                'orderASC' => new external_value(PARAM_BOOL, 'Specifies type of ordering. If true -
                    results will be ordered ASC, if false - results will be ordered DESC.
                     If empty no explicit ordering will be applied. ',
                    PARAM_OPTIONAL)
            )
        );
    }

     /**
     * Returns users transaction history
     *
     * @param int $user_id - id of the user whos history we will retreive.
     * @param string $criteriavalue Criteria value
     * @param int $page             Page number (for pagination)
     * @param int $limit          Items per page
     * @return array of transaction records
     */
    public static function get_user_transactions($user_id = NULL, $is_withdraw = NULL, $page = NULL, $limit = NULL, $orderASC = NULL) {
        global $CFG, $USER, $DB;
        $id = $USER->id;

        require_once($CFG->dirroot . '/local/wallet/lib.php');
        $params = self::validate_parameters(self::get_user_transactions_parameters(),
            array('user_id' => $user_id, 'is_withdraw' => $is_withdraw ,
                    'page' => $page, 'limit' =>  $limit, 'orderASC' => $orderASC));

        $sql =  "SELECT
                id, is_withdraw, amount, createdat
                FROM {local_wallet_transactions} WHERE";
        $options = array('user_id' => $id);

        if ($params['is_withdraw'] !== NULL) {
            $sql .=  " is_withdraw = :is_withdraw AND ";
            $options['is_withdraw'] = $params['is_withdraw'];
        }

        if ($params['page'] !== NULL && $params['limit'] !== NULL) {
            $offset = $params['page'] * $params['limit'];
            $end = $offset + $params['limit'];
            $options['start'] = $offset;
            $options['end'] = $end;

            $sql .=  " id BETWEEN :start AND :end AND ";
        }

        $sql .=  " user_id = :user_id ";

        if ($params['orderASC'] !== NULL) {
            if ($params['orderASC'] === true) {
                $sql .=  " ORDER BY createdat ASC ";
            }else{
                $sql .=  " ORDER BY createdat DESC ";
            }
        }

        $result = $DB->get_records_sql($sql, $options);
        if($result !== false){
            return array('transactions' => $result);
        }

        return false;
    }

     /**
     * Returns description of method result value
     * @return external_function_parameters
     */
    public static function get_user_transactions_returns() {
        return new external_function_parameters(
            array(
                'transactions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, PARAM_REQUIRED),
                            'is_withdraw' => new external_value(PARAM_INT, PARAM_REQUIRED),
                            'amount' => new external_value(PARAM_INT, PARAM_REQUIRED),
                            'createdat' => new external_value(PARAM_TEXT, PARAM_REQUIRED),
                        )
                    )
                )
            )
        );
    }


        /**
     * Returns description of method parameters
     * @return external_function_parameters
     */

    public static function get_purchase_history_parameters() {
        global  $USER;
        return new external_function_parameters(
            array(
                'user_id' => new external_value(PARAM_INT, 'get transhistoryactions of specific user.
                    If this field is empty the history for the current user will be returned', VALUE_DEFAULT, $USER->id),
                'page' => new external_value(PARAM_INT, 'Current page.  If emprty all matching records will be returned.',
                 PARAM_OPTIONAL),
                'limit' => new external_value(PARAM_INT, 'Page limit. If emprty all matching records will be returned.',
                PARAM_OPTIONAL),
                'orderASC' => new external_value(PARAM_BOOL, 'Specifies type of ordering. If true -
                results will be ordered ASC, if false - results will be ordered DESC.
                 If empty no explicit ordering will be applied. ',
                PARAM_OPTIONAL)
            )
        );
    }

     /**
     * Returns users transaction history
     *
     * @param int $user_id - id of the user whos history we will retreive.
     * @param string $criteriavalue Criteria value
     * @param int $page             Page number (for pagination)
     * @param int $limit          Items per page
     * @return array of transaction records
     */
    public static function get_purchase_history($user_id = NULL, $page = null, $limit = null, $orderASC = NULL) {
        global $CFG, $USER, $DB;
        $id = $USER->id;

        $params = self::validate_parameters(self::get_user_transactions_parameters(),
            array('user_id' => $user_id, 'page' => $page, 'limit' =>  $limit, 'orderASC' => $orderASC));

        $sql = "
        SELECT
            p.prod_id  as product_id,
            s.name as name,
            s.summary as summary,
            t.createdat as createdat
                    FROM public.mdl_local_wallet_product as p
                    INNER JOIN public.mdl_local_wallet_transactions as t
                    on p.transaction_id = t.id

                    INNER JOIN public.mdl_course_sections as s
                    on p.prod_id = s.id

                    WHERE 	 t.user_id = :user_id
                    AND s.id = p.prod_id ";

        $options = array('user_id' => $id);
        if ($params['page'] !== NULL && $params['limit'] !== NULL) {
            $offset = $params['page'] * $params['limit'];
            $end = $offset + $params['limit'];
            $options['start'] = $offset;
            $options['end'] = $end;

            $sql .=  " AND t.id BETWEEN :start AND :end ";
        }

        if ($params['orderASC'] !== NULL) {
            if ($params['orderASC'] === true) {
                $sql .=  " ORDER BY createdat ASC ";
            }else{
                $sql .=  " ORDER BY createdat DESC ";
            }
        }

        $result = $DB->get_records_sql($sql, $options);
        var_dump($result);
        if($result !== false){
            return array('history' => $result);
        }

        return false;
    }

     /**
     * Returns description of method result value
     * @return external_function_parameters
     */
    public static function get_purchase_history_returns() {
        return new external_function_parameters(
            array(
                'history' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'product_id' => new external_value(PARAM_INT, PARAM_REQUIRED),
                            'user_id' => new external_value(PARAM_INT, PARAM_REQUIRED),
                            'amount' => new external_value(PARAM_INT, PARAM_REQUIRED),
                            'createdat' => new external_value(PARAM_INT, PARAM_REQUIRED),
                        )
                    )
                )
            )
        );
    }
}
