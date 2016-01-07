<?php

class DatabaseSeeder extends Seeder
{
    /**
     * Tables to truncate.
     *
     * @var array
     */
    private $tables = [
        'annotation_comments',
        'annotation_permissions',
        'annotation_ranges',
        'annotation_tags',
        'annotations',
        'assigned_roles',
        'categories',
        'category_doc',
        'comment_meta',
        'comments',
        'dates',
        'doc_contents',
        'doc_group',
        'doc_meta',
        'doc_status',
        'doc_user',
        'docs',
        'group_members',
        'groups',
        'note_meta',
        'notifications',
        'organizations',
        'password_reminders',
        'permission_role',
        'permissions',
        'roles',
        'settings',
        'statuses',
        'user_meta',
        'users',
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->cleanDatabase();

        Eloquent::unguard();

        $this->call('UsersTableSeeder');
        $this->call('RbacSeeder');
        $this->call('GroupsTableSeeder');
        $this->call('DocumentsTableSeeder');

        if (App::environment() === 'testing') {
            $this->call('TestSeeder');
        }
    }

    /**
     * Clean database tables removing foreign keys.
     */
    protected function cleanDatabase()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->tables as $tableName) {
            DB::table($tableName)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
