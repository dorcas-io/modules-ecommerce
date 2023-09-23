<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle || 'Payment Confirmation' }}</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ cdn('apps/webstore/css/pay-form-style.css') }}" type="text/css" />
    <meta name="robots" content="noindex,follow" />
    <style type="text/css">
        .success-box {
            border:  1px solid #4caf50;
            background: #e8f5e9;
        }
        .error-box {
            border:  1px solid #f44336;
            background: #ffebee;
        }
    </style>
</head>
<body>
<div class="checkout-panel" style="height: auto;">
    <div class="panel-body">
        <div><center><img height="50" src="{{ $company_logo }}"/></center></div>
        <h2><center>{{ $company_name }} Store</center></h2>
        <div><br/></div>
        <h3 class="title">Payment Checkout</h3>
        <div class="progress-bar">
            <div class="step active"></div>
            <div class="step active"></div>
            <div class="step active"></div>
            <div class="step"></div>
        </div>
        <div class="payment-method">
            <label for="card" class="method card {{ $txn['is_successful'] ? 'success-box' : 'error-box' }}" style="width: 100%;">
                <h4>Payment Reference: {{ $reference }}</h4>
                <p>{{ $message }}</p>
            </label>
        </div>
        <div class="payment-method">
            <label for="card" class="method card info-box" style="width: 100%;">
                <p><a href="{{ $webstore_url }}">Back to Store</a></p>
            </label>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script type="text/javascript" src="{{ cdn('apps/webstore/js/payment-form.js.js') }}"></script>
</body>
</html>