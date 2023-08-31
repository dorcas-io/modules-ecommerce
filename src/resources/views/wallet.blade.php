@extends('layouts.tabler')

@section('head_css')
<style type="text/css">
    .pac-container {
        background-color: #FFF;
        z-index: 20;
        position: fixed;
        display: inline-block;
        float: left;
    }
    .modal{
        z-index: 20;   
    }
    .modal-backdrop{
        z-index: 10;        
    }
</style>
@endsection

@section('body_content_header_extras')

@endsection

@section('body_content_main')
@include('layouts.blocks.tabler.alert')
<div class="row" id="business-profile">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9">
        <div class="row">

            <div class="col-md-6">
                <form class="card" action="" method="post">
                    {{ csrf_field() }}
                    <div class="card-body">
                        <h3 class="card-title">Manage Payment Wallet</h3>
                      
                        <div class="row">

                            <div class="col-md-12">
                                <h4>Status</h4>
                                <span v-if="wallet_enabled">Enabled</span>
                                <span v-if="!wallet_enabled">Not Enabled</span>
                            </div>
            
                            <div class="col-md-6">
                                <div v-if="wallet_enabled">
                                    <span>@{{ wallet_data.account_reference }}</span>
                                    <div>@{{ wallet_data.bank_name }}</div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" name="action" value="update_business" class="btn btn-primary">Update Profile</button>
                    </div>

                </form>

            </div>



            <div class="col-md-6">

                <form class="card" action="" method="post">
                    {{ csrf_field() }}
                    <div class="card-body">
                        <h3 class="card-title">Transfer Wallet Funds</h3>
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    
                                </div>
                            </div>

                            
                        
                        </div>
                        <div class="row">

                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <input type="hidden" name="latitude" id="latitude">
                        <button :disabled="!addressIsConfirmed" type="submit" name="action" value="update_location" class="btn btn-primary">Save Address</button>
                    </div>

                </form>

            </div>

            <div class="modal fade" id="confirm-address-modal" tabindex="-1" role="dialog" aria-labelledby="confirm-address-modalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="confirm-address-modalLabel">Address GeoLocation</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            
                            <h5>Confirm your Address <em>on the map</em></h5>

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <input type="text" class="form-control" name="address_address" id="address_address" required placeholder="Enter Delivery Address">
                            
                                </div>
                            </div>

                            <div class="row col-md-12">
                                <div id="address_map" style="width:100%; height: 300px;">
                                    Loading Map...
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <a id="address_confirm" href="#" v-on:click.prevent="addressIsCorrect" class="btn btn-success btn-block">Confirm Location</a>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <!-- <button type="submit" v-if="addressIsConfirmed" form="form-confirm-address" class="btn btn-primary" name="action" value="confirm_address">Confirm & Save Address</button> -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">
                        <h3 class="card-title">Marketplace</h3>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <settings-toggle title="Professional Services" name="set_professional_status" :checked="loggedInUser.is_professional"></settings-toggle>
                            </div>
                        </div>
                        <!-- <div class="row">
                            <div class="form-group col-md-12">
                                <settings-toggle title="Product Vendor" name="set_vendor_status" :checked="loggedInUser.is_vendor"></settings-toggle>
                            </div>
                        </div> -->
                    </div>
                    <div class="card-footer">
                        Enable any of the above for your account
                    </div>

                </div>

            </div>


        </div>

    </div>

</div>


@endsection
@section('body_js')

