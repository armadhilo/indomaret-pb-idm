@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">HOME</h1>
@endsection


@section('content')
<script src="{{ url('js/home.js?time=') . rand() }}"></script>

<div class="container-fluid" id="container-wrapper">
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="text-center">
                <h1 class="pagetitle">
                    WELCOME TO PB IDM <sup>VB</sup>
                </h1>
            </div>
        </div>
    </div>

</div>
@endsection
