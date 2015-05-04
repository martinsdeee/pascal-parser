<?php
$code = isset($_POST['code']) ? $_POST['code'] : "";
?>

@extends('app')

@section('content')
<div class="row">
    <div class="col-sm-6 col-sm-offset-3">
        <h1 class="page-heading">
            Pascal Parser
        </h1>
        <form method="POST">
        <!-- Code Field -->
        <div class="form-group">
            <label for="code">Code</label>
            <textarea name="code" class="form-control" cols="30" rows="10" required>{{$code}}</textarea>
        </div>
        <!-- Submit Field -->
        <div class="form-group text-center row">
            <div class="col-md-6 col-md-offset-3">
                <button class="btn btn-success btn-block">Analizēt kodu</button>
            </div>
        </div>
        </form>
    </div>
</div>
@if(isset($keywords) && count($keywords)>0)
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <strong>Keywords table</strong>
            <table class="table table-bordered table-hover">
                <thead>
                    <th>#</th>
                    <th>Element</th>
                </thead>
                <tbody>
                    @foreach($keywords as $key => $value)
                        <tr>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@if(isset($splitters) && count($splitters)>0)
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <strong>Splitters table</strong>
            <table class="table table-bordered table-hover">
                <thead>
                <th>#</th>
                <th>Element</th>
                </thead>
                <tbody>
                @foreach($splitters as $key => $value)
                    <tr>
                        <td>{{$key}}</td>
                        <td>{{$value}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@if(isset($literals) && count($literals)>0)
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <strong>Literals table</strong>
            <table class="table table-bordered table-hover">
                <thead>
                <th>#</th>
                <th>Element</th>
                </thead>
                <tbody>
                @foreach($literals as $key => $value)
                    <tr>
                        <td>{{$key}}</td>
                        <td>{{$value}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@if(isset($variables) && count($variables)>0)
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <strong>Variables table</strong>
            <table class="table table-bordered table-hover">
                <thead>
                <th>#</th>
                <th>Element</th>
                </thead>
                <tbody>
                @foreach($variables as $key => $value)
                    <tr>
                        <td>{{$key}}</td>
                        <td>{{$value}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@if(isset($codeTable))
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <strong>Code table</strong>
            <table class="table table-bordered table-hover">
                <thead>
                    <th>#</th>
                    <th>Element</th>
                    <th>Type</th>
                    <th>Type #</th>
                </thead>
                <tbody>
                    @foreach($codeTable as $key => $value)
                        <tr>
                            <td>{{$key}}</td>
                            <td>{{$value['element']}}</td>
                            <td>{{$value['type']}}</td>
                            <td>{{$value['type_id']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection