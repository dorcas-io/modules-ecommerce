@extends('modules-ecommerce::blog.layouts.blog')
@section('body_main_content_container_body')
    <div class="nobottommargin col_last" v-bind:class="{'postcontent': blog_categories.length > 0}">
        <div class="progress" v-if="is_posting">
            <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar"
                 aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                <span class="sr-only">Processing...</span>
            </div>
        </div>
        <div id="posts" class="post-timeline clearfix" v-if="posts.length > 0">
            <blog-main-post v-for="(post, index) in posts" :key="post.id" :post="post" :index="index"
                       :show-admin-buttons="showAdminButtons" v-on:delete-post="deletePost"></blog-main-post>

            <div class="col_full">
                <!--TODO: Handle situations where the number of pages > 10; we need to limit the pages displayed in those cases -->
                <ul class="pagination pagination-lg" v-if="typeof meta.pagination !== 'undefined' && meta.pagination.total_pages > 1">
                    <li><a href="#" v-on:click.prevent="changePage(1)">«</a></li>
                    <li v-for="n in meta.pagination.total_pages" v-bind:class="{active: n === page_number}">
                        <a href="#" v-on:click.prevent="changePage(n)" v-if="n !== page_number">@{{ n }}</a>
                        <span v-else>@{{ n }}</span>
                    </li>
                    <li><a href="#" v-on:click.prevent="changePage(meta.pagination.total_pages)">»</a></li>
                </ul>
            </div>
        </div><!-- #blog end -->

        <div class="col_full nobottommargin" v-if="posts.length === 0 && !is_fetching">
            <div class="feature-box center media-box fbox-bg">
                <div class="fbox-desc">
                    <h3>No posts at the moment!<span class="subtitle">The blog owner has not posted any articles to their blog.</span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="sidebar nobottommargin" v-if="blog_categories.length > 0">
        <div class="sidebar-widgets-wrap">
            <div class="widget widget-filter-links clearfix">
                <h4>Select Category</h4>
                <ul class="custom-filter" data-container="#posts" data-active-class="active-filter">
                    <li class="widget-filter-reset" v-bind:class="{'active-filter': category_slug.length === 0}">
                        <a href="{{ route('blog') }}" data-filter="*">Clear</a>
                    </li>
                    <li v-for="category in blog_categories" :key="category.id" v-bind:class="{'active-filter': category_slug.length > 0 && category_slug == category.slug}">
                        <a v-bind:href="'{{ route('blog.categories') }}' + '/' + category.slug"
                           v-bind:data-filter="'.sf-cat-' + category.id">@{{ category.name }}</a>
                        <span>@{{ category.posts_count }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
@section('body_js')
    <script>
        var timelineView = new Vue({
            el: '#main_content_container',
            data: {
                is_fetching: false,
                is_posting: false,
                posts: [],
                meta: [],
                category_slug: {!! json_encode($categorySlug ?: '') !!},
                search_term: '{{ $defaultSearch }}',
                blogOwner: {!! json_encode($blogOwner) !!},
                base_url: "{{ config('dorcas-api.url') }}",
                page_number: 1,
                blog_settings: {!! json_encode($blogSettings) !!},
                blog_categories: {!! json_encode($blogCategories ?: []) !!},
                showAdminButtons: {!! json_encode(!empty($blogAdministrator)) !!}
            },
            mounted: function () {
                this.searchBlogPosts();
            },
            updated: function () {
                SEMICOLON.initialize.lightbox();
            },
            watch: {
                search_term: function (old_search, new_search) {
                    if (old_search.toLowerCase() === new_search.toLowerCase()) {
                        return;
                    }
                    this.page_number = 1;
                }
            },
            methods: {
                deletePost: function (index) {
                    var post = typeof this.posts[index] !== 'undefined' ? this.posts[index] : null;
                    if (post === null) {
                        return;
                    }
                    var context =  this;
                    swal({
                        title: "Delete Post?",
                        text: "You are about to delete the post with title: " + post.title,
                        type: "error",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it.",
                        closeOnConfirm: false,
                        confirmButtonColor: "#DD6B55",
                        showLoaderOnConfirm: true
                    }, function() {
                        axios.delete("/admin-blog/xhr/posts/" + post.id)
                            .then(function (response) {
                                console.log(response.data);
                                context.posts.splice(index, 1);
                                return swal("Post Removed", "The post was successfully deleted from your blog.", "success");
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
                changePage: function (number) {
                    this.page_number = parseInt(number, 10);
                    this.searchBlogPosts();
                },
                searchBlogPosts: function () {
                    var context = this;
                    this.is_fetching = true;
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
                search_term: ''
            },
            watch: {
                search_term: function (old_value, new_value) {
                    timelineView.search_term = new_value;
                }
            },
            methods: {
                searchBlog: function () {
                    timelineView.searchBlogPosts();
                }
            }
        });


Vue.component('blog-main-post', {
    template: '<div class="entry clearfix">' +
    '                <div class="entry-timeline">' +
    '                    @{{ date_day }}<span>@{{ date_month }}</span>' +
    '                    <div class="timeline-divider"></div>' +
    '                </div>' +
    '                <div class="entry-image">' +
    '                    <a v-if="typeof media.id !== \'undefined\'" v-bind:href="media.url" data-lightbox="image"><img class="image_fade" v-bind:src="media.url" :alt="media.title || \'Post Image\'"></a>' +
    '                </div>' +
    '                <div class="entry-title">' +
    '                    <h2><a v-bind:href="\'/posts/\' + post.slug">@{{ post.title }}</a></h2>' +
    '                </div>' +
    '                <ul class="entry-meta clearfix">' +
    '                    <li><a href="#"><i class="icon-user"></i> @{{ posted_by.firstname + " " + posted_by.lastname }}</a></li>' +
    '                    <li v-if="post.categories.data.length  > 0"><i class="icon-folder-open"></i><a v-for="(category, index) in post.categories.data" :key="category.id" v-if="index < 2" v-bind:href="\'/categories/\' + category.slug">@{{ index > 0 ? ", " : "" }}@{{ category.name }}</a></li>' +
    '                    <li v-if="typeof media.id !== \'undefined\'"><a href="#"><i class="icon-camera-retro"></i></a></li>' +
    '                    <li v-if="showAdminButtons"><a v-bind:href="\'/admin-blog/\' + post.id + \'/edit\'"><i class="icon-edit"></i> Edit</a></li>' +
    '                    <li v-if="showAdminButtons"><a href="#" v-on:click.prevent="deletePost" class="text-danger"><i class="icon-trash"></i> Delete</a></li>' +
    '                </ul>' +
    '                <div class="entry-content">' +
    '                    <p>@{{ post.summary === null ? "Click Read More to get more details" : post.summary }}</p>' +
    '                    <a v-bind:href="\'/posts/\' + post.slug" class="more-link">Read More</a>' +
    '                </div>' +
    '            </div>',
    data: function () {
        return {
            posted_by: {},
            media: {},
            image_url: null,
            video_url: null
        };
    },
    props: {
        post: {
            type: Object,
            required: true
        },
        index: {
            type: Number,
            required: true
        },
        showAdminButtons: {
            type: Boolean,
            required: false,
            default: function () {
                return false;
            }
        }
    },
    computed: {
        posted_at: function () {
            return typeof this.post.publish_at !== 'undefined' && this.post.publish_at !== null ?
                this.post.publish_at : this.post.created_at;
        },
        date_day: function () {
            return moment(this.posted_at).format('DD');
        },
        date_month: function () {
            return moment(this.posted_at).format('MMM');
        }
    },
    mounted: function () {
        if (typeof this.post.posted_by !== 'undefined' && typeof this.post.posted_by.data !== 'undefined') {
            this.posted_by = this.post.posted_by.data;
        }
        if (typeof this.post.media !== 'undefined' && typeof this.post.media.data !== 'undefined') {
            this.media = this.post.media.data;
            this.image_url = this.media.url;
        }
    },
    methods: {
        deletePost: function () {
            this.$emit('delete-post', this.index);
        }
    }
});



    </script>
@endsection