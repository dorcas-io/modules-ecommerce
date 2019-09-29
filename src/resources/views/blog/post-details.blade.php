@extends('blog.layouts.blog')
@section('head_meta')
    <meta property="og:site_name" content="Dorcas Hub" />
    <meta property="og:url" content="{{ route('blog.posts.details', [$post->slug]) }}" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $post->title }}" />
    <meta property="og:description" content="{{ $post->summary }}" />
    @if (!empty($post->media['data']))
        <meta property="og:image" content="{{ $post->media['data']['url'] }}" />
    @endif
    <meta property="twitter:card" content="summary" />
    @if (!empty($blogSettings['blog_twitter_id']))
        <meta property="twitter:site" content="{{ '@' . $blogSettings['blog_twitter_id'] }}" />
    @endif
@endsection
@section('body_main_content_container_body')
    <div class="postcontent nobottommargin clearfix">
        <div class="single-post nobottommargin">
            <!-- Single Post
            ============================================= -->
            <div class="entry clearfix">
                <!-- Entry Title
                ============================================= -->
                <div class="entry-title">
                    <h2>@{{ post.title }}</h2>
                </div><!-- .entry-title end -->
                <!-- Entry Meta
                ============================================= -->
                <ul class="entry-meta clearfix">
                    <li><i class="icon-calendar3"></i> @{{ posted_at.format('DD MMM, YYYY') }}</li>
                    <li><a href="#"><i class="icon-user"></i> @{{ post.posted_by.data.firstname + ' ' + post.posted_by.data.lastname }}</a></li>
                    <li v-if="typeof post.media !== 'undefined' && post.media.data.id !== 'undefined'"><a href="#"><i class="icon-camera-retro"></i></a></li>
                    <li v-if="showAdminButtons"><a v-bind:href="'/admin-blog/' + post.id + '/edit'"><i class="icon-edit"></i> Edit</a></li>
                    <li v-if="showAdminButtons"><a href="#" v-on:click.prevent="deletePost"><i class="icon-trash"></i> Delete</a></li>
                </ul><!-- .entry-meta end -->

                <!-- Entry Image
                ============================================= -->
                <div v-if="typeof post.media !== 'undefined' && typeof post.media.data.url !== 'undefined'" class="entry-image">
                    <a href="#"><img :src="post.media.data.url" :alt="post.media.data.title" /></a>
                </div><!-- .entry-image end -->

                <!-- Entry Content
                ============================================= -->
                <div class="entry-content notopmargin" v-html="post.content">
                    @{{ post.content }}
                    <!-- Post Single - Content End -->
                    <div class="clear"></div>
                    <!-- Tag Cloud
                    ============================================= -->
                    <div class="tagcloud clearfix bottommargin topmargin-lg" v-if="typeof post.categories !== 'undefined'">
                        <a :href="'/blog/categories/' + cat.slug" v-for="cat in post.categories.data" :key="cat.id">@{{ cat.name }}</a>
                    </div><!-- .tagcloud end -->

                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>Posted by <a href="#">@{{ post.posted_by.data.firstname + ' ' + post.posted_by.data.lastname }}</a></strong></div>
            </div>

            <!-- <div class="line"></div>

            <h4>Related Posts:</h4>
            <div class="related-posts clearfix">
                <div class="col_full nobottommargin">
                    <suggestion-blog-post v-for="(sPost, index) in posts" :key="sPost.id"
                                          v-if="index < 6 && sPost.id !== post.id" :post="sPost"
                                          :index="index" :show-admin-buttons="showAdminButtons"></suggestion-blog-post>
                </div>
            </div> -->
        </div>

    </div><!-- .postcontent end -->

    <!-- Sidebar
    ============================================= -->
    <div class="sidebar nobottommargin col_last clearfix">
        <div class="sidebar-widgets-wrap">
            <div class="widget clearfix" v-if="blog_categories.length > 0">
                <h4>Tag Cloud</h4>
                <div class="tagcloud">
                    <a :href="'/blog/categories/' + cat.slug" v-for="cat in blog_categories" :key="cat.id">@{{ cat.name }}</a>
                </div>
            </div>
        </div>
    </div><!-- .sidebar end -->
@endsection
@section('body_js')
    <script>
        var postView = new Vue({
            el: '#main_content_container',
            data: {
                is_posting: false,
                post: {!! json_encode($post) !!},
                blogOwner: {!! json_encode($blogOwner) !!},
                blog_settings: {!! json_encode($blogSettings) !!},
                showAdminButtons: {!! json_encode(!empty($blogAdministrator)) !!},
                posts: [],
                blog_categories: {!! json_encode($blogCategories ?: []) !!},
                base_url: "{{ config('dorcas-api.url') }}"
            },
            computed: {
                posted_at: function () {
                    var date = typeof this.post.publish_at !== 'undefined' && this.post.publish_at !== null ?
                        this.post.publish_at : this.post.created_at;
                    return moment(date);
                }
            },
            mounted: function () {
                this.searchBlogPosts();
            },
            updated: function () {
                SEMICOLON.initialize.lightbox();
            },
            methods: {
                deletePost: function (index) {
                    var context =  this;
                    swal({
                        title: "Delete this Post?",
                        text: "You are about to delete the post with title: " + context.post.title,
                        type: "error",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it.",
                        closeOnConfirm: false,
                        confirmButtonColor: "#DD6B55",
                        showLoaderOnConfirm: true
                    }, function() {
                        axios.delete("/admin-blog/xhr/posts/" + context.post.id)
                            .then(function (response) {
                                console.log(response.data);
                                context.posts.splice(index, 1);
                                window.location = '/blog';
                            }).catch(function (error) {
                            var message = '';
                            if (error.response) {
                                // The request was made and the server responded with a status code
                                // that falls out of the range of 2xx
                                var e = error.response.data.errors[0];
                                message = e.title;
                            } else if (error.request) {
                                // The request was made but no response was received
                                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                                // http.ClientRequest in node.js
                                message = 'The request was made but no response was received';
                            } else {
                                // Something happened in setting up the request that triggered an Error
                                message = error.message;
                            }
                            return swal("Delete Failed", message, "warning");
                        });
                    });
                },
                searchBlogPosts: function () {
                    var context = this;
                    this.posts = [];
                    axios.get(this.base_url + "/blog/" + this.blogOwner.id, {
                        params: {
                            search: context.search_term,
                            limit: 12,
                            page: context.page_number,
                            category_slug: context.category_slug
                        }
                    }).then(function (response) {
                        context.is_fetching = false;
                        context.posts = response.data.data;
                        context.meta = response.data.meta;
                    }).catch(function (error) {
                        var message = '';
                        context.is_fetching = false;
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            var e = error.response.data.errors[0];
                            message = e.title;
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            message = 'The request was made but no response was received';
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            message = error.message;
                        }
                        return swal("Oops!", message, "warning");
                    });
                }
            }
        });

        var headerView = new Vue({
            el: '#header',
            data: {
                search_term: '',
            },
            watch: {
                search_term: function (old_value, new_value) {
                    postView.search_term = new_value;
                }
            },
            methods: {
                searchProducts: function () {
                    window.location = '/blog?q=' + encodeURIComponent(this.search_term)
                }
            }
        });
    </script>
@endsection