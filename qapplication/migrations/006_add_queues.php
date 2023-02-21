<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 12/8/18
 * Time: 11:13 AM
 */
class Migration_Add_queues extends CI_Migration
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
        $this->dbforge->create_table('queues');
    }

    public function down()
    {
        $this->dbforge->drop_table('queues');
    }
}