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
                        id="currency" name="currencies[]" autofocus required>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency }}">{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>

                <label for="price" class="col-md-3 control-label">Price</label>
                <div class="col-md-3">
                    <input type="number" id="price" name="price" value="{{ old('price') }}">
                </div>

                <label class="col-md-3 control-label">Start sell</label>
                <div class="col-md-3">
                    <input type="date" id="date-open"
                        value="{{ old('date-open') ?? now()->format('Y-m-d') }}">
                    <input type="time" id="time-open"
                        value="{{ old('time-open') ?? now()->format('H:i:s') }}">
                </div>

                <label class="col-md-3 control-label">End sell</label>
                <div class="col-md-3">
                    
                    <input type="date" id="date-close" value="{{ old('date-close') }}">
                    <input type="time" id="time-close" value="{{ old('time-close') }}">
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
    </div>
</div>
@endsection
