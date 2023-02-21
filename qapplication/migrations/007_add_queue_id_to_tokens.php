<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 12/8/18
 * Time: 11:22 AM
 */
class Migration_Add_queue_id_to_tokens extends CI_Migration
{
    public function up()
    {
        $fields = array(
            'queue_id' => array(
                'type' => 'INT',
                'constraint' => 5,
                'null' => TRUE
            )
        );

        $this->dbforge->add_column('tokens', $fields);

        $this->dbforge->add_key('queue_id');
    }

    public function down()
    {
        $this->dbforge->drop_column('tokens', 'queue_id');
    }
}