<div class="modal fade" id="add-domain-modal" tabindex="-1" role="dialog" aria-labelledby="add-domain-modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="add-domain-modalLabel">Add an Existing Domain (that You Own)</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <form action="" id="form-add-domain" method="post">
            {{ csrf_field() }}
            <h5>Add an <strong>existing domain</strong> that you own:</h5>
            <fieldset class="form-fieldset">
                <div class="row">
                    <div class="col-md-12 form-group">
                        <input class="form-control" id="domain" type="text" name="domain" maxlength="80" v-model="domain" required v-on:keyup="removeStatus()">
                        <label class="form-label" for="domain">Your Domain</label>
                    </div>
                </div>
            </fieldset>
        </form>


      </div>
      <div class="modal-footer">
        <button type="submit" form="form-add-domain" class="btn btn-primary" v-on:click="addExistingDomain" :class="{ 'btn-loading' : adding_existing_domain }" name="add_domain" value="1">Add Domain</button>
      </div>
    </div>
  </div>
</div>