@isset($cart)
  @if(count($cart->article_properties))
  @foreach($cart->article_properties as $article_property)
    <tr>
      <td>
        <figure class="itemside align-items-center">
          <div class="aside"><img src="{{ asset($article_property->article->thumb()) }}" class="img-sm"></div>
          <figcaption class="info">
            <a href="{{ route('front.articledetail', $article_property->article->id) }}" class="title text-dark">
              {{ $article_property->article->title }}
            </a>
            <p class="text-muted small">
              {{--@foreach($article_property->article->features as $value)
                {{ $value->feature->name . ': ' . $value->possible_value }}<br>
              @endforeach--}}
              @foreach($article_property->values as $value)
                {{ $value->property->name . ': ' . $value->possible_value }}<br>
              @endforeach
            </p>
          </figcaption>
        </figure>
      </td>
      <td class="p-27">
        <input class="form-control quantity-article mwi-80" type="number" value="{{ $article_property->pivot->quantity }}" 
          data-url="{{route('checkout.setitemquantity', $article_property->id) }}" data-id="{{ $article_property->id }}" 
          data-price="{{ $article_property->final_price }}">
      </td>
      @if(config('config.SHOW_PRICING'))
      <td>
        <div class="price-wrap">
          <div class="price total-price" id="total-{{ $article_property->id }}">{{ $article_property->pivot->line_total_formatted }}</div>
          <small class="text-muted">{{ $article_property->getPriceFormatted() }} c/u</small>
        </div> <!-- price-wrap .// -->
      </td>
      @endif
      <td class="d-none d-md-block p-30">
        <a href="#!" class="btn  btn-danger delete-btn " data-url="{{ route('checkout.removecartitem', $article_property->id) }}">Eliminar</a>
      </td>
    </tr>
  @endforeach
  @else
    <tr>
      <td class="text-center" colspan="3">No hay productos en el carrito</td>
    </tr>
  @endif
@else
  @if(session()->has('cart'))
    @if(count(session()->get('cart')->article_properties))
    @foreach(session()->get('cart')->article_properties as $article_property)
      <tr>
      <td>
        <figure class="itemside align-items-center">
          <div class="aside"><img src="{{ asset($article_property->article->thumb()) }}" class="img-sm"></div>
          <figcaption class="info">
            <a href="{{ route('front.articledetail', $article_property->article->id) }}" class="title text-dark">
              {{ $article_property->article->title }}
            </a>
            <p class="text-muted small">
              @foreach($article_property->article->features as $value)
                {{ $value->feature->name . ': ' . $value->possible_value }}<br>
              @endforeach
              @foreach($article_property->values as $value)
                {{ $value->property->name . ': ' . $value->possible_value }}<br>
              @endforeach
            </p>
          </figcaption>
        </figure>
      </td>
      <td class="p-27">
        <input class="form-control quantity-article  mwi-80" type="number" value="{{ $article_property->pivot->quantity }}" 
          data-url="{{route('checkout.setitemquantity', $article_property->id) }}" data-id="{{ $article_property->id }}"
          data-price="{{ $article_property->price }}">
      </td>
      @if(config('config.SHOW_PRICING'))
      <td>
        <div class="price-wrap">
          <div class="price total-price" id="total-{{ $article_property->id }}">{{ $article_property->pivot->line_total }}</div>
          <small class="text-muted">{{ $article_property->getPriceFormatted() }} c/u</small>
        </div> <!-- price-wrap .// -->
      </td>
      @endif
      <td class="d-none d-md-block p-30">
        <a href="#!" class="btn btn-light delete-btn" class="delete-btn" data-url="{{ route('checkout.removecartitem', $article_property->id) }}">Eliminar</a>
      </td> 
    </tr>

    @endforeach
    @else
    <tr>
      <td class="text-center" colspan="3">No hay productos en el carrito</td>
    </tr>
  @endif
   @endif
@endisset
