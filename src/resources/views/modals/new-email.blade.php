<div class="modal fade" id="add-email-modal" tabindex="-1" role="dialog" aria-labelledby="add-email-modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="add-email-modalLabel">New Email Account</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <form action="" id="form-add-email" method="post">
            {{ csrf_field() }}
            <h5>Add a new <strong>Email Account</strong> such as <em>info@yourdomain.com</em></h5>
            <fieldset class="form-fieldset">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="email-username" type="text" name="username" maxlength="80" required
                               v-model="email.username">
                        <label class="form-label" for="email-username">Email Username Prefix</label>
                        <small>Without the @ part</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <select class="form-control" id="email-domain" name="domain" required v-model="email.domain">
                            <option value="" disabled>Select the Domain</option>
                            <option v-for="domain in domains" :key="domain.id" :value="domain.domain">@{{ domain.domain }}</option>
                        </select>
                        <label class="form-label" for="email-domain">Domain</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="email-password" type="password" name="password" maxlength="255">
                        <label class="form-label" for="email-password">Password</label>
                        <small>The password should have 8 characters minimum with at least one uppercase-character, and a number</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="email-quota" type="number" name="quota" min="25" max="1024" v-model="email.quota">
                        <label class="form-label" for="email-quota">Storage Quota (in MB)</label>
                    </div>
                </div>
            </fieldset>
        </form>


      </div>
      <div class="modal-footer">
        <button type="submit" form="form-add-email" class="btn btn-primary" name="action" value="create_email">Create Email Account</button>
      </div>
    </div>
  </div>
</div>
