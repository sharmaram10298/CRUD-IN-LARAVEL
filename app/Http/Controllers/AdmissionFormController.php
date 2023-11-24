<?php

namespace App\Http\Controllers;

use App\Models\Student_Admission_Form;
use Illuminate\Http\Request;
use App\Helper\Constant;

class AdmissionFormController extends Controller
{
    public function index(){
        return view('student.index',['studentData'=>Student_Admission_Form::get()]);
    }
    public function create(){
        return view('student.create');
    }
    public function store(Request $request){
        $imageName = time().'.'.$request->image->extension();
        $request->image->move(public_path('images'),$imageName);
        
        $data = new Student_Admission_Form;
        $data->image = $imageName;
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone_no = $request->phone_no;
        $data->class = $request->class;
        $data->Address = $request->Address;
        $data->save();
        return redirect('index');
    }
    public function edit($id){
       $editdata = Student_Admission_Form::where('id', $id)->first();
       return view('student.edit',['sdtdata'=> $editdata]);
    }

    public function update(Request $request, $id){
        $request->validate([
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,|max:1000',
            'name' => 'required',
            'email' => 'required',
            'phone_no' => 'required',
            'class' => 'required',
            'Address' => 'required'
        ]);
 
        $students = Student_Admission_Form::where('id', $id)->first();

        if(isset($request->image)){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'),$imageName);
            $students->image = $imageName;
        }
        $students->name = $request->name;
        $students->email = $request->email;
        $students->phone_no = $request->phone_no;
        $students->class = $request->class;
        $students->Address = $request->Address;
        $students->save();
        return redirect('index');

        
    }
    public function delete($id){
        $datadelete = Student_Admission_Form::where('id', $id)->first();
        $datadelete->delete();
        return redirect('index');
    }
}
