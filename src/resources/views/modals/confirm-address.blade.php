<div class="modal fade" id="confirm-address-modal" tabindex="-1" role="dialog" aria-labelledby="confirm-address-modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="confirm-address-modalLabel">Delivery Address</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <h5>Confirm your Delivery Address <em>on the map</em></h5>

                <div v-if="!useAutoComplete" class="row" id="address_map"></div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <a id="address_confirm" href="#" v-on:click.prevent="addressConfirm" class="btn btn-success btn-block">Address Is Correct</a>
                    </div>
                    <div class="col-md-6 form-group">
                        <!-- <a id="address_cancel" href="#" v-on:click.prevent="addressCancel" class="btn btn-warning btn-block">Let Me Change Address</a> -->
                        <a id="address_cancel" href="#" data-dismiss="modal" class="btn btn-warning btn-block">Let Me Change Address</a>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <!-- <button type="submit" v-if="addressIsConfirmed" form="form-confirm-address" class="btn btn-primary" name="action" value="confirm_address">Confirm & Save Address</button> -->
            </div>
        </div>
    </div>
</div>
