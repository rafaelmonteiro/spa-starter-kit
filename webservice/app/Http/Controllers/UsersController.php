<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;

class UsersController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sort = $this->parameters->sort();
        $order = $this->parameters->order();
        $limit = $this->parameters->limit();

        $users = User::orderBy($sort, $order)->paginate($limit);

        return $this->response->collection($users);
    }

    /**
     * Display a full list of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fullList()
    {
        return $this->response->collection(User::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $user = User::create($request->all());

        return $this->response->withCreated($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        if (! $user) {
            return $this->response->withNotFound('User not found');
        }

        return $this->response->item($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {
        $user = User::find($id);

        if (! $user) {
            return $this->response->withNotFound('User not found');
        }
        
        $user->fill($request->all())->save();

        return $this->response->item($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (! $user) {
            return $this->response->withNotFound('User not found');
        }

        $user->delete();

        return $this->response->withNoContent();
    }
}
