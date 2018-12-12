<?php

/**
 * Update users credit event observers.
 *
 * @package    local_wallet
 * @author     Ekaterina Staneva
 */

defined('MOODLE_INTERNAL') || die();

class local_wallet_observer {
    /**
     * Event processor - user created
     *
     * @param \local_points_badges\event\points_earned $event
     * @return bool
     */
    public static function update_wallet_handler(\local_points_badges\event\points_earned $event) {
        global $DB, $CFG;
        $message = "";

        $event_data = $event->get_data();

        require_once($CFG->dirroot . '/local/wallet/lib.php');
        if(update_wallet($event_data->user_id, $event_data->credits_amount)){
            $message = 'update_wallet_handler: points_earned event was triggered.
                Users wallet was succesfully updated. Transactions table was succesfully updated.';
        }else{
            $message = 'update_wallet_handler: points_earned event was triggered.
                Users wallet and / or transactions table were not succesfully updated';
        }

		$logfile_dir = $CFG->dataroot . DIRECTORY_SEPARATOR . 'apireports' . DIRECTORY_SEPARATOR . 'pblogs.txt';
		$myfile = fopen($logfile_dir, "a");
		$txt = date("d-m-Y H:i:s") . ": ";
		fwrite($myfile, "\n". $txt . $message);
		fclose($myfile);
    }
}
