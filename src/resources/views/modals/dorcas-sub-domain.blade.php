<div class="modal fade" id="dorcas-sub-domain-modal" tabindex="-1" role="dialog" aria-labelledby="dorcas-sub-domain-modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="dorcas-sub-domain-modalLabel">Reserve Your Dorcas Sub Domain</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="" id="form-dorcas-sub-domain" method="post">
            {{ csrf_field() }}
            <h5>Check &amp; Reserve your <strong>Dorcas Sub Domain</strong> (<em>a unique prefix for services such as website, online store, blog, etc</em>)</h5>
            <fieldset class="form-fieldset">
                <div class="row">
                    <div class="col-md-12 form-group">
                        <input class="form-control" id="domain" type="text" name="domain" maxlength="80" v-model="domain" required v-on:keyup="removeStatus()">
                        <label class="form-label" for="domain">Desired Domain</label>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-status card-status-left" v-bind:class="{'bg-green': is_available, 'bg-red': !is_available && is_queried }"></div>
                            <div class="card-body">
                                <p :class="{'card-alert alert alert-success mb-0': is_available, 'card-alert alert alert-danger mb-0': !is_available && is_queried, '': is_querying }">
                                    https://@{{ actual_domain }}.{{ get_dorcas_parent_domain() }}
                                </p>
                                <p id="domain_result" style="font-weight: bold;"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
      </div>
      <div class="modal-footer">
        <a href="#" v-on:click.prevent="checkAvailability()" class="btn btn-primary" :class="{'btn-loading': is_querying}">Check Availability</a>
        <button type="submit" form="form-dorcas-sub-domain" name="reserve_subdomain" value="1" class="btn btn-primary" name="save_credential" value="1" v-if="is_available">Reserve</button>
      </div>
    </div>
  </div>
</div>
