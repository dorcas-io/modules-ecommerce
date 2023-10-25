@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9">
	    
        <div class="container" id="ecommerce-adverts">
	        <div class="row row-cards row-deck" v-show="adverts !== null && adverts.length > 0">
	            <!-- <advert-card v-for="(ad, index) in adverts" :key="ad.id" :advert="ad" :index="index" v-on:edit-advert="editAdvert" v-on:delete-advert="deleteAdvert"></advert-card> -->

	              <div class="col-md-6" v-for="(ad, index) in adverts" :key="ad.id" :advert="ad" :index="index">
	                <div class="card">
	                  <a href="#"><img class="card-img-top" style="height: auto !important; width:auto !important; max-width: 100% !important; max-height: 150px !important;" v-bind:src="ad.image_url" v-bind:alt="ad.title + ' image'"></a>
	                  <div class="card-body d-flex flex-column">
	                    <h4><a href="#">@{{ ad.title }}</a></h4>
	                    <div class="d-flex align-items-center">
	                    	<a v-if="typeof ad.redirect_url !== 'undefined' && ad.redirect_url !== null" v-bind:href="ad.redirect_url" target="_blank" class="btn btn-primary btn-sm">Open Link</a>&nbsp;
	                    	<a href="#" class="btn btn-secondary btn-sm" v-on:click.prevent="editAdvert(index)">Edit</a>&nbsp;
	                    	<a href="#" class="btn btn-danger btn-sm" v-on:click.prevent="deleteAdvert(index)">Delete</a>
	                    </div>
	                  </div>
	                </div>
	              </div>

	        </div>

            <div class="col s12" v-if="adverts === null || adverts.length === 0">
                @component('layouts.blocks.tabler.empty-fullpage')
                    @slot('title')
                        No Ads
                    @endslot
                    You can add one or more ads to be displayed on your Dorcas Services (<em>such as Store and Blog</em>)
                    @slot('buttons')
                        <a href="#" v-on:click.prevent="createAdvert" class="btn btn-primary btn-sm">Add Advert</a>
                    @endslot
                @endcomponent
            </div>
            @include('modules-ecommerce::modals.new-advert')
        </div>

    </div>

</div>


@endsection
@section('body_js')
    <script type="text/javascript">
        var vm = new Vue({
            el: '#ecommerce-adverts',
            data: {
                adverts: {!! json_encode(!empty($adverts) ? $adverts : []) !!},
                advert: {title: '', type: '', redirect_url: '', is_default: 1},
                editMode: false,
                recommendedDim: ''
            },
            methods: {
                createAdvert: function () {
                    this.advert = {title: '', type: '', redirect_url: '', is_default: 1};
                    $('#add-advert-modal').modal('show');
                },
                adjustRecommendation: function () {
                    if (this.advert.type === 'sidebar') {
                        this.recommendedDim = '240 x [any height]';
                    } else if (this.advert.type === 'footer') {
                        this.recommendedDim = '[any width] x [any height]';
                    } else {
                        this.recommendedDim = 'proper';
                    }
                },
                editAdvert: function (index) {
                    let advert = typeof this.adverts[index] !== 'undefined' ? this.adverts[index] : null;
                    if (typeof advert.id === 'undefined') {
                        return;
                    }
                    advert.is_default = advert.is_default ? 1 : 0;
                    this.advert = advert;
                    $('#add-advert-modal').modal('show');
                },
                deleteAdvert: function (index) {
                    let advert = typeof this.adverts[index] !== 'undefined' ? this.adverts[index] : null;
                    if (advert === null) {
                        return;
                    }
                    advert.is_default = advert.is_default ? 1 : 0;
                    this.advert = advert;
                    let context = this;
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You are about to delete advert " + context.advert.title,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, delete it!",
		                showLoaderOnConfirm: true,
		                preConfirm: (advert_delete) => {
	                        return axios.delete("/mec/ecommerce-adverts/" + context.advert.id)
	                            .then(function (response) {
	                                console.log(response);
	                                context.adverts.splice(index, 1);
	                                return swal("Deleted!", "The advert was successfully deleted.", "success");
	                            })
	                            .catch(function (error) {
	                                var message = '';
	                                if (error.response) {
	                                    // The request was made and the server responded with a status code
	                                    // that falls out of the range of 2xx
	                                    //var e = error.response.data.errors[0];
	                                    //message = e.title;
			                                var e = error.response;
			                                message = e.data.message;
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
		                },
		                allowOutsideClick: () => !Swal.isLoading()
                    });


                }
            }
        });

        new Vue({
            el: '#sub-menu-action',
            data: {

            },
            methods: {
                createAdvert: function () {
                    vm.createAdvert();
                }
            }
        })

    </script>
@endsection

