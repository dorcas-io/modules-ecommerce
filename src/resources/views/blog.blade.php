@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9" id="ecommerce-blog">

	    <div class="row row-cards row-deck" id="blog-statistics">
	    	<div class="col-6 col-sm-4 col-lg-2">
	    		<div class="card p-3">
	    			<div class="d-flex align-items-center">
	    				<span class="stamp stamp-md {{ empty($subdomain) ? 'bg-red' : 'bg-green' }} mr-3"><i class="fe fe-grid"></i></span>
	    				<div>
	    					<h4 class="m-0"><a href="javascript:void(0)">{{ empty($subdomain) ? 'Disabled' : 'Enabled' }}</a></h4>
	    					<small class="text-muted">Blog Status</small>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    	<div class="col-6 col-sm-4 col-lg-2">
	    		<div class="card p-3">
	    			<div class="d-flex align-items-center">
	    				<span class="stamp stamp-md bg-blue mr-3"><i class="fe fe-grid"></i></span>
	    				<div>
	    					<h4 class="m-0"><a href="javascript:void(0)">{{ $postsCount ? number_format($postsCount) : 'No Posts' }}</a></h4>
	    					<small class="text-muted">Posts</small>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    	<div class="col-6 col-sm-4 col-lg-2">
	    		<div class="card p-3">
	    			<div class="d-flex align-items-center">
	    				<span class="stamp stamp-md bg-blue mr-3"><i class="fe fe-grid"></i></span>
	    				<div>
	    					<h4 class="m-0"><a href="javascript:void(0)">Blog Domain</a></h4>
	    					<small class="text-muted">{{ !empty($subdomain) ? $subdomain . '/blog' : 'Not Reserved' }}</small>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>



        <div class="row col-md-12">
            @if (!empty($subdomain))
                <div class="col-md-6">
                    <form action="" method="post" class="col s12">
                        {{ csrf_field() }}
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <input class="form-control" id="blog_name" name="blog_name" type="text"
                                           class="validate" v-model="blog_settings.blog_name">
                                    <label class="form-label" for="blog_name">Blog Name</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <input class="form-control" id="blog_instagram_id" name="blog_instagram_id" type="text"
                                           class="validate" v-model="blog_settings.blog_instagram_id">
                                    <label class="form-label" for="blog_instagram_id">Blog Instagram ID</label>
                                </div>
                                <div class="col-md-12 form-group">
                                    <input class="form-control" id="blog_twitter_id" name="blog_twitter_id" type="text"
                                           class="validate" v-model="blog_settings.blog_twitter_id">
                                    <label class="form-label" for="blog_twitter_id">Blog Twitter ID</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <input class="form-control" id="blog_facebook_page" name="blog_facebook_page" type="url"
                                           class="validate" v-model="blog_settings.blog_facebook_page">
                                    <label class="form-label" for="blog_facebook_page">Facebook Page</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <input class="form-control" id="blog_terms_page" name="blog_terms_page" type="url"
                                           class="validate" v-model="blog_settings.blog_terms_page">
                                    <label class="form-label" for="blog_terms_page">Terms of Service URL</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                Save Settings
                            </button>
                    </form>
                </div>
            @endif
            <div class="col-md-6">
                <div class="row">
                    <h5>Categories</h5>
                    <!-- <blog-category v-for="(category, index) in categories" class="m6 l6" :key="category.id"
                                   :index="index" :category="category"
                                   v-bind:show-delete="true" v-on:update="update"
                                   v-on:remove="decrement"></blog-category> -->


	                <div class="card">
	                    <div class="card-header">
	                        <h3 class="card-title">Categories</h3>
	                    </div>
	                    <div class="card-body">
			                <div class="table-responsive" v-if="categories.length > 0">
			                    <table class="table card-table table-striped table-vcenter">
			                        <tbody>
			                            <tr v-for="category in categories" :key="category.id">
			                                <td>
	                                            <p>@{{ category.name }}</p>
	                                            <small class="text-muted">@{{ category.posts_count }}</small>
	                                        </td>
			                                <td>
			                                	<a href="#" v-on:click.prevent="edit" class="btn btn-sm btn-outline-secondary ml-3">Edit</a>
			                                	<a href="#" v-on:click.prevent="deleteField" class="btn btn-sm btn-outline-danger ml-3">Delete</a>
			                                </td>
			                            </tr>
			                        </tbody>
			                    </table>
			                </div>
						    <div class="row row-cards row-deck" v-if="applications.length === 0 && !apps_fetching">
						        <div class="col-sm-12">
						            @component('layouts.blocks.tabler.empty-card')
						                @slot('buttons')
						                    <div class="btn-list text-center">
						                        <a href="{{ safe_href_route('app-store-main') ? route('app-store-main').'#apps_apps-store' : '#' }}" class="btn btn-primary">Explore App Store</a>
						                    </div>
						                @endslot
						                You have no Apps installed
						            @endcomponent
						        </div>
						    </div>
	                        <div class="row" v-if="applications.length === 0 && apps_fetching">
	                          <div class="loader"></div>
	                          <div>Loading Apps</div>
	                        </div>

	                    </div>
	                </div>



                    <div class="col s12" v-if="categories.length  === 0">
				        @component('layouts.blocks.tabler.empty-fullpage')
				            @slot('title')
				                No Blog Categories
				            @endslot
				            Add one or more categories to classify your blog posts.
				            @slot('buttons')
	                            <a class="btn btn-primary btn-sm" href="#" v-on:click.prevent="newCategory">
                                    Add Category
                                </a>
				            @endslot
				        @endcomponent

                    </div>
                </div>
            </div>
            @if (empty($subdomain))
                <div class="col-md-6">
			        @component('layouts.blocks.tabler.empty-fullpage')
			            @slot('title')
			                No Subdomainn
			            @endslot
			            Reserve your <strong>dorcas sub-domain</strong> to proceed with enabling your blog.
			            @slot('buttons')
                            <a class="btn btn-primary" href="{{ route('ecommerce-domains') }}">
                                Reserve SubDomain
                            </a>
			            @endslot
			        @endcomponent
                </div>
            @endif
        </div>



    </div>

