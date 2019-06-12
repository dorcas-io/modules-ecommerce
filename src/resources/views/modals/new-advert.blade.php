<div class="modal fade" id="add-domain-modal" tabindex="-1" role="dialog" aria-labelledby="add-domain-modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="add-domain-modalLabel">New Credential</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        

        <form action="" id="form-dorcas-sub-domain" method="post">
            {{ csrf_field() }}
            <h5>Add a new <strong>Credential</strong> such as certifications</h5>
            <fieldset class="form-fieldset">
                <div class="row">
                    <div class="col-md-12 form-group">
                        <input class="form-control" id="domain" type="text" name="domain" maxlength="80" v-model="domain" required v-on:keyup="removeStatus()">
                        <label class="form-label" for="domain">Desired Domain</label>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-status" v-bind:class="{'bg-green': is_available, 'bg-red': !is_available && is_queried}"></div>
                            <div class="card-content">
                                <p>
                                    https://@{{ actual_domain }}.{{ get_dorcas_domain() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>


      </div>
      <div class="modal-footer">
        <button type="submit" form="form-new-credential" class="btn btn-primary" name="save_credential" value="1" :class="{'btn-loading': modals.credential.is_processing}">Save Credential</button>
      </div>
    </div>
  </div>
</div>




<div id="manage-ad-modal" class="modal">
    <form class="col s12" action="" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="modal-content">
            <h4>@{{ typeof advert.id !== 'undefined' ? 'Edit Advert' : 'New Advert' }}</h4>
            <div class="row">
                <div class="col s12 m6">
                    <div class="input-field col s12">
                        <input id="ad-title" type="text" name="title" maxlength="80" v-model="advert.title">
                        <label for="ad-title" v-bind:class="{'active': advert.title.length > 0}">Advert Title</label>
                    </div>
                </div>
                <div class="col s12 m6">
                    <div class="input-field col s12">
                        <select class="browser-default" id="ad-type" name="type" v-model="advert.type" required
                                v-on:change="adjustRecommendation">
                            <option value="" disabled>Select the Advert Type</option>
                            <option value="sidebar">Sidebar Vertical Ad</option>
                            <option value="footer">Footer Horizontal Ad</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col s12 m6">
                    <div class="input-field col s12">
                        <input id="ad-url" type="text" name="redirect_url" maxlength="400" v-model="advert.redirect_url">
                        <label for="ad-url" v-bind:class="{'active': advert.redirect_url.length > 0}">Advert Redirect URL (when the Ad is clicked)</label>
                    </div>
                </div>
                <div class="col s12 m6">
                    <div class="input-field col s12">
                        <select class="browser-default" id="ad-is_default" name="is_default" v-model="advert.is_default">
                            <option value="0" selected>Leave it as is</option>
                            <option value="1">Make Default Ad for Type</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col s12 m6">
                    <div class="file-field input-field">
                        <div class="btn">
                            <span>File</span>
                            <input type="file" name="image" id="ad-image" accept="image/*" >
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text" placeholder="Select Ad image" />
                            <small>We recommend a <strong>@{{ recommendedDim }}</strong> image, or similar</small>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6">
                    <img v-if="typeof advert.image_url !== 'undefined' && advert.image_url !== null"
                         class="responsive-img" :src="advert.image_url">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="hidden" name="advert_id" id="ad-advert-id" :value="advert.id" v-if="typeof advert.id !== 'undefined'" />
            <button type="submit" class="modal-action waves-effect waves-green btn-flat" name="save_ad"
                    value="1" >@{{ typeof advert.id !== 'undefined' ? 'Update Advert' : 'Create Ad' }}</button>
        </div>
    </form>
</div>