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
                        <h3 class="card-title">Business Information</h3>
                      
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="name"  @if ($errors->has('name')) data-error="{{ $errors->first('name') }}" @endif>Business Name</label>
                                    <input id="name" type="text" name="name" maxlength="80" v-model="company.name" required class="form-control {{ $errors->has('name') ? ' invalid' : '' }}" >
                                </div>
                            </div>
            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="registration" @if ($errors->has('registration')) data-error="{{ $errors->first('registration') }}" @endif>Registration Number</label>
                                    <input id="registration" type="text" name="registration" v-model="company.registration" maxlength="30" class="form-control {{ $errors->has('registration') ? ' invalid' : '' }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="phone"  @if ($errors->has('phone')) data-error="{{ $errors->first('phone') }}" @endif>Contact Phone</label>
                                    <input id="phone" type="text" name="phone" v-model="company.phone" maxlength="30" class="form-control {{ $errors->has('phone') ? ' invalid' : '' }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="email" class="active"  @if ($errors->has('email')) data-error="{{ $errors->first('email') }}" @endif>Email Address</label>
                                    <input id="email" type="email" name="email" v-model="company.email" maxlength="80" class="form-control {{ $errors->has('email') ? ' invalid' : '' }}">
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="website" class="active"  @if ($errors->has('website')) data-error="{{ $errors->first('website') }}" @endif>Website</label>
                                    <input id="website" type="text" name="website" v-model="company.website" maxlength="80" class="form-control {{ $errors->has('website') ? ' invalid' : '' }}">
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
                        <h3 class="card-title">Address Information</h3>
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="address1"  @if ($errors->has('address1')) data-error="{{ $errors->first('address1') }}" @endif>Address Line 1</label>
                                    <input id="address1" type="text" name="address1" v-model="location.address1" maxlength="100" required class="form-control {{ $errors->has('address1') ? ' invalid' : '' }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="address2"  @if ($errors->has('address2')) data-error="{{ $errors->first('address2') }}" @endif>Address Line 2</label>
                                    <input id="address2" type="text" name="address2" v-model="location.address2" maxlength="100" class="form-control {{ $errors->has('address2') ? ' invalid' : '' }}">
                                </div>
                            </div>
                                                        
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="city"  @if ($errors->has('city')) data-error="{{ $errors->first('city') }}" @endif>City</label>
                                    <input id="city" type="text" name="city" maxlength="100" v-model="location.city" required class="form-control {{ $errors->has('city') ? ' invalid' : '' }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="state"  @if ($errors->has('state')) data-error="{{ $errors->first('state') }}" @endif>State</label>
                                    <select name="state" id="state" v-model="location.state.data.id" class="form-control {{ $errors->has('state') ? ' invalid' : '' }}">
                                        <option v-for="state in states" :value="state.id" :key="state.id">@{{ state.name }}</option>
                                    </select>
                                </div>
                            </div>
                        
                        </div>
                        <div class="row">
                            <div class="col-md-12" v-if="!addressIsConfirmed">
                                <p>
                                    For the purpose of shipping/logistics (when you have to send orders to your customers), we need to properly <strong>geolocate</strong> your address above.
                                    <em>Click the <strong>Geo-Locate</strong> Address button to do this</em>
                                </p>
                                <button name="check_address" value="check_address" class="btn btn-success" v-on:click.prevent="addressConfirm">Geo-Locate Address</button>
                            </div>
                            <div class="col-md-12" v-if="addressIsConfirmed">
                                If you would like to change the GeoLocation of your address (for shipping/logistics purposes),
                                <a href="#" v-on:click.prevent="addressReConfirm">RE-LOCATE ADDRESS</a>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <input type="hidden" name="latitude" id="latitude" v-model="company_data.location.latitude">
                        <input type="hidden" name="longitude" id="longitude" v-model="company_data.location.longitude">
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
            location: {!! json_encode($location) !!},
            states: {!! json_encode($states) !!},
            countries: {!! json_encode($countries) !!},
            env: {!! json_encode($env) !!},
            loggedInUser: headerAuthVue.loggedInUser,
            addressIsConfirmed: false,
            useAutoComplete: true,
            locationLatitude: 0,
            locationLongitude: 0
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
