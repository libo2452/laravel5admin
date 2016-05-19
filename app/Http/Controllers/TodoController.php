<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use App\Http\Requests;

class TodoController extends Controller {
    //
    public function index(Request $request) {
        $uncompletedTodos = Todo::where('isCompleted' , 0)->get();
        $completedTodos = Todo::where('isCompleted' , 1)->get();

        $data = [
            'uncompletedTodos' => $uncompletedTodos ,
            'completedTodos'   => $completedTodos
        ];
        if( $request->ajax() ){
//            dd($data);
            return view('todo.ajax' , $data);
        }
        return view('todo.index' , $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request) {
        $todo = new Todo;
        $todo->title = $request->title;
        $todo->save();
        return response()->json(['id' => $todo->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {
        $todo = Todo::find($id);
        return view('todo.show' , ['todo' => $todo]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(Request $request , $id) {
        $todo = Todo::find($id);
        $todo->isCompleted = (bool)$request->isCompleted;
        $todo->save();
        return;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {
        $todo = Todo::find($id);
        $todo->delete();
        return;
    }
}
