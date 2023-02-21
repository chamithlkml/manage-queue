<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 12/18/18
 * Time: 6:11 PM
 */
class Migration_Add_crs extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => TRUE,
                'auto_increment' => TRUE),
            'vehicle_no' => array(
                'type' => 'VARCHAR',
                'constraint' => '50'
            ),
            'cr_name' => array(
                'type' => 'VARCHAR',
                'unsigned' => TRUE,
                'constraint' => '10'
            ),
            'file_path' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE
            ),
            'officer_id' => array(
                'type' => 'INT',
                'unsigned' => TRUE,
                'constraint' => '10',
                'null' => TRUE
            ),
            'created_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'deleted_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('officer_id');
        $this->dbforge->create_table('crs');
    }

    public function down()
    {
        $this->dbforge->drop_table('crs');
    }
}