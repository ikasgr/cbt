<?php
/**
 * Created by IntelliJ IDEA.
 * User: AbangAzmi
 * Date: 30/01/2021
 * Time: 12:08
 */

class Install_model extends CI_Model {

    function install_success(){
        return $this->check_installer();
    }

    function check_installer(){
        include APPPATH . 'config/database.php';
        $database = $db['default']['database'];
        $this->load->dbutil();
        // check connection details
        if ($database == '') {
            return '1'; //nama database belum ditulis
        } else {
            if (!$this->dbutil->database_exists($database)) {
                return '5'; //echo 'Not connected to a database, or database not exists';
            } else {
                $CI =& get_instance();
                $CI->load->database();
                if ($CI->db->table_exists('users')) {
                    if ($CI->db->get('users')->row()) {
                        if ($CI->db->get('setting')->row()) {
                            return '0'; // installed
                        } else {
                            return '4'; // tidak ada data sekolah
                        }
                    } else {
                        return '3'; //user belum ada admin
                    }

                } else {
                    return '2'; //belum ada table user
                }
            }
        }
    }
}
