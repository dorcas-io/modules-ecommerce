@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="row col-md-9 col-xl-9" id="e-commerce-main">

		<div class="col-md-6 col-xl-4">
			<div class="card">
				<div class="card-status bg-green"></div>
				<div class="card-header">
					<h3 class="card-title">Domain Manager</h3>
					<div class="card-options">
						<a href="#" class="btn btn-primary btn-sm">Launch</a>
					</div>
				</div>
				<div class="card-body">
					Reserve your Dorcas sub-domain, buy custom names and manage domain
				</div>
			</div>
		</div>






		
    </div>

</div>


@endsection
@section('body_js')
    <script type="text/javascript">
     
    </script>
@endsection