<div class="modal fade" id="add-advert-modal" tabindex="-1" role="dialog" aria-labelledby="add-advert-modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="add-advert-modalLabel">@{{ typeof advert.id !== 'undefined' ? 'Edit Advert' : 'New Advert' }}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        

        <form action="" id="form-add-advert" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <h5>Add a new <strong>Advert</strong> for display on your Store or Blog</h5>
            <fieldset class="form-fieldset">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="ad-title" type="text" name="title" maxlength="80" v-model="advert.title">
                        <label class="form-label" for="ad-title" v-bind:class="{'active': advert.title.length > 0}">Advert Title</label>
                    </div>
                    <div class="col-md-6 form-group">
                        <select class="form-control" id="ad-type" name="type" v-model="advert.type" required v-on:change="adjustRecommendation">
                            <option value="" disabled>Select the Advert Type</option>
                            <option value="sidebar">Sidebar Vertical Ad</option>
                            <option value="footer">Footer Horizontal Ad</option>
                        </select>
                        <label class="form-label" for="ad-type">Advert Type</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="ad-url" type="text" name="redirect_url" maxlength="400" v-model="advert.redirect_url">
                        <label for="ad-url" v-bind:class="{'active': advert.redirect_url.length > 0}">Advert Redirect URL (when the Ad is clicked)</label>
                    </div>
                    <div class="col-md-6 form-group">
                        <select class="form-control" id="ad-is_default" name="is_default" v-model="advert.is_default">
                            <option value="0" selected>Leave it as is</option>
                            <option value="1">Make Default Ad for Type</option>
                        </select>
                        <label class="form-label" for="ad-is_default">Advert Default</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <div class="form-label">Ad Image</div>
                        <div class="custom-file">
                            <input type="file" name="image" id="ad-image" accept="image/*" class="custom-file-input" name="example-file-input-custom">
                            <label class="custom-file-label">Choose Image</label>
                            <small>We recommend a <strong>@{{ recommendedDim }}</strong> image, or similar</small>
                        </div>
                    </div>
                    <div class="col-md-12 form-group">
                        <img v-if="typeof advert.image_url !== 'undefined' && advert.image_url !== null" class="responsive-img" :src="advert.image_url">
                    </div>
                </div>
            </fieldset>
        </form>

      </div>
      <div class="modal-footer">
            <input type="hidden" name="advert_id" id="ad-advert-id" :value="advert.id" v-if="typeof advert.id !== 'undefined'" />
        <button type="submit" form="form-add-advert" class="btn btn-primary"  name="save_ad" value="1" >@{{ typeof advert.id !== 'undefined' ? 'Update Advert' : 'Create Ad' }}</button>
      </div>
    </div>
  </div>
</div>

