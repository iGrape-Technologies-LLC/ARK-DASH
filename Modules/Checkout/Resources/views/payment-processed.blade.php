@extends('front.layouts.app')

@section('title')
  @if(!is_null(request()->query('status')) && (request()->query('status') == 'success' || request()->query('status') == 'approved'))
    @lang('checkout::process.purchase_finished')
  @else
    @lang('checkout::process.purchase_incomplete')
  @endif
@endsection

@section('content')
<div class="error section-padding">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
        <div class="error-content">
          <div class="error-message">
            @if(!is_null(request()->query('status')) && (request()->query('status') == 'success' || 
            request()->query('status') == 'approved'))
              <h2>@lang('checkout::process.ready')</h2>
              <h3>@lang('checkout::process.purchase_recieved')</h3>
            @else
              <h2>@lang('checkout::process.oops')</h2>
              <h3>@lang('checkout::process.porcess_incomplete')</h3>
            @endif
          </div>
          <div class="description">
            <span>@lang('general.back_home', ['url' => route('front.pages.home')])</span>
          </div>
        </div>
      </div>
    </div>      
  </div>
</div>
@endsection
