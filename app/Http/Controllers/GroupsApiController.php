<?php

namespace App\Http\Controllers;

use Input;
use Response;
use DB;
use App\Models\Group;

class GroupsApiController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->beforeFilter('auth', array('on' => array('post', 'put', 'delete')));
    }

    public function getVerify()
    {
        $this->beforeFilter('admin');

        $status = Input::get('status');

        if ($status) {
            $groups = Group::where('status', '=', $status)->get();
        } else {
            $groups = Group::all();
        }

        return Response::json($groups);
    }

    public function putVerify($groupId)
    {
        $this->beforeFilter('admin');

        $newGroup = (object) Input::all();

        if (!Group::isValidStatus($newGroup->status)) {
            throw new \Exception("Invalid value for verify request");
        }

        $group = Group::where('id', '=', $groupId)->first();

        if (!$group) {
            throw new \Exception("Invalid Group");
        }

        $group->status = $newGroup->status;

        DB::transaction(function () use ($group) {
            $group->save();

            switch ($group->status) {
                case Group::STATUS_ACTIVE:
                    $group->createRbacRules();
                    break;
                case Group::STATUS_PENDING:
                    $group->destroyRbacRules();
                    break;
            }
        });

        return Response::json($group);
    }
}
