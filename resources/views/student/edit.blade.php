@extends('bootstrap.base')
@include('bootstrap.navbar')
@section('contents')

<div class="container mt-5">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <h1>Student Create Form</h1>
                <div class="card-body">
                <form action="update" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-floating mb-3">
                        <input type="file" class="form-control "  name="image"   value="{{old('image',$sdtdata->image)}}">
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control"  name="name" required value="{{old('name',$sdtdata->name)}}">
                        <label for="student_name" class="form-label">Name</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control"  name="email" required value="{{old('email',$sdtdata->email)}}">
                        <label for="student_email" class="form-label">Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control"  name="phone_no" required value="{{old('phone_no',$sdtdata->phone_no)}}">
                        <label for="student_number" class="form-label">phone Number</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control"  name="class" required value="{{old('class',$sdtdata->class)}}">
                        <label for="student_number" class="form-label">Class</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control"  name="Address" required value="{{old('Address',$sdtdata->Address)}}">
                        <label for="student_address" class="form-label">Address</label>
                    </div>
                    <!-- Add more form fields as needed -->
                    <button type="submit" class="btn btn-primary offset-3">update</button>
                </form>
                </div>
                
            </div>
        </div>
    </div>
</div>