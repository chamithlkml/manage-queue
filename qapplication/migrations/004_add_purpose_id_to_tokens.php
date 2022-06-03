<?php
/**
 * Created by Dropsuite Pte Ltd.
 * User: chamith
 * Date: 10/29/18
 * Time: 5:41 AM
 */
class Migration_Add_purpose_id_to_tokens extends CI_Migration
{

    public function up()
    {
        $fields = array(
            'purpose_id' => array(
                'type' => 'INT',
                'constraint' => 5,
                'null' => TRUE
            )
        );

        $this->dbforge->add_column('tokens', $fields);

        $this->dbforge->add_key('purpose_id', FALSE);
    }

    public function down()
    {
        $this->dbforge->drop_column('tokens', 'purpose_id');
    }

}