@extends('layouts.app')

@section('title', 'Add currency')

@section('content')
<div class="card">
    <div class="card-body">
        <form role="form" method="POST" action="{{ route('addLotFromForm') }}">
            @csrf
            
            <div class="form-group">
                <label for="currency" class="col-md-3 control-label">Currency</label>
                <div class="col-md-3">
                    <select class="form-control {{ $errors->has('title') ? ' has-error' : '' }}"
                        id="currency" name="currency-id" autofocus required>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency['id'] }}">{{ $currency['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <label for="price" class="col-md-3 control-label">Price</label>
                <div class="col-md-3">
                    <input type="text" id="price" name="price" value="{{ old('price') }}">
                </div>

                <label class="col-md-3 control-label">Start sell</label>
                <div class="col-md-3">
                    <input type="date" name="date-open" id="date-open" 
                        value="{{ old('date-open') ?? now()->format('Y-m-d') }}">
                    <input type="time" name="time-open" id="time-open"
                        value="{{ old('time-open') ?? now()->format('H:i:s') }}">
                </div>

                <label class="col-md-3 control-label">End sell</label>
                <div class="col-md-3">
                    
                    <input type="date" name="date-close" id="date-close" value="{{ old('date-close') ?? now()->format('Y-m-d') }}">
                    <input type="time" name="time-close" id="time-close" value="{{ old('time-close') ?? now()->addHours(1)->format('H:i:s')}}">
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-3 col-md-offset-1">
                    <button type="submit" class="btn btn-primary">
                        Add lot
                    </button>
                </div>
            </div>
        </form>

        <p>{{ $errormsg }}</p>
    </div>
</div>
@endsection
