<header id="header" class="full-header">
    <div id="header-wrap">
        <div class="container clearfix">
            <div id="primary-menu-trigger"><i class="icon-reorder"></i></div>
            <!-- Logo
            ============================================= -->
            <div id="logo">
                <a href="{{ route('blog') }}" class="standard-logo">
                    <img src="{{ !empty($blogOwner->logo) ? $blogOwner->logo : cdn('images/icon-only.png') }}"
                         alt="{{ $blogOwner->name }}" style="max-width: 126px;">
                </a>
                <a href="{{ route('blog') }}" class="retina-logo">
                    <img src="{{ !empty($blogOwner->logo) ? $blogOwner->logo : cdn('images/icon-only.png') }}"
                         alt="{{ $blogOwner->name }}"  style="max-width: 126px;">
                </a>
            </div><!-- #logo end -->

            <!-- Primary Navigation
            ============================================= -->
            <nav id="primary-menu">

                <ul>
                    <li><a href="{{ route('blog') }}"><div>Home</div></a></li>
                    @if (!empty($blogCategories))
                        <li>
                            <a href="{{ route('blog.categories') }}"><div>Categories</div></a>
                            <ul>
                                @foreach ($blogCategories as $category)
                                    <li>
                                        <a href="{{ route('blog.categories.single', [$category->slug]) }}">
                                            <div>{{ $category->name }}</div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                    @if (!empty($blogAdministrator))
                        <li><a href="{{ route('blog.admin.new-post') }}"><div>New Post</div></a></li>
                    @endif
                </ul>

                <!-- Top Search
                ============================================= -->
                <!-- <div id="top-search">
                    @if (empty($categorySlug))
                        <a href="#" id="top-search-trigger"><i class="icon-search3"></i><i class="icon-line-cross"></i></a>
                    @endif
                    <form action="" method="get" v-on:submit.prevent="searchBlog">
                        <input type="text" name="q" class="form-control" v-model="search_term" placeholder="Type &amp; Hit Enter..">
                    </form>
                </div> -->

            </nav><!-- #primary-menu end -->

        </div>

    </div>

</header><!-- #header end -->