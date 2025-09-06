<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Update extends CI_Controller {

    function __construct(){
        parent::__construct();
        include APPPATH . 'config/database.php';
        //$this->load->model('Install_model', 'install');
        //$this->load->model('Dashboard_model', 'dashboard');
        $this->load->dbforge();
        $this->load->database();
        $this->load->library('encryption');
    }

    public function output_json($data, $encode = true){
        if($encode) $data = json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function index() {
        $json = file_get_contents("./assets/app/db/database.json");
        $json = json_decode($json);
        $json = (array) $json;

        $data['json'] = $json;
        $this->load->view('install/header', $data);
        $this->load->view('install/update');
        $this->load->view('install/footer');
    }

    function object_to_array($data){
        if (is_array($data) || is_object($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[$key] = (is_array($data) || is_object($data)) ? $this->object_to_array($value) : $value;
            }
            return $result;
        }
        return $data;
    }

    public function checkDatabase() {
        $db_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        $tabless = $this->db->list_tables();
        $fields = [];
        $currentDb = [];
        foreach ($tabless as $table) {
            $datafld = $this->db->field_data($table);

            $sql = 'SELECT `column_name`, `numeric_precision`, `extra`, `is_nullable`'.
			' FROM `information_schema`.`columns` WHERE table_schema = "'.$this->db->database.'" AND table_name = "'.$table.'"';

            if (($query = $this->db->query($sql)) === FALSE) {
                $currentDb = FALSE;
            }
            $query = $query->result_object();

            $retval = array();
            for ($i = 0, $c = count($query); $i < $c; $i++) {
                if ($datafld[$i]->name == $query[$i]->column_name) {
                    if ($query[$i]->extra != '') {
                        if ($query[$i]->extra == 'auto_increment') {
                            $datafld[$i]->auto_increment = true;
                        } else {
                            $datafld[$i]->extra = $query[$i]->extra;
                        }
                    }
                }
                $retval[$i]			    = new stdClass();
                $retval[$i]->name		= $query[$i]->column_name;
                $retval[$i]->extra		= $query[$i]->extra;
            }
            $currentDb[$table] = $retval;
            $fields[$table] = $datafld;
        }

        $json = file_get_contents("./assets/app/db/database.json");
        $json = json_decode($json);
        $json = (array) $json;

        $tbl_baru = array_keys($json);
        $tbl_ada = array_keys($fields);
        $full_tables = array_merge($tbl_baru, $tbl_ada);
        $full_tables = array_unique($full_tables);
        sort($full_tables);

        $create_tables = [];
        $add_columns = [];
        $edit_columns = [];

        foreach ($full_tables as $table) {
            if ($this->db->table_exists($table)) {
                if (isset($json[$table])) {
                    foreach ($json[$table] as $jtbl) {
                        if ($this->db->field_exists($jtbl->name, $table)) {
                            foreach ($fields[$table] as $ftbl) {
                                if ($jtbl->name == $ftbl->name) {
                                    if ($jtbl->default != $ftbl->default || $jtbl->max_length != $ftbl->max_length || $jtbl->type != $ftbl->type) {
                                        $edit_columns[$table][] = $jtbl;
                                    }
                                }
                            }
                        } else {
                            $add_columns[$table][] = $jtbl;
                        }
                    }
                }
            } else {
                $create_tables[$table] = $json[$table];
            }
        }

        $counts = count($create_tables) + count($add_columns) + count($edit_columns);
        $data = [
            'db' => $fields,
            'create' => $create_tables,
            'modify' => $edit_columns,
            'add' => $add_columns,
            'counts' => $counts,
            'json' => $json,
            'current' => $currentDb
            ];
        $this->output_json($data);
    }

    public function updateDatabase() {
        $tabless = $this->db->list_tables();
        $fields = [];
        foreach ($tabless as $table) {
            $fields[$table] = $this->db->field_data($table);
        }

        $json = file_get_contents("./assets/app/db/database.json");
        $json = json_decode($json);
        $json = (array) $json;

        $tbl_baru = array_keys($json);
        $tbl_ada = array_keys($fields);
        $full_tables = array_merge($tbl_baru, $tbl_ada);
        $full_tables = array_unique($full_tables);
        sort($full_tables);

        //$create_tables = [];
        //$add_columns = [];
        //$edit_columns = [];

        foreach ($full_tables as $table) {
            if ($this->db->table_exists($table)) {
                if (isset($json[$table])) {
                    foreach ($json[$table] as $jtbl) {
                        if ($this->db->field_exists($jtbl->name, $table)) {
                            foreach ($fields[$table] as $ftbl) {
                                if ($jtbl->name == $ftbl->name) {
                                    if ($jtbl->default != $ftbl->default || $jtbl->max_length != $ftbl->max_length || $jtbl->type != $ftbl->type) {
                                        if ($jtbl->primary_key == 0) {
                                            if ($jtbl->default == 'CURRENT_TIMESTAMP') {
                                                $onUpdate = isset($jtbl->extra) ? ' '.strtolower($jtbl->extra ?? '') : '';
                                                $field = array(
                                                    $jtbl->name.' datetime default current_timestamp'.$onUpdate
                                                );
                                            } else {
                                                $field = array(
                                                    $jtbl->name => array(
                                                        'type' => $jtbl->type,
                                                        'constraint' => $jtbl->max_length,
                                                        'default' => $jtbl->default
                                                    )
                                                );
                                            }
                                            $this->dbforge->modify_column($table, $field);
                                        } else {
                                            if ($jtbl->auto_increment == true) {
                                                $field = array(
                                                    $jtbl->name => array(
                                                        'type' => $jtbl->type,
                                                        'constraint' => $jtbl->max_length,
                                                        'null' => false,
                                                        'auto_increment' => true
                                                    )
                                                );
                                            } else {
                                                $field = array(
                                                    $jtbl->name => array(
                                                        'type' => $jtbl->type,
                                                        'constraint' => $jtbl->max_length,
                                                        'null' => false,
                                                    )
                                                );
                                            }
                                            $this->dbforge->add_key($jtbl->name,true);
                                            $this->dbforge->modify_column($table, $field);
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($jtbl->primary_key == 0) {
                                $field = array(
                                    $jtbl->name => array(
                                        'type' => $jtbl->type,
                                        'constraint' => $jtbl->max_length,
                                        'default' => $jtbl->default
                                    )
                                );
                                $this->dbforge->add_column($table, $field);
                            } else {
                                $field = array(
                                    $jtbl->name => array(
                                        'type' => $jtbl->type,
                                        'constraint' => $jtbl->max_length,
                                        'null' => false,
                                    )
                                );
                                $this->dbforge->add_key($jtbl->name,true);
                                $this->dbforge->add_column($table, $field);
                            }
                        }
                    }
                }
            } else {
                if (isset($json[$table])) {
                    foreach ($json[$table] as $tbl=>$jtbl) {
                        $field = [
                            $jtbl->name => [
                                'type' => $jtbl->type,
                                'constraint' => $jtbl->max_length,
                                //'default' => $jtbl->default,
                                'null' => $jtbl->primary_key == 0
                            ]
                        ];
                        $this->dbforge->add_field($field);
                        if ($jtbl->primary_key == 1) {
                            $this->dbforge->add_key($jtbl->name,true);
                        }
                    }
                    $this->dbforge->create_table($table, TRUE);
                    $this->db->query('ALTER TABLE  `'.$table.'` ENGINE = InnoDB');
                    //$this->output_json($fieldss);
                }
            }
        }
        echo true;
    }

    public function checkDb() {
        $db_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        $tabless = $this->db->list_tables();
        $fields = [];
        foreach ($tabless as $table) {
            $sql = 'SELECT `column_name`, `column_type`, `collation_name`, `data_type`, `character_maximum_length`, `numeric_precision`,'.
                ' `column_default`, `column_key`, `column_comment`, `extra`, `is_nullable`
			FROM `information_schema`.`columns` WHERE table_schema = "'.$this->db->database.'" AND table_name = "'.$table.'"';

            if (($query = $this->db->query($sql)) === FALSE) {
                $fields = FALSE;
            }
            $query = $query->result_object();

            $retval = array();
            for ($i = 0, $c = count($query); $i < $c; $i++)
            {
                $retval[$i]			    = new stdClass();
                $retval[$i]->name		= $query[$i]->column_name;
                $retval[$i]->col_type	= $query[$i]->column_type;
                $retval[$i]->type		= $query[$i]->data_type;
                $retval[$i]->collation	= $query[$i]->collation_name;
                $retval[$i]->max_length	= ($query[$i]->character_maximum_length > 0) ? $query[$i]->character_maximum_length : $query[$i]->numeric_precision;
                $retval[$i]->default	= $query[$i]->column_default;
                $retval[$i]->comment	= $query[$i]->column_comment;
                $retval[$i]->extra		= $query[$i]->extra;
                $retval[$i]->nullable	= $query[$i]->is_nullable;
                $retval[$i]->primary	= $query[$i]->column_key;
            }

            $fields[$table] = (object)['table_name' => $table, 'columns' => $retval];
        }

        $json = file_get_contents("./assets/app/db/database.json");
        $json = json_decode($json);
        $json = (array) $json;

        $tbl_seharusnya = array_keys($json);
        $tbl_ada = array_keys($fields);
        $full_tables = array_merge($tbl_seharusnya, $tbl_ada);
        $full_tables = array_unique($full_tables);
        sort($full_tables);

        // cek table
        $create_tables = [];
        $script_create_table = [];

        $add_columns = [];
        $script_create_column = [];

        $edit_columns = [];
        $script_edit_column = [];

        foreach ($full_tables as $table) {
            if (!$this->db->table_exists($table)) {
                $create_tables[] = $json[$table];
                $script = 'CREATE TABLE `'.$table.'` (';
                $pri = '';
                foreach ($json[$table]->columns as $column) {
                    if ($column->max_length == null) {
                        $length = '';
                    } elseif ($column->type != "longtext" && $column->type != "mediumtext" && $column->type != "text") {
                        if ($column->type == "int") {
                            $length = '('.($column->max_length + 1).')';
                        } else {
                            $length = '('.($column->max_length).')';
                        }
                    } else {
                        $length = '';
                    }
                    $nullable = $column->nullable == "NO" ? ' NOT NULL' : '';
                    $default = $column->default == null ? '' : ' DEFAULT '.$column->default;
                    $extra = $column->extra == "" ? '' : ' '.strtoupper($column->extra ?? '');
                    $comment = $column->comment == "" ? '' : ' COMMENT \''.$column->comment .'\'';

                    $script .= '`'.$column->name.'` '.$column->type.$length.$nullable.$default.$extra.$comment.', ';
                    $pri .= $column->primary != "" ? 'PRIMARY KEY (`'.$column->name.'`)' : '';
                }
                $script .= $pri.') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
                $script_create_table[$table] = $script;
            } else {
                // cek kolom
                if (isset($json[$table])) {
                    $add_column = [];
                    $modif_column = [];
                    foreach ($json[$table]->columns as $jtbl) {
                        if (!$this->db->field_exists($jtbl->name, $table)) {
                            $add_columns[$table][] = $jtbl;

                            if ($jtbl->max_length == null) {
                                $length = '';
                            } elseif ($jtbl->type != "longtext" && $jtbl->type != "mediumtext" && $jtbl->type != "text") {
                                if ($jtbl->type == "int") {
                                    $length = '('.($jtbl->max_length + 1).')';
                                } else {
                                    $length = '('.($jtbl->max_length).')';
                                }
                            } else {
                                $length = '';
                            }
                            $nullable = $jtbl->nullable == "NO" ? ' NOT NULL' : '';
                            $default = $jtbl->default == null ? '' : ' DEFAULT '.$jtbl->default;
                            $extra = $jtbl->extra == "" ? '' : ' '.strtoupper($jtbl->extra ?? '');
                            if (strtoupper($extra ?? '') == ' AUTO_INCREMENT') $extra .= ' PRIMARY KEY';
                            $comment = $jtbl->comment == "" ? '' : ' COMMENT \''.$jtbl->comment . '\'';

                            array_push($add_column , 'ADD `'.$jtbl->name.'` '.$jtbl->type.$length.$nullable.$default.$extra.$comment);
                            //array_push($name_tables, $jtbl->name);
                        }

                        foreach ($fields[$table]->columns as $ftbl) {
                            if ($jtbl->name == $ftbl->name) {

                                if ($jtbl->col_type != $ftbl->col_type) {
                                    $edit_columns[$table][$jtbl->name]['col_type'] = $jtbl->col_type;
                                }

                                //if ($jtbl->type != $ftbl->type) {
                                //    $edit_columns[$table][$jtbl->name]['type'] = $jtbl->type;
                                //}

                                //if ($jtbl->max_length != $ftbl->max_length) {
                                //    $edit_columns[$table][$jtbl->name]['max_length'] = $jtbl->max_length;
                                //}

                                if ($jtbl->nullable != $ftbl->nullable) {
                                    $edit_columns[$table][$jtbl->name]['nullable'] = $jtbl->nullable;
                                }

                                if ($jtbl->default != null) {
                                    $jtbl->default = str_replace("()", "", $jtbl->default ?? '');
                                    $jtbl->default = strtoupper($jtbl->default ?? '');
                                }
                                if ($ftbl->default != null) {
                                    $ftbl->default = str_replace("()", "", $ftbl->default ?? '');
                                    $ftbl->default = strtoupper($ftbl->default ?? '');
                                }
                                if ($jtbl->default != $ftbl->default) {
                                    $edit_columns[$table][$jtbl->name]['default'] = $jtbl->default;
                                }

                                if ($jtbl->extra != null) {
                                    $jtbl->extra = str_replace("()", "", $jtbl->extra ?? '');
                                    $jtbl->extra = strtoupper($jtbl->extra ?? '');
                                }
                                if ($ftbl->extra != null) {
                                    $ftbl->extra = str_replace("()", "", $ftbl->extra ?? '');
                                    $ftbl->extra = strtoupper($ftbl->extra ?? '');
                                }
                                if ($jtbl->extra != $ftbl->extra) {
                                    $edit_columns[$table][$jtbl->name]['extra'] = $jtbl->extra;
                                }

                                if ($jtbl->comment != $ftbl->comment) {
                                    $edit_columns[$table][$jtbl->name]['comment'] = $jtbl->comment;
                                }

                                if ($jtbl->primary != $ftbl->primary) {
                                    $edit_columns[$table][$jtbl->name]['primary'] = $jtbl->primary;

                                    if (strtolower($jtbl->primary ?? '') == 'pri') {
                                        array_push($modif_column, 'ADD PRIMARY KEY (`'.$jtbl->name.'`)');
                                    } elseif (strtolower($jtbl->primary ?? '') == 'uni') {
                                        array_push($modif_column, 'ADD UNIQUE KEY `'.$jtbl->name.'` (`'.$jtbl->name.'`)');
                                    }
                                }

                                if ($jtbl->col_type != $ftbl->col_type ||
                                    //$jtbl->max_length != $ftbl->max_length ||
                                    $jtbl->nullable != $ftbl->nullable ||
                                    $jtbl->default != $ftbl->default ||
                                    $jtbl->extra != $ftbl->extra ||
                                    $jtbl->comment != $ftbl->comment) {

                                    /*
                                    if ($jtbl->max_length == null) {
                                        //col_type = datetime
                                        $length = '';
                                    } elseif ($jtbl->type != "longtext" && $jtbl->type != "mediumtext" && $jtbl->type != "text") {
                                        if ($jtbl->type == "int") {
                                            $length = '('.($jtbl->max_length + 1).')';
                                        } else {
                                            $length = '('.($jtbl->max_length).')';
                                        }
                                    } else {
                                        $length = '';
                                    }
                                    */
                                    $nullable = $jtbl->nullable == "NO" ? ' NOT NULL' : '';
                                    $default = $jtbl->default == null ? '' : ' DEFAULT '.$jtbl->default;
                                    $extra = $jtbl->extra == "" ? '' : ' '.strtoupper($jtbl->extra ?? '');
                                    $comment = $jtbl->comment == "" ? '' : ' COMMENT \''.$jtbl->comment .'\'';

                                    //array_push($modif_column, 'MODIFY `'.$jtbl->name.'` '. $jtbl->type.$length.$nullable.$default.$extra.$comment);
                                    array_push($modif_column, 'MODIFY `'.$jtbl->name.'` '. $jtbl->col_type.$nullable.$default.$extra.$comment);
                                }
                            }
                        }
                    }
                    if (count($add_column)>0) $script_create_column[$table] = 'ALTER TABLE `'.$table. '` ' . implode(', ', $add_column) .';';
                    if (count($modif_column)>0) $script_edit_column[$table] = 'ALTER TABLE `'.$table. '` ' . implode(', ', $modif_column) . ';';
                }
            }
        }

        $this->db->db_debug = $db_debug;

        $data = [
            'fields' => $fields,
            //'json' => $json,
            //'tbl_baru' => $tbl_seharusnya,
            //'tbl_ada' => $tbl_ada,
            //'full_tables' => $full_tables,
            'create_tables' => $create_tables,
            'count_tbl' => count($create_tables),
            //'script_create_table' => $script_create_table,
            'add_columns_to_table' => $add_columns,
            'count_col' => count($add_columns),
            //'script_create_column' => $script_create_column,
            'edit_columns' => $edit_columns,
            'count_mod' => count($edit_columns),
            //'script_edit_column' => $script_edit_column,
            'add_tbl' => $this->encryption->encrypt(json_encode($script_create_table)),
            'add_col' => $this->encryption->encrypt(json_encode($script_create_column)),
            'mod_col' => $this->encryption->encrypt(json_encode($script_edit_column))
        ];

        $this->output_json($data);
    }

    public function createTable() {
        $scripts = $this->input->post('data', true);
        str_replace('%2B', '+', $scripts ?? '');
        sleep(1);

        $scripts = json_decode($this->encryption->decrypt($scripts));

        $queries = '';
        foreach ($scripts as $script) {
            $queries .= $script;
        }
        $data['success'] = $this->runQuery($queries);
        $data['message'] = "Update kolom";
        $this->output_json($data);
    }

    public function createColumn() {
        $scripts = $this->input->post('data', true);
        str_replace('%2B', '+', $scripts ?? '');
        sleep(1);

        $scripts = json_decode($this->encryption->decrypt($scripts));

        $queries = '';
        foreach ($scripts as $script) {
            $queries .= $script;
        }

        if (strpos('`uid`', $queries) !== false) {
            $this->updateUID();
        }
        //$data['script'] = $queries;
        $data['success'] = $this->runQuery($queries);
        $data['message'] = "Modify kolom";
        $this->output_json($data);
    }

    public function editColumn() {
        $scripts = $this->input->post('data', true);
        str_replace('%2B', '+', $scripts ?? '');
        sleep(1);

        $scripts = json_decode($this->encryption->decrypt($scripts));

        $queries = '';
        foreach ($scripts as $script) {
            $queries .= $script;
        }
        //$data['script'] = $queries;
        $data['success'] = $this->runQuery($queries);
        $data['message'] = "Update selesai";
        $this->output_json($data);
    }

    function runQuery($script){
        $hostname = $this->db->hostname;
        $hostuser = $this->db->username;
        $hostpass = $this->db->password;
        $database = $this->db->database;
        $mysqli = new mysqli($hostname,$hostuser,$hostpass,$database);
        if(mysqli_connect_errno()) {
            return mysqli_connect_errno();
            //die($mysqli->error);
            //return false;
        }
        //if($mysqli->errno) die($mysqli->error);
        //$query = file_get_contents($script);
        $mysqli->multi_query($script);
        $mysqli->close();

        return true;
    }

    function updateUID() {
        $this->load->library('Uuid', 'uuid');
        $siswas = $this->db->get('master_siswa')->result();
        $input = array();
        foreach ($siswas as $siswa) {
            $input[] = array(
                'id_siswa'	=> $siswa->id_siswa,
                'uid' 	=> $this->uuid->v4() //$this->db->set('uid','UUID()',FALSE)
            );
        }

        return $this->db->update_batch('master_siswa', $input, 'id_siswa');
    }

    function make_base(){
        //$this->load->library('ci_migrations_generator/Sqltoci');

        // All Tables:
        //$this->sqltoci->generate();

        //Single Table:
        //$this->sqltoci->generate('master_siswa');
    }
}
