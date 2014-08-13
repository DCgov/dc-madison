<?php


class UserTest extends \Codeception\TestCase\Test
{
   /**
    * @var \UnitTester
    */
    protected $tester;

    protected function _before()
    {
        Artisan::call('migrate');
    }

    protected function _after()
    {

    }

    public function test_fname_is_required() {
        $user = new User;
        $user->email = 'user@mymadison.io';
        $user->password = 'password';
        $user->lname = 'User';
        $this->assertFalse($user->save());

        $errors = $user->errors()->all();
        $this->assertCount(1, $errors);

        $this->assertEquals($errors[0], "The first name field is required.");
    }

    public function test_lname_is_required() {
        $user = new User;
        $user->email = 'user@mymadison.io';
        $user->password = 'password';
        $user->fname = "User";
        $this->assertFalse($user->save());

        $errors = $user->errors()->all();

        $this->assertCount(1, $errors);

        $this->assertEquals($errors[0], "The last name field is required.");
    }

}