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
<div class="row" id="ecommerce-wallet">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9">
        <div class="row">

            <div class="col-md-6">
                <form class="card" action="" method="post">
                    {{ csrf_field() }}
                    <div class="card-status card-status-top" v-bind:class="{ 'bg-green': wallet_enabled, 'bg-red': !wallet_enabled }"></div>
                    <div class="card-body">
                        <h3 class="card-title">Manage Payment Wallet</h3>
                      
                        <div class="row">

                            <div class="col-md-12">
                                <h4>Status</h4>
                                <p v-if="wallet_enabled" class="card-alert alert alert-success mb-0"><strong>Enabled</strong></p>
                                <p v-if="!wallet_enabled" class="card-alert alert alert-danger mb-0"><strong>Not Enabled</strong></p>
                                <br/>
                                <br/>
                                <h4>Details</h4>
                                <div v-if="wallet_enabled">
                                    <div>Account Reference: @{{ wallet_data.account_reference }}</div>
                                    <div>Account Name: @{{ wallet_data.account_name }}</div>
                                    <div>Account Bank: @{{ wallet_data.bank_name }}</div>
                                    <div>Account Number: @{{ wallet_data.nuban }}</div>
                                    <div>Status: @{{ wallet_data.status }}</div>
                                </div>
                                <br/>
                                <h4>Balances</h4>
                                <div v-if="wallet_enabled">
                                    <div v-for="(wallet, index) in wallet_balances" :key="index" v-if="wallet_balances.length > 0">
                                        <div>Balance (Ledger): @{{ wallet.currency + ' ' + wallet.ledger_balance.toLocaleString("en-US") }}</div>
                                        <div>Balance (Available): @{{ wallet.currency + ' ' + wallet.available_balance.toLocaleString("en-US") }}</div>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                    <div class="card-footer text-right">
                        <!-- <button type="submit" name="action" value="update_business" class="btn btn-primary">Update Something</button> -->
                    </div>

                </form>

            </div>



            <div class="col-md-6">

                <form class="card" action="" method="post">
                    {{ csrf_field() }}
                    <div class="card-body">
                        <h3 class="card-title">Transfer Wallet Funds</h3>
                        <p class="text-muted">
                            You can transfer funds from your wallet to your corporate bank account<br/><br/>

                            <ul v-if="!transfer_bank_available">
                                <li>It appears you have not setup your bank account information</li>
                                <li><a href="{{ route('settings-banking') }}">Setup Banking</a></li>
                            </ul>

                            <ul v-if="transfer_bank_available">
                                <li>Transfer Bank: <strong>@{{ bank_details.bank_name }}</strong></li>
                                <li>Transfer Account Name: <strong>@{{ bank_details.account_name }}</strong></li>
                                <li>Transfer Account Number: <strong>@{{ bank_details.account_number }}</strong></li>
                            </ul>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="transfer_amount">Amount to Transfer</label>
                                    <input id="transfer_amount" type="text" name="transfer_amount" maxlength="30" v-model="transfer_amount_available" required class="form-control">
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                Status: 
                            </div>

                            <div class="col-md-12">
                                <button class="btn btn-primary" name="action">Initiate Transfer</button>
                            </div>
                        </p>

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


        </div>

    </div>

</div>


@endsection
@section('body_js')

<script type="text/javascript">
    let vmSettingsPage = new Vue({
        el: '#ecommerce-wallet',
        data: {
            company: {!! json_encode($company) !!},
            company_data: {!! json_encode($company_data) !!},
            wallet_enabled: {!! json_encode($wallet_enabled) !!},
            wallet_data: {!! json_encode($wallet_data) !!},
            loggedInUser: headerAuthVue.loggedInUser,
            wallet_balances: {!! json_encode($wallet_balances) !!},
            transfer_bank_available: {!! json_encode($transfer_bank_available) !!},
            transfer_amount_available: {!! json_encode($transfer_amount_available) !!},
            transfer_available: {!! json_encode($transfer_available) !!},
            bank_details: {!! json_encode($bank_details) !!},
        },
        mounted: function() {
            
        },
        computed: {
            
        },
        methods: {
            getWalletBalance: function () {
                
            },
        }
    })
</script>
    
@endsection
