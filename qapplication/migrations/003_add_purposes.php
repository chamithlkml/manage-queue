<?php
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 10/29/18
 * Time: 4:49 AM
 */
class Migration_Add_purposes extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 2,
                'unsigned' => TRUE,
                'auto_increment' => TRUE),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'type' => array(
                'type' => 'VARCHAR',
                'unsigned' => TRUE,
                'constraint' => '100'
            ),
            'deactivated_on' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'created_on' => array(
                'type' => 'DATETIME',
            ),
            'created_by' => array(
                'type' => 'INT',
                'constraint' => 2
            ),
            'deactivated_by' => array(
                'type' => 'INT',
                'constraint' => 2,
                'null' => TRUE
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('purposes');

        $this->config->load('appconfig', TRUE);

        $purposes = $this->config->item('purposes', 'appconfig');

        foreach($purposes as $i=>$purpose)
        {
            $purposes[$i]['created_on'] = date("Y-m-d H:i:s");
            $purposes[$i]['created_by'] = 0;
        }

        log_message('debug', 'purposes: ' . print_r($purposes, true));

        $this->db->insert_batch('purposes', $purposes);
    }

    public function down()
    {
        $this->dbforge->drop_table('purposes');
    }
}