<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddIsBindToTableTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // 桌台表
        $table = $this->table('table');
        $table->addColumn(Column::integer('is_bind')->setNull(false)->setDefault(0)->setComment('平板绑定状态 0-否 1-是')->setAfter('app_id'));
        $table->addColumn(Column::string('bind_info')->setNull(false)->setDefault("")->setComment('绑定信息')->setAfter('app_id'));
        $table->update();
    }
}
