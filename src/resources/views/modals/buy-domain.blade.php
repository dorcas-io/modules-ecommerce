<div class="modal fade" id="buy-domain-modal" tabindex="-1" role="dialog" aria-labelledby="buy-domain-modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="buy-domain-modalLabel">Buy Domain</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <form action="" id="form-buy-domain" method="post">
            {{ csrf_field() }}
            <h5>Buy a <strong>custom domain</strong> for your business and services (<em>such as abc.com or xyz.com.ng</em>)</h5>
            <fieldset class="form-fieldset">
                <div class="row">
                    <div class="col-md-8 form-group">
                        <input class="form-control" id="domain" type="text" name="domain" maxlength="80" v-model="domain" required v-on:keyup="removeStatus()">
                        <label class="form-label" for="domain">Desired Domain</label>
                    </div>
                    <div class="col-md-4 form-group">
                        <select class="form-control" id="extension" name="extension" v-model="extension">
                            <option value="com">.com</option>
                            <option value="com.ng">.com.ng</option>
                        </select>
                        <label class="form-label" for="extension">Extension</label>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-status card-status-left" v-bind:class="{'bg-green': is_available, 'bg-red': !is_available && is_queried}"></div>
                            <div class="card-content">
                                <p>
                                    @{{ actual_domain }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>

      </div>
      <div class="modal-footer">
          <a href="#" v-on:click.prevent="checkAvailability()" class="btn btn-primary" :class="{'btn-loading': is_querying }">Check Availability</a>
          <button type="submit" form="form-buy-domain" class="btn btn-success" name="purchase_domain" value="1" v-if="is_available && wallet_balance >= domain_amount">Purchase</button>
          <button class="btn btn-success" name="add_balance" v-on:click.prevent="payForDomain()" v-if="is_available && wallet_balance < domain_amount">Purchase</button>
      </div>
    </div>
  </div>
</div>