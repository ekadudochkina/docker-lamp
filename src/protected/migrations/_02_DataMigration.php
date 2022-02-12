<?php

/**
 * Начальная миграция для данных. Сюда можно вкладывать все изменения.
 * Создание данных нельзя совмещать с изменением таблиц.
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class _02_DataMigration extends DbMigration
{
    public function up()
    {
        $admin = new SimpleAdmin();
        $admin->setLogin("admin");
        $admin->setEmail("admin@home-studio.pro");
        $admin->setPassword("1q2w3e4rDD");
        $this->saveModel($admin);
        
        if(EnvHelper::isProduction())
        {
            $this->createItems(null);
            $this->createBlog();
            return true;
        }
        
        $user = new User();
        $user->setName("Alex Hawks");
        $user->setLogin("user");

        $user->setEmail("test@home-studio.pro");
        $user->setPassword("1q2w3e4rDD");
        $this->saveModel($user);
        

    }

    public function down()
    {
        
    }

    public function getNumber()
    {
        return 02;
    }





}
