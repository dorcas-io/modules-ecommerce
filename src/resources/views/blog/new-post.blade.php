@extends('modules-ecommerce::blog.layouts.blog')
@section('head_css')

@endsection
@section('body_main_content_container_body')
    <div class="col_one_fourth">

        <div class="list-group">
            <a href="{{ route('blog') }}" class="list-group-item list-group-item-action clearfix"><i class="icon-laptop2 float-left"></i> Posts</a>
            <a href="{{ url('/logout') }}" class="list-group-item list-group-item-action clearfix"><i class="icon-line2-logout float-left"></i> Logout</a>
        </div>
    </div>
    <div class="col_three_fourth col_last">
        @include('blog.layouts.blocks.ui-response-alert')
        <h3>@{{ title }}</h3>
        <form action="" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="title">Post Title</label>
                    <input id="title" name="title" placeholder="Post Title..." class="form-control input-lg" type="text"
                           v-model="post.title" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="summary">Summary</label>
                    <input id="summary" name="summary" placeholder="Snippet to be displayed in previews."
                           class="form-control input-lg" type="text" v-model="post.summary" required maxlength="">
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="content">Categories</label>
                <select class="select2 form-control input-lg" name="categories[]" id="categories" multiple="multiple"
                        data-placeholder="Select the post categories..." v-model="post.categories"
                        data-allow-clear="true">
                    <option v-for="category in categories" :key="category.id" v-bind:selected="post.categories.indexOf(category.id) >= 0"
                            :value="category.id">@{{ category.name }}</option>
                </select>
            </div>
            <div class="form-row">
               <div class="form-group col-md-6">
                   <label for="image">Banner Image (Recommended: 860 x 400)</label>
                   <input class="form-control-file" id="image" name="image" type="file" accept="image/*">
               </div>
            </div>
            <div class="row" v-if="post.image_url.length > 0">
                <div class="col_full">
                    <input class="form-check-input mt-4" id="retain_photo" checked
                           name="retain_photo" value="1" type="checkbox">
                    <label class="form-check-label mt-4" for="remove_photo">
                        Retain Photo
                    </label>
                    <img :src="post.image_url" class="center-align img-thumbnail img-responsive" width />
                </div>
            </div>
            <div class="form-group col-sm-12">
                <label for="content">Content</label>
                <textarea class="form-control summernote" name="content" id="post_content" v-model="post.content"
                          rows="10" placeholder="Post Content..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <input type="text" class="form-control single-date-picker-to-future input-lg" placeholder="When to publish?"
                           id="publish_at" name="publish_at" v-bind:required="!post.is_published"
                           v-show="!post.is_published" v-model="post.publish_at">
                    <input class="form-check-input mt-4" v-model="post.is_published" id="is_published"
                           name="is_published" value="1" type="checkbox">
                    <label class="form-check-label mt-4" for="is_published">
                        Publish Immediately
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="button button-rounded button-reveal button-large button-border tright mt-3">
                        <i class="icon-save"></i><span>@{{ buttonText }}</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="line"></div>

    </div>
@endsection
@section('body_js')
    <script>
        new Vue({
            el: '#content',
            data: {
                categories: {!! json_encode(!empty($categories) ? $categories : []) !!},
                post: {
                    title: '{{ old('title', '') }}',
                    summary: '{{ old('summary', '') }}',
                    content: '{{ old('content', '') }}',
                    is_published: true,
                    publish_at: '',
                    categories: [],
                    image_url: ''
                },
                editPost: {!! json_encode(!empty($post) ? $post : []) !!},
                title: 'New Post'
            },
            mounted: function () {
                if (typeof this.editPost.id !== 'undefined') {
                    this.title = 'Edit Post';
                    this.post.title = this.editPost.title;
                    this.post.summary = this.editPost.summary;
                    this.post.summary = this.editPost.summary;
                    this.post.content = this.editPost.content;
                    $('#post_content').summernote('code', this.post.content);
                    if (typeof this.editPost.categories !== 'undefined') {
                        this.post.categories = this.editPost.categories.data.map(function (e) { return e.id; });
                    }
                    if (typeof this.editPost.media !== 'undefined') {
                        this.post.image_url = this.editPost.media.data.url;
                    }
                }
            },
            computed: {
                buttonText: function () {
                    return !this.post.is_published ? 'Publish Later' : 'Publish Now';
                }
            }
        });

        new Vue({
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
                    window.location = '/blog?q=' + encodeURIComponent(this.search_term);
                }
            }
        });
    </script>
@endsection