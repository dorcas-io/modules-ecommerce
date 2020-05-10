<footer id="footer" class="dark">
    <div id="copyrights">

        <div class="container clearfix">

            <div class="col_half">
                Copyrights &copy; {{ date('Y') }} All Rights Reserved by {{ $storeOwner->name }}<br>
                <div class="copyright-links"><a href="{{ $storeSettings['store_terms_page'] ?? '#' }}">Terms of Use</a> / <a href="#">Privacy Policy</a></div>
            </div>

            <div class="col_half col_last tright">
                <div class="fright clearfix">
                    @if (!empty($storeSettings['store_facebook_page']))
                        <a href="{{ $storeSettings['store_facebook_page'] }}" class="social-icon si-small si-borderless si-facebook">
                            <i class="icon-facebook"></i>
                            <i class="icon-facebook"></i>
                        </a>
                    @endif
                    @if (!empty($storeSettings['store_twitter_id']))
                        <a href="{{ 'https://www.twitter.com/' . $storeSettings['store_twitter_id'] }}"
                           class="social-icon si-small si-borderless si-twitter">
                            <i class="icon-twitter"></i>
                            <i class="icon-twitter"></i>
                        </a>
                    @endif
                    @if (!empty($storeSettings['store_instagram_id']))
                        <a href="{{ 'https://www.instagram.com/' . $storeSettings['store_instagram_id'] }}"
                           class="social-icon si-small si-borderless si-instagram">
                            <i class="icon-instagram2"></i>
                            <i class="icon-instagram2"></i>
                        </a>
                    @endif
                    <!--<a href="#" class="social-icon si-small si-borderless si-gplus">
                        <i class="icon-gplus"></i>
                        <i class="icon-gplus"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-borderless si-pinterest">
                        <i class="icon-pinterest"></i>
                        <i class="icon-pinterest"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-borderless si-vimeo">
                        <i class="icon-vimeo"></i>
                        <i class="icon-vimeo"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-borderless si-github">
                        <i class="icon-github"></i>
                        <i class="icon-github"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-borderless si-yahoo">
                        <i class="icon-yahoo"></i>
                        <i class="icon-yahoo"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-borderless si-linkedin">
                        <i class="icon-linkedin"></i>
                        <i class="icon-linkedin"></i>
                    </a>-->
                </div>

                <div class="clear"></div>
                @if (!empty($storeOwner->email))
                    <i class="icon-envelope2"></i> {{ $storeOwner->email }} <span class="middot">&middot;</span>
                @endif
                @if (!empty($storeOwner->phone))
                    <i class="icon-headphones"></i> {{ $storeOwner->phone }} <span class="middot">&middot;</span>
                @endif
            </div>

        </div>

    </div><!-- #copyrights end -->
</footer><!-- #footer end -->