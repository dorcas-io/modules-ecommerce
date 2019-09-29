<footer id="footer" class="dark">
    <div id="copyrights">

        <div class="container clearfix">

            <div class="col_half">
                Copyrights &copy; {{ date('Y') }} All Rights Reserved by {{ $blogOwner->name }}<br>
                <div class="copyright-links"><a href="{{ $blogSettings['blog_terms_page'] ?? '#' }}">Terms of Use</a> / <a href="#">Privacy Policy</a></div>
            </div>

            <div class="col_half col_last tright">
                <div class="fright clearfix">
                    @if (!empty($blogSettings['blog_facebook_page']))
                        <a href="{{ $blogSettings['blog_facebook_page'] }}" class="social-icon si-small si-borderless si-facebook">
                            <i class="icon-facebook"></i>
                            <i class="icon-facebook"></i>
                        </a>
                    @endif
                    @if (!empty($blogSettings['blog_twitter_id']))
                        <a href="{{ 'https://www.twitter.com/' . $blogSettings['blog_twitter_id'] }}"
                           class="social-icon si-small si-borderless si-twitter">
                            <i class="icon-twitter"></i>
                            <i class="icon-twitter"></i>
                        </a>
                    @endif
                    @if (!empty($blogSettings['blog_instagram_id']))
                        <a href="{{ 'https://www.instagram.com/' . $blogSettings['blog_instagram_id'] }}"
                           class="social-icon si-small si-borderless si-instagram">
                            <i class="icon-instagram2"></i>
                            <i class="icon-instagram2"></i>
                        </a>
                    @endif
                </div>
                <div class="clear"></div>
                @if (!empty($blogOwner->email))
                    <i class="icon-envelope2"></i> {{ $blogOwner->email }} <span class="middot">&middot;</span>
                @endif
                @if (!empty($blogOwner->phone))
                    <i class="icon-headphones"></i> {{ $blogOwner->phone }} <span class="middot">&middot;</span>
                @endif
            </div>

        </div>

    </div><!-- #copyrights end -->
</footer><!-- #footer end -->