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
                    <div class="card-body">
                        <h3 class="card-title">Manage Payment Wallet</h3>
                      
                        <div class="row">

                            <div class="col-md-12">

                                <h4>Status</h4>
                                <span v-if="wallet_enabled">Enabled</span>
                                <span v-if="!wallet_enabled">Not Enabled</span>
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
                                        <span>Currency: @{{ wallet.currency }}</span>
                                        <span>Balance (Ledger): @{{ wallet.ledger_balance }}</span>
                                        <span>Balance (Available): @{{ wallet.available_balance }}</span>
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
