@extends('bootstrap.base')
@include('bootstrap.navbar')
@section('title', 'Student Admission Form')
@section('contents')
   
<div class="container py-5">
    <div class="row">
        <div class="col-md-6 offset-3">
        <table class="table table-bordered  table-hover">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">profile</th>
      <th scope="col">Name</th>
      <th scope="col">email</th>
      <th scope="col">Phone</th>
      <th scope="col">Standar</th>
      <th scope="col">Address</th>
      <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>

  @foreach($studentData as $row)
    <tr>
      <th scope="row">{{ $row->id }}</th>
      <td><img src="images/{{ $row->image }}" alt="" class="rounded-circle" width="50" height="50"></td>
      <td>{{ $row->name}}</td>
      <td>{{ $row->email }}</td>
      <td>{{ $row->phone_no }}</td>
      <td>{{ $row->class}}</td>
      <td>{{ $row->Address}}</td>
      <td class="d-flex">
      <a class="btn btn-success me-2 " href="{{$row->id}}/edit" role="btn">Edit</a>
      <form action="{{$row->id}}/delete" method="POST">
        @csrf
        @method('DELETE')
      <a  type="submit" class="btn btn-danger " >Delete</a>

      </form>
      <td>
      
   </tr>
@endforeach

@endsection