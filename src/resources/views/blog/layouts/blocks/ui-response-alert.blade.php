@if (!empty($uiResponse) && $uiResponse instanceof \App\Dorcas\Hub\Utilities\UiResponse\UiResponseInterface)
    <div class="row">
        <div class="col_full">
            {!! $uiResponse->toHtml() !!}
        </div>
    </div>
@elseif (count($errors) > 0)
    <div class="style-msg2 errormsg">
        <div class="msgtitle">Fix the Following Errors:</div>
        <div class="sb-msg">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif