<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith.
 * User: chamith
 * Date: 8/22/18
 * Time: 4:40 PM
 */
class Migration_Add_tokens extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 10,
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
            'date_to_office' => array(
                'type' => 'DATE'
            ),
            'queue_no' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE
            ),
            'queue_no_issued_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'expected_service_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'arrived_to_office_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'uuid' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE
            ),
            'job_started_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'job_done_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'officer_id' => array(
                'type' => 'INT',
                'unsigned' => TRUE,
                'constraint' => '10',
                'null' => TRUE
            ),
            'estimated_waiting_time' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('officers_id');
        $this->dbforge->create_table('tokens');
    }

    public function down()
    {
        $this->dbforge->drop_table('tokens');
    }
}