</div>


@endsection
@section('body_js')
    <script type="text/javascript">
        function addCategory() {
            swal({
                    title: "New Category",
                    text: "Enter the name for the category:",
                    type: "input",
                    showCancelButton: true,
                    closeOnConfirm: false,
                    animation: "slide-from-top",
                    showLoaderOnConfirm: true,
                    inputPlaceholder: "e.g. Stationery"
                },
                function(inputValue){
                    if (inputValue === false) return false;
                    if (inputValue === "") {
                        swal.showInputError("You need to write something!");
                        return false
                    }
                    axios.post("/mec/ecommerce-blog-categories", {
                        name: inputValue
                    }).then(function (response) {
                        console.log(response);
                        vm.categories.push(response.data);
                        return swal("Success", "The blog category was successfully created.", "success");
                    })
                        .catch(function (error) {
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
                            return swal("Oops!", message, "warning");
                        });
                });
        }

        var vm = new Vue({
            el: '#ecommerce-blog',
            data: {
                blog_owner: {!! json_encode($business) !!},
                blog_settings: {!! json_encode($blogSettings) !!},
                categories: {!! json_encode($categories ?: [])  !!}
            },
            methods: {

		        edit: function () {
		            var context = this;
		            swal({
		                    title: "Update Category",
		                    text: "Enter new name [" + context.category.name + "]:",
		                    type: "input",
		                    showCancelButton: true,
		                    closeOnConfirm: false,
		                    animation: "slide-from-top",
		                    showLoaderOnConfirm: true,
		                    inputPlaceholder: "Custom Field Name"
		                },
		                function(inputValue){
		                    if (inputValue === false) return false;
		                    if (inputValue === "") {
		                        swal.showInputError("You need to write something!");
		                        return false
		                    }
		                    axios.put("/xhr/ecommerce/blog/categories/"+context.category.id, {
		                        name: inputValue,
		                        update_slug: true
		                    }).then(function (response) {
		                        console.log(response);
		                        context.$emit('update', context.index, response.data);
		                        return swal("Success", "The category name was successfully updated.", "success");
		                    })
		                        .catch(function (error) {
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
		                            return swal("Oops!", message, "warning");
		                        });
		                });
		        },
		        deleteField: function () {
		            var context = this;
		            swal({
		                title: "Are you sure?",
		                text: "You are about to delete the category (" + context.category.name + ").",
		                type: "warning",
		                showCancelButton: true,
		                confirmButtonColor: "#DD6B55",
		                confirmButtonText: "Yes, delete it!",
		                closeOnConfirm: false,
		                showLoaderOnConfirm: true
		            }, function() {
		                axios.delete("/xhr/ecommerce/blog/categories/" + context.category.id)
		                    .then(function (response) {
		                        console.log(response);
		                        context.$emit('remove', context.index);
		                        return swal("Deleted!", "The category was successfully deleted.", "success");
		                    })
		                    .catch(function (error) {
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


                decrement: function (index) {
                    console.log('Removing: ' + index);
                    this.categories.splice(index, 1);
                },
                newCategory: function () {
                    addCategory();
                },
                update: function (index, category) {
                    console.log('Updating: ' + index);
                    this.categories.splice(index, 1, category);
                }
            }
        });


        new Vue({
            el: '#sub-menu-action',
            data: {
            	blogUrl: {{ $blogUrl or '#' }}
            },
            methods: {
                newField: function () {
                    addCategory();
                }
            }
        });
    </script>
@endsection

