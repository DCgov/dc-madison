<?php


class GroupTest extends TestCase
{
    use ModelHelpers;

    protected $group;

    /**
     *   setUp.
     *
     *   Runs before each test
     *       Stubs $this->group
     *       Truncates Group table
     */
    public function setUp()
    {
        parent::setUp();

        Eloquent::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('groups')->truncate();

        //Stub a generic user
        $this->group = $this->stubGroup();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     *   stubGroup.
     *
     *   Helper function to stub a generic User
     *
     *   @param void
     *
     *   @return Group $group
     */
    protected function stubGroup()
    {
        $group = new Group();
        $group->name = 'Group Name';
        $group->address1 = '123 Some Street';
        $group->address2 = '';
        $group->city = 'Gotham';
        $group->state = 'NY';
        $group->postal_code = "12345";
        $group->phone_number = "555-555-5555";
        $group->display_name = "Display Name";

        return $group;
    }

    /**
     *   @test
     */
    public function name_is_required()
    {
        unset($this->group->name);

        $this->assertFalse($this->group->save());

        $errors = $this->group->getErrors()->all();

        //Ensure we only have the error we expect
        $this->assertCount(1, $errors);

        //Check the string value for that error
        $this->assertEquals($errors[0], "The group name is required");
    }

    /**
     *   @test
     */
    public function address_is_required()
    {
        unset($this->group->address1);

        $this->assertFalse($this->group->save());

        $errors = $this->group->getErrors()->all();

        //Ensure we only have the error we expect
        $this->assertCount(1, $errors);

        //Check the string value for that error
        $this->assertEquals($errors[0], "The group address is required");
    }

    /**
     *   @test
     */
    public function address2_is_optional()
    {
        unset($this->group->address2);

        $this->assertTrue($this->group->save());
    }

    /**
     *   @test
     */
    public function city_is_required()
    {
        unset($this->group->city);

        $this->assertFalse($this->group->save());

        $errors = $this->group->getErrors()->all();

        //Ensure we only have the error we expect
        $this->assertCount(1, $errors);

        //Check the string value for that error
        $this->assertEquals($errors[0], "The group city is required");
    }

    /**
     *   @test
     */
    public function state_is_required()
    {
        unset($this->group->state);

        $this->assertFalse($this->group->save());

        $errors = $this->group->getErrors()->all();

        //Ensure we only have the error we expect
        $this->assertCount(1, $errors);

        //Check the string value for that error
        $this->assertEquals($errors[0], "The group state is required");
    }

    /**
     *   @test
     */
    public function postal_code_is_required()
    {
        unset($this->group->postal_code);

        $this->assertFalse($this->group->save());

        $errors = $this->group->getErrors()->all();

        //Ensure we only have the error we expect
        $this->assertCount(1, $errors);

        //Check the string value for that error
        $this->assertEquals($errors[0], "The group postal code is required");
    }

    /**
     *   @test
     */
    public function phone_number_is_required()
    {
        unset($this->group->phone_number);

        $this->assertFalse($this->group->save());

        $errors = $this->group->getErrors()->all();

        //Ensure we only have the error we expect
        $this->assertCount(1, $errors);

        //Check the string value for that error
        $this->assertEquals($errors[0], "The group phone number is required");
    }

    /**
     *   @test
     */
    public function display_name_is_required()
    {
        unset($this->group->display_name);

        $this->assertFalse($this->group->save());

        $errors = $this->group->getErrors()->all();

        //Ensure we only have the error we expect
        $this->assertCount(1, $errors);

        //Check the string value for that error
        $this->assertEquals($errors[0], "The group display name is required");
    }

    /**
     *   @test
     */
    public function group_saved_correctly()
    {
        $this->assertTrue($this->group->save());
        $this->assertTrue($this->group->exists);
    }
}
