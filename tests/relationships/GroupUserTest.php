<?php namespace Redooor\Redminportal\Test;

use DB;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

class GroupUserTest extends BaseRelationshipTest
{
    public function testAddOneGroupToUser()
    {
        $user = $this->createNewModel(new User, array(
            'email'     => 'john.doe@example.com',
            'password'  => 'test',
            'activated' => true
        ));
        
        $testcase = array(
            'name'  => 'test',
            'permissions' => "{'admin.view':'1','admin.create':'0'}"
        );
        
        $group = $this->createNewModel(new Group, $testcase);
        $user->addGroup($group->id);
        $group_id = $group->id;

        $this->assertTrue($user->groups()->count() == 1);
        $this->assertTrue($group->users()->count() == 1);
        
        $check_groups = DB::table('users_groups')
            ->where('group_id', $group_id)
            ->count();
        $this->assertTrue($check_groups == 1);
        
        foreach ($user->groups as $item) {
            $this->assertTrueModelAllTestcases($item, $testcase);
        }
        
        // Delete user will delete groups relationship
        $user->delete();
        
        $check_groups = DB::table('users_groups')
            ->where('group_id', $group_id)
            ->count();
        $this->assertTrue($check_groups == 0);
    }
    
    public function testAddMultipleGroupsToUser()
    {
        $user = $this->createNewModel(new User, array(
            'email'     => 'sam.toh@example.com',
            'password'  => 'multiple',
            'activated' => false
        ));
        
        $testcase1 = array(
            'name'  => 'test',
            'permissions' => "{'admin.view':'1','admin.create':'0'}"
        );
        
        $testcase2 = array(
            'name'  => 'test2',
            'permissions' => "{'admin.view':'1','admin.create':'0'}"
        );
        
        $group1 = $this->createNewModel(new Group, $testcase1);
        $group1_id = $group1->id;
        
        $group2 = $this->createNewModel(new Group, $testcase2);
        $group2_id = $group2->id;
        
        $user->addGroup([$group1->id, $group2->id]);
        
        $this->assertTrue($user->groups()->count() == 2);
        $this->assertTrue($group1->users()->count() == 1);
        $this->assertTrue($group2->users()->count() == 1);
        
        $check_groups = DB::table('users_groups')
            ->where('group_id', $group1_id)
            ->count();
        $this->assertTrue($check_groups == 1);
        
        $check_groups = DB::table('users_groups')
            ->where('group_id', $group2_id)
            ->count();
        $this->assertTrue($check_groups == 1);
        
        foreach ($user->groups as $item) {
            if ($item->name == 'test') {
                $this->assertTrueModelAllTestcases($item, $testcase1);
            } else {
                $this->assertTrueModelAllTestcases($item, $testcase2);
            }
        }
        
        // Delete user will delete groups relationship
        $user->delete();
        
        $check_groups = DB::table('users_groups')
            ->where('group_id', $group1_id)
            ->count();
        $this->assertTrue($check_groups == 0);
        
        $check_groups = DB::table('users_groups')
            ->where('group_id', $group2_id)
            ->count();
        $this->assertTrue($check_groups == 0);
    }
}
