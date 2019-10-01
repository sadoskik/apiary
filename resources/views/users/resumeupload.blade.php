@extends('layouts/app')

@section('title')
R&eacute;sum&eacute; | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  R&eacute;sum&eacute;
@endcomponent

<resume-upload-form user-uid="{{$id}}"></resume-upload-form>

@endsection
