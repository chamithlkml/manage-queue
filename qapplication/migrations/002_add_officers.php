<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Chamith.
 * User: chamith
 * Date: 14/9/18
 * Time: 4:44 AM
 */

class Migration_Add_officers extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => TRUE,
                'auto_increment' => TRUE),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'mobile_number' => array(
                'type' => 'VARCHAR',
                'unsigned' => TRUE,
                'constraint' => '10'
            ),
            'mobile_number_verification_code' => array(
                'type' => 'VARCHAR',
                'constraint' => '10',
                'null' => TRUE
            ),
            'verification_code_issued_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'mobile_number_verified' => array(
                'type' => 'TINYINT',
                'constraint' => '1'
            ),
            'role' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE
            ),
            'deactivated_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'created_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'created_by' => array(
                'type' => 'INT',
                'null' => TRUE
            ),
            'deactivated_by' => array(
                'type' => 'INT',
                'null' => TRUE
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('officers');

        $this->config->load('appconfig', TRUE);

        $officers = $this->config->item('officers', 'appconfig');

        $data = array();

        foreach($officers as $officer)
        {
            $data[] = array(
                'name' => $officer[0],
                'mobile_number' => $officer[1],
                'role' => $officer[2],
                'mobile_number_verified' => 1,
                'created_on' => date("Y-m-d H:i:s"),
                'created_by' => 0
            );
        }

        $this->db->insert_batch('officers', $data);
    }

    public function down()
    {
        $this->dbforge->drop_table('officers');
    }
}