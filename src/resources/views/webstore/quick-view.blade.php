<div class="single-product shop-quick-view-ajax clearfix" id="product-quick-view">
    <div class="ajax-modal-title">
        <h2>{{ $product->name }}</h2>
    </div>
    <div class="product modal-padding clearfix">
        <div class="col_half nobottommargin">
            <div class="product-image">
                <div class="fslider" data-pagi="false">
                    <div class="flexslider">
                        <div class="slider-wrap">
                            @if (!empty($product->images->data))
                                @foreach ($product->images->data as $image)
                                    <div class="slide">
                                        <a href="{{ $image->url }}" title="{{ $product->name .' - Photo #' . $loop->iteration }}">
                                            <img src="{{ $image->url }}" alt="{{ $product->name }}">
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <div class="slide">
                                    <a href="{{ cdn('apps/webstore/images/products/1.jpg') }}" title="{{ $product->name }}">
                                        <img src="{{ cdn('apps/webstore/images/products/1.jpg') }}" alt="{{ $product->name }}">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!--<div class="sale-flash">Sale!</div>-->
            </div>
        </div>
        <div class="col_half nobottommargin col_last product-desc">
            @if (empty($product->prices) && empty($product->prices->data))
                <div class="product-price"><ins>{{ $product->default_currency }}{{ $product->default_unit_price->formatted }}</ins></div>
            @else
                <div class="product-price"><ins>{{ $product->prices->data[0]->currency }}{{ $product->prices->data[0]->unit_price->formatted }}</ins></div>
            @endif
            <div class="clear"></div>
            <div class="line"></div>
            <!-- Product Single - Quantity & Cart Button
            ============================================= -->
            <form class="cart nobottommargin clearfix" method="post">
                @if ($product->inventory > 0)
                    <div class="quantity clearfix">
                        <input type="button" value="-" class="minus">
                        <input type="text" step="1" min="1" name="quantity" value="1" title="Qty" class="qty" size="4"
                               max="{{ $product->inventory }}" />
                        <input type="button" value="+" class="plus">
                    </div>
                    <button type="submit" class="add-to-cart button nomargin">Add to cart</button>
                @else
                    <span class="text-danger" style="font-weight: bold;">OUT OF STOCK</span>
                @endif
            </form><!-- Product Single - Quantity & Cart Button End -->
            <div class="clear"></div>
            <div class="line"></div>
            <p>{{ $product->description }}</p>
            <div class="panel panel-default product-meta nobottommargin">
                <div class="panel-body">
                    <span itemprop="productID" class="sku_wrapper">SKU: <span class="sku">{{ strtoupper($product->id) }}</span></span>
                    <span class="posted_in">Store Owner: <a href="{{ $storeDomain }}" rel="tag">{{ $storeOwner->name }}</a>.</span>
                    <span class="posted_in">Categories:
                        @foreach ($product->categories->data as $category)
                            <a href="{{ route('webstore.categories.single', [$category->slug]) }}" rel="tag">{{ $category->name }}</a>.
                        @endforeach
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var product = {!! json_encode($product) !!};
    var price = {!! json_encode($price ?: []) !!};
    $(function(){
        $('input[type=button].plus').on('click', function () {
            var $input = $(this).siblings('input[type=text].qty');
            if ($input === null) {
                throw '';
            }
            var max = parseInt($input.attr('max'), 10);
            var current = parseInt($input.val(), 10);
            $input.val(current < max ? current + 1 : current);
        });
        $('input[type=button].minus').on('click', function () {
            var $input = $(this).siblings('input[type=text].qty');
            if ($input === null) {
                throw '';
            }
            var current = parseInt($input.val(), 10);
            $input.val(current > 0 ? current - 1 : 0);
        })
        $('form.cart').on('submit', function () {
            var photo = '{{ cdn('apps/webstore/images/products/1.jpg') }}';
            if (typeof product.images !== 'undefined' && typeof product.images.data !== 'undefined' && product.images.data.length > 0) {
                photo = product.images.data[0].url;
            }
            var salePrice = typeof price !== 'undefined' && typeof price.currency !== 'undefined' ? price.unit_price : product.default_unit_price;
            var quantity = $(this).find('input[type=text].qty').val();
            addToCart(product.id, product.name, salePrice.raw, photo, quantity);
            return false;
        });
    });
</script>