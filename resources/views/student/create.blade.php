@extends('bootstrap.base')
@include('bootstrap.navbar')
@section('contents')

<div class="container mt-5 offset-3">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <h1 class="text-center">Student Create Form</h1>
                <div class="card-body">
                <form action="store" method="POST" enctype="multipart/form-data">
                    @csrf
                   
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control"  name="name" required>
                        <label for="student_name" class="form-label">Name</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control"  name="email" required>
                        <label for="student_email" class="form-label">Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control"  name="phone_no" required>
                        <label for="student_number" class="form-label">phone Number</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control"  name="class" required>
                        <label for="student_number" class="form-label">Class</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control"  name="Address" required>
                        <label for="student_address" class="form-label">Address</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="file" class="form-control "  name="image" required>
                    </div>
                  
                    <button type="submit" class="btn btn-primary offset-3">Submit</button>
                </form>
                </div>
                
            </div>
        </div>
    </div>
</div>