<script type="text/javascript">
    let vmSettingsPage = new Vue({
        el: '#business-profile',
        data: {
            company: {!! json_encode($company) !!},
            company_data: {!! json_encode($company_data) !!},
            wallet_enabled: {!! json_encode($wallet_enabled) !!},
            wallet_data: {!! json_encode($wallet_data) !!},
            loggedInUser: headerAuthVue.loggedInUser,
        },
        mounted: function() {
            if (this.company_data.location.latitude > 0 && this.company_data.location.longitude > 0) {
                this.addressIsConfirmed = true
            } else {
                //console.log(this.company_data.location)
            }
        },
        computed: {
            
        },
        methods: {
            loadGoogleMaps: function () {
                // Load the Google Maps API script
                const script = document.createElement('script');
                if (this.useAutoComplete) {
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${this.env.CREDENTIAL_GOOGLE_API_KEY}&libraries=places&callback=Function.prototype`;
                } else {
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${this.env.CREDENTIAL_GOOGLE_API_KEY}&callback=Function.prototype`;
                }
                script.onload = function() {
                    vmSettingsPage.initAutocomplete();
                };
                script.defer = true;
                document.head.appendChild(script);
            },
            // initMap: function () {
            //     // Initialize and display the map
            //     const address = `${this.location.address1}, ${this.location.address2}, ${this.location.city}`;
            //     let stateObject = this.states.find( st => st.id === this.location.state.data.id );
            //     const state = stateObject.name;
            //     console.log(stateObject)
            //     console.log(this.countries)
            //     console.log(this.env.SETTINGS_COUNTRY);
            //     const country = this.countries.find( co => co.id === this.env.SETTINGS_COUNTRY );

            //     let retry = false;
            //     //let retry = vmSettingsPage.company_data.location.latitude > 0 && vmSettingsPage.company_data.location.longitude > 0;

            //     if (retry) {

            //         const latitude = vmSettingsPage.company_data.location.latitude;
            //         const longitude = vmSettingsPage.company_data.location.longitude;

            //         const mapOptions = {
            //             center: { lat: latitude, lng: longitude },
            //             zoom: 8
            //         };
            //         const map = new google.maps.Map(document.getElementById('address_map'), mapOptions);

            //         // Optionally, you can add a marker at the specified coordinates
            //         const marker = new google.maps.Marker({
            //             position: { lat: latitude, lng: longitude },
            //             map: map,
            //             title: vmSettingsPage.company.name
            //         });

            //     } else {

            //         const geocoder = new google.maps.Geocoder();
            //         const mapOptions = {
            //             zoom: 15,
            //             center: new google.maps.LatLng(0, 0) // Default center
            //         };
            //         const map = new google.maps.Map(document.getElementById('address_map'), mapOptions);

            //         const addressString = `${address}, ${state}, ${country}`;
            //         console.log(addressString)
            //         geocoder.geocode({ address: addressString }, function(results, status) {
            //             console.log(status, results);
            //             if (status === google.maps.GeocoderStatus.OK) {
            //                 map.setCenter(results[0].geometry.location);
            //                 new google.maps.Marker({
            //                     map: map,
            //                     position: results[0].geometry.location,
            //                     title: vmSettingsPage.company.name
            //                 });
            //             } else {
            //                 console.log('Geocode was not successful for the following reason: ' + status);
            //             }
            //         });

            //     }
            // },
            initAutocomplete: function () {

                const mapOptions = {
                    center: { lat: 0, lng: 0 },
                    zoom: 18
                };
                const map = new google.maps.Map(document.getElementById('address_map'), mapOptions);
                const geocoder = new google.maps.Geocoder();

                // Initialize the autocomplete
                const input = document.getElementById('address_address');
                const autocomplete = new google.maps.places.Autocomplete(input);

                autocomplete.bindTo('bounds', map);

                // Retrieve the selected place and populate latitude and longitude fields
                autocomplete.addListener('place_changed', function() {

                    const place = autocomplete.getPlace();
                    if (!place.geometry) {
                        console.log('No location data available for this place.');
                        vmSettingsPage.addressIsConfirmed = false;
                        return;
                    }

                    // Update the map center and marker
                    map.setCenter(place.geometry.location);
                    const marker = new google.maps.Marker({
                        map: map,
                        position: place.geometry.location
                    });

                    // Extract the state and country
                    let state = '';
                    let country = '';
                    let countryCode = '';
                    for (const component of place.address_components) {
                        const componentType = component.types[0];
                        if (componentType === 'administrative_area_level_1') {
                            state = component.long_name;
                        } else if (componentType === 'country') {
                            country = component.long_name;
                            countryCode = component.short_name; // Two-digit ISO country code
                        }
                    }

                    // Log the state and country to the console
                    // console.log(state);
                    // console.log(country + ' ' + countryCode);

                    vmSettingsPage.locationLatitude = place.geometry.location.lat();
                    vmSettingsPage.locationLongitude = place.geometry.location.lng();
                    
                });
            },
            addressConfirm: function () {
                this.loadGoogleMaps();
                $('#confirm-address-modal').modal('show');
            },
            addressReConfirm: function () {
                this.addressIsConfirmed = false;
                // this.loadGoogleMaps();
                // $('#confirm-address-modal').modal('show');
            },
            addressIsCorrect: function () {
                this.addressIsConfirmed = true;
                this.company_data.location.latitude = this.locationLatitude;
                this.company_data.location.longitude = this.locationLongitude;
                $('#confirm-address-modal').modal('hide');
            },
            addressCancel: function () {
                $('#confirm-address-modal').modal('hide');
            },
        }
    })
</script>
    
@endsection
