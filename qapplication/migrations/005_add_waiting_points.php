<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Dropsuite Pte Ltd.
 * User: chamith
 * Date: 11/4/18
 * Time: 4:57 PM
 */
class Migration_Add_waiting_points extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'purpose_id' => array(
                'type' => 'INT',
                'constraint' => 5,
                'null' => FALSE
            ),
            'waiting_point_on' => array(
                'type' => 'DATETIME'
            ),
            'duration' => array(
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => TRUE
            ),
            'created_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'deleted_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'created_by' => array(
                'type' => 'INT',
                'constraint' => 5,
                'null' => FALSE
            ),
        ));

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('purpose_id', TRUE);
        $this->dbforge->add_key('officer_id', TRUE);
        $this->dbforge->create_table('waiting_points');
    }

    public function down()
    {
        $this->dbforge->drop_table('waiting_points');
    }
}